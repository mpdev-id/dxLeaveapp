<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveRequestResource;
use App\Jobs\SendLeaveRequestNotification;
use App\Jobs\SendLeaveRequestStatusUpdatedNotification;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Workflow;
use App\Services\EntitlementService;
use App\Services\LeaveRequestService;
use App\Services\WorkflowService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    protected $leaveRequestService;
    protected $entitlementService;
    protected $workflowService;

    public function __construct(LeaveRequestService $leaveRequestService, EntitlementService $entitlementService, WorkflowService $workflowService)
    {
        $this->leaveRequestService = $leaveRequestService;
        $this->entitlementService = $entitlementService;
        $this->workflowService = $workflowService;
    }

    public function index(Request $request)
    {
        try {
            $query = LeaveRequest::with(['user.department', 'leaveType', 'currentStep.approverRole', 'workflow.steps.approverRole', 'approvals.approver'])
                ->select('leave_requests.*');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    })
                        ->orWhereHas('leaveType', function ($leaveTypeQuery) use ($search) {
                            $leaveTypeQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            if ($request->filled('sort_by')) {
                $sortBy = $request->input('sort_by');
                $sortDir = $request->input('sort_dir', 'asc');
                $allowedSorts = ['start_date', 'end_date', 'current_status', 'user_name', 'leave_type_name'];

                if (in_array($sortBy, $allowedSorts)) {
                    if ($sortBy === 'user_name') {
                        $query->join('users', 'leave_requests.user_id', '=', 'users.id')
                            ->orderBy('users.name', $sortDir);
                    } elseif ($sortBy === 'leave_type_name') {
                        $query->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
                            ->orderBy('leave_types.name', $sortDir);
                    } else {
                        $query->orderBy($sortBy, $sortDir);
                    }
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $leaveRequests = $query->paginate($request->input('per_page', 10));

            return ResponseFormatter::success(LeaveRequestResource::collection($leaveRequests), 'Leave requests retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve leave requests: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'leave_type_id' => 'required|exists:leave_types,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:500',
                'leave_period' => [
                    'required',
                    Rule::in(['full_day', 'half_day_morning', 'half_day_afternoon']),
                ],
                'supporting_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'current_status' => 'required|in:Draft,Pending',
            ]);

            $user = User::find($validatedData['user_id']);

            $attachmentPath = null;
            if ($request->hasFile('supporting_document')) {
                $attachmentPath = $request->file('supporting_document')->store('attachments', 'public');
            }

            if (in_array($validatedData['leave_period'], ['half_day_morning', 'half_day_afternoon'])) {
                if ($validatedData['start_date'] !== $validatedData['end_date']) {
                    throw ValidationException::withMessages([
                        'leave_period' => 'Half-day leave can only be requested for a single day.',
                    ]);
                }
            }

            $startDate = Carbon::parse($validatedData['start_date']);
            $endDate = Carbon::parse($validatedData['end_date']);
            $duration = (in_array($validatedData['leave_period'], ['half_day_morning', 'half_day_afternoon'])) ? 0.5 : $startDate->diffInDays($endDate) + 1;


            if (!$this->entitlementService->hasSufficientBalance($user, $validatedData['leave_type_id'], $duration)) {
                throw ValidationException::withMessages(['leave' => 'Insufficient leave balance for the user.']);
            }

            $workflow = Workflow::where('applicable_model', LeaveRequest::class)->first();
            if (!$workflow) {
                return ResponseFormatter::error(null, 'Leave workflow not found.', 500);
            }

            $leaveRequest = DB::transaction(function () use ($validatedData, $workflow, $duration, $attachmentPath, $user) {
                $newData = [
                    'user_id' => $user->id,
                    'leave_type_id' => $validatedData['leave_type_id'],
                    'workflow_id' => $workflow->id,
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                    'leave_period' => $validatedData['leave_period'],
                    'reason' => $validatedData['reason'],
                    'duration_days' => $duration,
                    'supporting_attachment_path' => $attachmentPath,
                    'current_status' => $validatedData['current_status'],
                ];

                if ($validatedData['current_status'] === 'Pending') {
                    $firstStep = $workflow->steps()->orderBy('step_number', 'asc')->first();
                    if (!$firstStep) {
                        throw new \Exception('Workflow is not configured correctly. No steps found.');
                    }
                    $newData['current_workflow_step_id'] = $firstStep->id;
                }

                $leaveRequest = LeaveRequest::create($newData);

                if ($leaveRequest->current_status === 'Pending') {
                    SendLeaveRequestStatusUpdatedNotification::dispatch($leaveRequest);
                    $approver = $this->workflowService->findApproverForStep($leaveRequest->user, $leaveRequest->currentStep);
                    if ($approver) {
                        SendLeaveRequestNotification::dispatch($approver, $leaveRequest);
                    }
                }
                return $leaveRequest;
            });

            return ResponseFormatter::success(new LeaveRequestResource($leaveRequest->load('leaveType')), 'Leave request created successfully');
        } catch (ValidationException $e) {
            return ResponseFormatter::error(['errors' => $e->errors()], $e->getMessage(), 422);
        } catch (\Exception $e) {
            if (isset($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }
            return ResponseFormatter::error(null, 'Failed to create leave request: ' . $e->getMessage(), 500);
        }
    }


    public function show(LeaveRequest $leaveRequest)
    {
        try {
            $leaveRequest->load(['user', 'leaveType', 'approvals.approver', 'workflow.steps', 'currentStep']);
            return ResponseFormatter::success(new LeaveRequestResource($leaveRequest), 'Leave request retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve leave request: ' . $e->getMessage(), 500);
        }
    }


    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        try {
            $validatedData = $request->validate([
                'leave_type_id' => 'sometimes|required|exists:leave_types,id',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after_or_equal:start_date',
                'reason' => 'sometimes|required|string|max:500',
                'leave_period' => [
                    'sometimes', 'required',
                    Rule::in(['full_day', 'half_day_morning', 'half_day_afternoon']),
                ],
                'supporting_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            $attachmentPath = $leaveRequest->getRawOriginal('supporting_attachment_path');
            if ($request->hasFile('supporting_document')) {
                if ($attachmentPath) {
                    Storage::disk('public')->delete($attachmentPath);
                }
                $attachmentPath = $request->file('supporting_document')->store('attachments', 'public');
                $validatedData['supporting_attachment_path'] = $attachmentPath;
            }

            if (isset($validatedData['start_date']) || isset($validatedData['end_date']) || isset($validatedData['leave_period'])) {
                $startDate = Carbon::parse($validatedData['start_date'] ?? $leaveRequest->start_date);
                $endDate = Carbon::parse($validatedData['end_date'] ?? $leaveRequest->end_date);
                $leavePeriod = $validatedData['leave_period'] ?? $leaveRequest->leave_period;

                if (in_array($leavePeriod, ['half_day_morning', 'half_day_afternoon'])) {
                    if ($startDate->notEqualTo($endDate)) {
                        throw ValidationException::withMessages([
                            'leave_period' => 'Half-day leave can only be requested for a single day.',
                        ]);
                    }
                    $validatedData['duration_days'] = 0.5;
                } else {
                    $validatedData['duration_days'] = $startDate->diffInDays($endDate) + 1;
                }

                if (!$this->entitlementService->hasSufficientBalance($leaveRequest->user, $validatedData['leave_type_id'] ?? $leaveRequest->leave_type_id, $validatedData['duration_days'])) {
                    throw ValidationException::withMessages(['leave' => 'Insufficient leave balance for the user.']);
                }
            }


            $leaveRequest->update($validatedData);

            return ResponseFormatter::success(new LeaveRequestResource($leaveRequest->fresh()), 'Leave request updated successfully');
        } catch (ValidationException $e) {
            return ResponseFormatter::error(['errors' => $e->errors()], $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to update leave request: ' . $e->getMessage(), 500);
        }
    }


    public function destroy(LeaveRequest $leaveRequest)
    {
        try {
            DB::transaction(function () use ($leaveRequest) {
                if ($leaveRequest->getRawOriginal('supporting_attachment_path')) {
                    Storage::disk('public')->delete($leaveRequest->getRawOriginal('supporting_attachment_path'));
                }
                $leaveRequest->delete();
            });

            return ResponseFormatter::success(null, 'Leave request deleted successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to delete leave request: ' . $e->getMessage(), 500);
        }
    }


    public function handleApproval(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'action' => 'required|in:Approve,Reject,Cancel',
            'comments' => 'nullable|string',
        ]);

        try {
            $admin = Auth::user();
            $action = $request->input('action');
            $comments = $request->input('comments');

            $this->leaveRequestService->processApproval($leaveRequest, $admin, $action, $comments);

            return ResponseFormatter::success(new LeaveRequestResource($leaveRequest->fresh()), 'Approval action recorded successfully.');
        } catch (ValidationException $e) {
            return ResponseFormatter::error($e->errors(), $e->getMessage(), 403);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'A system error occurred.', 500);
        }
    }

    public function submit(Request $request, LeaveRequest $leaveRequest)
    {
        try {
            if ($leaveRequest->current_status !== 'Draft') {
                return ResponseFormatter::error(null, 'Only draft leave requests can be submitted.', 403);
            }

            // Validasi penuh sebelum submit
            $finalDuration = $leaveRequest->duration_days;
            if (!$this->entitlementService->hasSufficientBalance($leaveRequest->user, $leaveRequest->leave_type_id, $finalDuration)) {
                throw ValidationException::withMessages(['leave' => 'Insufficient leave balance.']);
            }

            $workflow = $leaveRequest->workflow;
            if (!$workflow) {
                return ResponseFormatter::error(null, 'Workflow not found for this leave request.', 500);
            }
            $firstStep = $workflow->steps()->orderBy('step_number', 'asc')->first();
            if (!$firstStep) {
                return ResponseFormatter::error(null, 'Workflow is not configured correctly. No steps found.', 500);
            }

            $leaveRequest->update([
                'current_status' => 'Pending',
                'current_workflow_step_id' => $firstStep->id,
            ]);
            
            $updatedLeaveRequest = $leaveRequest->fresh();

            SendLeaveRequestStatusUpdatedNotification::dispatch($updatedLeaveRequest);

            $approver = $this->workflowService->findApproverForStep($updatedLeaveRequest->user, $firstStep);
            if ($approver) {
                SendLeaveRequestNotification::dispatch($approver, $updatedLeaveRequest);
            }

            return ResponseFormatter::success(new LeaveRequestResource($updatedLeaveRequest), 'Leave request submitted successfully');
        } catch (ValidationException $e) {
            return ResponseFormatter::error(['errors' => $e->errors()], $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to submit leave request: ' . $e->getMessage(), 500);
        }
    }
}
