<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Models\Workflow;
use App\Models\PublicHoliday;
use App\Services\LeaveRequestService;
use App\Services\EntitlementService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Notifications\LeaveRequestStatusUpdated;
use App\Notifications\NewLeaveRequestForApprover;
use App\Jobs\SendLeaveRequestNotification;
use App\Jobs\SendLeaveRequestStatusUpdatedNotification;

class LeaveRequestController extends Controller
{
    protected $leaveRequestService;
    protected $entitlementService;
    protected $workflowService;

    // Injeksi dependensi
    public function __construct(LeaveRequestService $leaveRequestService, EntitlementService $entitlementService, WorkflowService $workflowService)
    {
        $this->leaveRequestService = $leaveRequestService;
        $this->entitlementService = $entitlementService;
        $this->workflowService = $workflowService;
    }

    /**
     * Tampilkan daftar permintaan cuti.
     * Manajer melihat permintaan yang perlu disetujui.
     * Karyawan melihat permintaan miliknya.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            // Eager load relationships for efficiency and for the resource
            $query = LeaveRequest::with(['user.department', 'leaveType', 'currentStep.approverRole', 'workflow.steps.approverRole', 'approvals.approver']);

            // Base query for user's own requests
            $ownRequestsQuery = (clone $query)->where('user_id', $user->id);

            // If user has permission to approve, they will also see requests they need to approve.
            if ($user->hasPermissionTo('approve leave request')) {
                // Get user roles
                $userRoles = $user->getRoleNames();

                // Get workflow steps the user can approve based on their roles
                $approvableStepIds = DB::table('workflow_steps as ws')
                    ->join('roles', 'ws.approver_role_id', '=', 'roles.id')
                    ->whereIn('roles.name', $userRoles)
                    ->pluck('ws.id');

                // Get requests waiting at those steps, excluding the user's own requests
                $requestsToApproveQuery = (clone $query)
                    ->whereIn('current_workflow_step_id', $approvableStepIds)
                    ->where('user_id', '!=', $user->id);

                // Get both sets of requests
                $ownRequests = $ownRequestsQuery->get();
                $requestsToApprove = $requestsToApproveQuery->get();

                // Merge, ensure uniqueness, and re-index.
                $requests = $ownRequests->merge($requestsToApprove)->unique('id')->values();
            } else {
                // Regular users only see their own requests
                $requests = $ownRequestsQuery->get();
            }

            return ResponseFormatter::success(LeaveRequestResource::collection($requests), 'Leave requests retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve leave requests: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveRequest $leaveRequest)
    {
        try {
            $leaveRequest->load(['user.department', 'leaveType', 'workflow']);

            // Authorization: Ensure user can only view their own request or if they are an approver/admin
            $user = Auth::user();
            if ($leaveRequest->user_id !== $user->id && !$user->hasPermissionTo('approve leave request')) {
                 return ResponseFormatter::error(null, 'Unauthorized access to leave request', 403);
            }

            return ResponseFormatter::success(new LeaveRequestResource($leaveRequest), 'Leave request retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Leave request not found: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Simpan permintaan cuti yang baru (Pengajuan oleh Karyawan).
     */
    public function store(Request $request)
    {
        try {
            // 1. Validasi Input
            $validatedData = $request->validate([
                'leave_type_id' => 'required|exists:leave_types,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:500',
                'leave_period' => [
                    'required',
                    Rule::in(['full_day', 'half_day_morning', 'half_day_afternoon']),
                ],
                'supporting_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'signature' => 'nullable|string', // Base64 string
                'use_saved_signature' => 'nullable|boolean',
            ]);

            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('supporting_document')) {
                // Store in 'storage/app/public/attachments' and get the relative path
                $attachmentPath = $request->file('supporting_document')->store('attachments', 'public');
                $attachmentPath = $request->file('supporting_document')->store('attachments', 'public');
            }

            // Handle Signature
            $signaturePath = null;
            if ($request->boolean('use_saved_signature')) {
                // Use saved signature from user profile
                if (Auth::user()->signature_path) {
                    // We copy the file so if user changes profile signature later, this request remains unchanged
                    $originalPath = Auth::user()->signature_path;
                    if (Storage::disk('public')->exists($originalPath)) {
                        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                        $newPath = 'signatures/requests/' . uniqid() . '.' . $extension;
                        Storage::disk('public')->copy($originalPath, $newPath);
                        $signaturePath = $newPath;
                    }
                }
            } elseif ($request->filled('signature')) {
                // Use new signature provided
                $signatureData = $request->input('signature');
                if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
                    $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
                    $type = strtolower($type[1]); 
                    if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                        $signatureData = base64_decode($signatureData);
                        if ($signatureData !== false) {
                            $fileName = 'signatures/requests/' . uniqid() . '.' . $type;
                            Storage::disk('public')->put($fileName, $signatureData);
                            $signaturePath = $fileName;
                        }
                    }
                }
            }

            // Validasi kustom: Cuti setengah hari hanya boleh untuk satu hari.
            if (in_array($validatedData['leave_period'], ['half_day_morning', 'half_day_afternoon'])) {
                if ($validatedData['start_date'] !== $validatedData['end_date']) {
                    throw ValidationException::withMessages([
                        'leave_period' => 'Half-day leave can only be requested for a single day.',
                    ]);
                }
            }

            // 2. Hitung Durasi Cuti
            $duration = $this->calculateDuration($validatedData['start_date'], $validatedData['end_date'], $validatedData['leave_period']);

            // 3. Cek Jatah Cuti
            if (!$this->entitlementService->hasSufficientBalance(Auth::user(), $validatedData['leave_type_id'], $duration)) {
                throw ValidationException::withMessages(['leave' => 'Insufficient leave balance.']);
            }

            // 4. Cari Alur Kerja yang Sesuai
            $workflow = Workflow::where('applicable_model', LeaveRequest::class)->first();
            if (!$workflow) {
                return ResponseFormatter::error(null, 'Leave workflow not found.', 500);
            }

            // Find first step
            $firstStep = $workflow->steps()->orderBy('step_number', 'asc')->first();
            $currentWorkflowStepId = $firstStep ? $firstStep->id : null;

            // 5. Buat Permintaan Cuti
            $leaveRequest = DB::transaction(function () use ($validatedData, $workflow, $duration, $attachmentPath, $currentWorkflowStepId, $signaturePath) {
                return LeaveRequest::create([
                    'user_id' => Auth::id(),
                    'leave_type_id' => $validatedData['leave_type_id'],
                    'workflow_id' => $workflow->id,
                    'current_workflow_step_id' => $currentWorkflowStepId,
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                    'leave_period' => $validatedData['leave_period'],
                    'reason' => $validatedData['reason'],
                    'duration_days' => $duration,
                    'supporting_attachment_path' => $attachmentPath,
                    'signature_path' => $signaturePath,
                    'current_status' => 'Draft', // Selalu mulai dari Draft
                ]);
            });

            return ResponseFormatter::success(new LeaveRequestResource($leaveRequest->load('leaveType')), 'Leave request submitted successfully');
        } catch (ValidationException $e) {
            return ResponseFormatter::error(['errors' => $e->errors()], $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to submit leave request: ' . $e->getMessage(), 500);
        }
    }


    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        try {
            Log::info('Update method called', ['request_data' => $request->all()]);

            // Otorisasi: Pastikan pengguna hanya mengedit permintaan cuti miliknya sendiri.
            $this->authorize('update', $leaveRequest);
            Log::info('Authorization successful');

            // Validasi input dasar
            $validatedData = $request->validate([
                'leave_type_id' => 'sometimes|required|exists:leave_types,id',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after_or_equal:start_date',
                'reason' => 'sometimes|required|string|max:500',
                'current_status' => 'sometimes|required|in:Draft,Pending', // Izinkan perubahan status
                'leave_period' => [
                    'sometimes',
                    'required',
                    Rule::in(['full_day', 'half_day_morning', 'half_day_afternoon']),
                ],
                'supporting_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'signature' => 'nullable|string',
                'use_saved_signature' => 'nullable|boolean',
            ]);
            Log::info('Validation successful', ['validated_data' => $validatedData]);


            // Cek status: Hanya izinkan edit jika statusnya masih 'Draft'.
            if ($leaveRequest->current_status !== 'Draft') {
                // Log::warning('Attempted to update a non-draft leave request', ['leave_request_id' => $leaveRequest->id, 'current_status' => $leaveRequest->current_status]);
                return ResponseFormatter::error(
                    null,
                    'This leave request cannot be edited because it is already being processed.',
                    403
                );
            }

            // Handle file upload
            if ($request->hasFile('supporting_document')) {
                // Hapus file lama jika ada
                if ($leaveRequest->getRawOriginal('supporting_attachment_path')) {
                    Storage::disk('public')->delete($leaveRequest->getRawOriginal('supporting_attachment_path'));
                }

                // Store in 'storage/app/public/attachments' and get the relative path
                $path = $request->file('supporting_document')->store('attachments', 'public');
                $validatedData['supporting_attachment_path'] = $path;
                $path = $request->file('supporting_document')->store('attachments', 'public');
                $validatedData['supporting_attachment_path'] = $path;
            }

            // Handle Signature Update
            if ($request->boolean('use_saved_signature')) {
                 if (Auth::user()->signature_path) {
                    $originalPath = Auth::user()->signature_path;
                    if (Storage::disk('public')->exists($originalPath)) {
                        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
                        $newPath = 'signatures/requests/' . uniqid() . '.' . $extension;
                        Storage::disk('public')->copy($originalPath, $newPath);
                        $validatedData['signature_path'] = $newPath;
                    }
                }
            } elseif ($request->filled('signature')) {
                $signatureData = $request->input('signature');
                if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
                    $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
                    $type = strtolower($type[1]); 
                    if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                        $signatureData = base64_decode($signatureData);
                        if ($signatureData !== false) {
                            $fileName = 'signatures/requests/' . uniqid() . '.' . $type;
                            Storage::disk('public')->put($fileName, $signatureData);
                            $validatedData['signature_path'] = $fileName;
                        }
                    }
                }
            }

            // Hitung ulang durasi jika ada perubahan terkait tanggal atau periode cuti
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
                    $validatedData['duration_days'] = $this->calculateDuration($startDate, $endDate, $leavePeriod);
                }
            }

            // Jika status diubah ke 'Pending', lakukan validasi penuh dan mulai alur kerja
            if (isset($validatedData['current_status']) && $validatedData['current_status'] === 'Pending') {
                // Log::info('Attempting to change status to Pending');

                // Recalculate duration to ensure it's up to date with holidays/weekends
                // This handles cases where the draft was created before holiday logic was added,
                // or if holidays were added/changed since the draft was created.
                $currentStartDate = $validatedData['start_date'] ?? $leaveRequest->start_date;
                $currentEndDate = $validatedData['end_date'] ?? $leaveRequest->end_date;
                $currentLeavePeriod = $validatedData['leave_period'] ?? $leaveRequest->leave_period;
                
                $validatedData['duration_days'] = $this->calculateDuration($currentStartDate, $currentEndDate, $currentLeavePeriod);

                // Validasi field yang wajib ada saat submit
                $submitData = array_merge($leaveRequest->toArray(), $validatedData);
                validator($submitData, [
                    'leave_type_id' => 'required|exists:leave_types,id',
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after_or_equal:start_date',
                    'reason' => 'required|string|max:500',
                    'leave_period' => ['required', Rule::in(['full_day', 'half_day_morning', 'half_day_afternoon'])],
                ])->validate();

                // Cek kembali jatah cuti dengan durasi final
                $finalDuration = $validatedData['duration_days'] ?? $leaveRequest->duration_days;
                $finalLeaveTypeId = $validatedData['leave_type_id'] ?? $leaveRequest->leave_type_id;
                if (!$this->entitlementService->hasSufficientBalance(Auth::user(), $finalLeaveTypeId, $finalDuration)) {
                    throw ValidationException::withMessages(['leave' => 'Insufficient leave balance.']);
                }

                // Cari langkah pertama dari alur kerja yang terkait
                $workflow = $leaveRequest->workflow;
                if (!$workflow) {
                    return ResponseFormatter::error(null, 'Workflow not found for this leave request.', 500);
                }
                $firstStep = $workflow->steps()->orderBy('step_number', 'asc')->first();
                if (!$firstStep) {
                    return ResponseFormatter::error(null, 'Workflow is not configured correctly. No steps found.', 500);
                }

                // Set ID langkah alur kerja saat ini ke langkah pertama
                $validatedData['current_workflow_step_id'] = $firstStep->id;
                Log::info('Setting current_workflow_step_id to first step', ['step_id' => $firstStep->id]);

                // Lakukan pembaruan DULUAN agar status 'Pending' tersimpan sebelum notif
                $leaveRequest->update($validatedData);
                $updatedLeaveRequest = $leaveRequest->fresh();

                // Kirim notifikasi ke karyawan - Leave Request Created
                $updatedLeaveRequest->user->notify(new \App\Notifications\LeaveRequestCreated($updatedLeaveRequest));

                // Kirim notifikasi status update ke karyawan
                SendLeaveRequestStatusUpdatedNotification::dispatch($updatedLeaveRequest);

                // Kirim notifikasi ke approver
                $approver = $this->workflowService->findApproverForStep($updatedLeaveRequest->user, $firstStep);
                // dd($approver);
                if ($approver) {
                    SendLeaveRequestNotification::dispatch($approver, $updatedLeaveRequest);
                    Log::info('Approver notification queued', ['approver_id' => $approver->id]);
                }

                return ResponseFormatter::success(new LeaveRequestResource($updatedLeaveRequest), 'Leave request submitted successfully');
            }

            // Lakukan pembaruan untuk perubahan selain submit (misal, hanya simpan draft)
            $leaveRequest->update($validatedData);
            Log::info('Leave request updated successfully', ['leave_request_id' => $leaveRequest->id]);

            return ResponseFormatter::success(new LeaveRequestResource($leaveRequest->fresh()), 'Leave request updated successfully');
        } catch (ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return ResponseFormatter::error(['errors' => $e->errors()], $e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('An error occurred in update method', ['error' => $e->getMessage()]);
            return ResponseFormatter::error(null, 'Failed to update leave request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Endpoint untuk manajer menyetujui atau menolak permintaan cuti (menggunakan Service Layer).
     */
    public function handleApproval(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'action' => 'required',
            'comments' => 'nullable|string',
            'signature' => 'nullable|string',
        ]);

        try {
            $approver = Auth::user();
            $action = $request->input('action');
            $comments = $request->input('comments');

            $signaturePath = null;
            if ($request->filled('signature')) {
                $signatureData = $request->input('signature');
                if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $type)) {
                    $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif
                    if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                         // Handle invalid type if needed
                    } else {
                        $signatureData = base64_decode($signatureData);
                        if ($signatureData !== false) {
                            $fileName = 'signatures/' . uniqid() . '.' . $type;
                            Storage::disk('public')->put($fileName, $signatureData);
                            $signaturePath = $fileName;
                        }
                    }
                }
            }

            // Panggil Service Layer yang memegang semua logika sequential check.
            $this->leaveRequestService->processApproval($leaveRequest, $approver, $action, $comments, $signaturePath);

            return ResponseFormatter::success(new LeaveRequestResource($leaveRequest->fresh()), 'Approval action recorded successfully.');

        } catch (ValidationException $e) {
            // Menangkap kesalahan validasi, termasuk batasan urutan (sequential check)
            return ResponseFormatter::error($e->errors(), $e->getMessage(), 403);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'A system error occurred.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LeaveRequest  $leaveRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        try {
            // Authorization: Ensure user can only delete their own request
            if ($leaveRequest->user_id !== Auth::id()) {
                return ResponseFormatter::error(null, 'Unauthorized access to delete leave request', 403);
            }

            // Check status: Only allow delete if status is 'Draft' or 'Pending'
            if (!in_array($leaveRequest->current_status, ['Draft', 'Pending'])) {
                return ResponseFormatter::error(
                    null,
                    'Cannot delete leave request with status ' . $leaveRequest->current_status,
                    403
                );
            }

            // Delete attachments if any
            if ($leaveRequest->supporting_attachment_path) {
                Storage::disk('public')->delete($leaveRequest->supporting_attachment_path);
            }
            if ($leaveRequest->signature_path) {
                Storage::disk('public')->delete($leaveRequest->signature_path);
            }

            $leaveRequest->delete();

            return ResponseFormatter::success(null, 'Leave request deleted successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to delete leave request: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Get approval history for the current user (approver log)
     */
    public function getApproverLog(Request $request)
    {
        try {
            $approver = Auth::user();
            
            // Get all approval history where this user was the approver
            $approvalHistory = DB::table('approvals_history as ah')
                ->join('leave_requests as lr', function($join) {
                    $join->on('ah.approvable_id', '=', 'lr.id')
                         ->where('ah.approvable_type', '=', LeaveRequest::class);
                })
                ->join('users as u', 'lr.user_id', '=', 'u.id')
                ->join('leave_types as lt', 'lr.leave_type_id', '=', 'lt.id')
                ->join('workflow_steps as ws', 'ah.workflow_step_id', '=', 'ws.id')
                ->leftJoin('roles as r', 'ws.approver_role_id', '=', 'r.id')
                ->where('ah.approver_user_id', $approver->id)
                ->select(
                    'ah.id as approval_id',
                    'ah.action',
                    'ah.comments',
                    'ah.acted_at',
                    'lr.id as leave_request_id',
                    'lr.start_date',
                    'lr.end_date',
                    'lr.duration_days',
                    'lr.reason',
                    'lr.current_status',
                    'u.name as employee_name',
                    'u.employee_code',
                    'lt.name as leave_type_name',
                    'ws.step_number',
                    'r.name as approver_role_name'
                )
                ->orderBy('ah.acted_at', 'desc')
                ->get();

            return ResponseFormatter::success($approvalHistory, 'Approver log retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve approver log: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Calculate leave duration excluding weekends and public holidays.
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @param string $leavePeriod
     * @return float
     */
    private function calculateDuration($startDate, $endDate, $leavePeriod)
    {
        if (in_array($leavePeriod, ['half_day_morning', 'half_day_afternoon'])) {
            return 0.5;
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Fetch public holidays within the range
        $holidays = PublicHoliday::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                                 ->get()
                                 ->pluck('date')
                                 ->map(function ($date) {
                                     return $date->format('Y-m-d');
                                 })
                                 ->toArray();

        $duration = 0;
        while ($start->lte($end)) {
            // Check if it's not a weekend and not a public holiday
            if (!$start->isWeekend() && !in_array($start->format('Y-m-d'), $holidays)) {
                $duration++;
            }
            $start->addDay();
        }

        return $duration;
    }
}
