<?php

namespace App\Services;

use App\Jobs\SendLeaveRequestNotification;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\ApprovalHistory;
use App\Notifications\LeaveRequestStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    protected $workflowService;
    protected $entitlementService; // Tambahkan properti

    // Inject WorkflowService dan EntitlementService
    public function __construct(WorkflowService $workflowService, EntitlementService $entitlementService)
    {
        $this->workflowService = $workflowService;
        $this->entitlementService = $entitlementService; // Inisialisasi properti
    }

    /**
     * Menangani tindakan persetujuan/penolakan untuk permintaan cuti.
     */
    public function processApproval(LeaveRequest $request, User $approver, string $action, ?string $comments = null, ?string $signaturePath = null): void
    {
        DB::transaction(function () use ($request, $approver, $action, $comments, $signaturePath) {
            $currentStep = $this->workflowService->getCurrentStep($request);

            if (!$currentStep) {
                throw ValidationException::withMessages(['workflow' => 'No pending approval step found for this request.']);
            }

            // Cek apakah approver memiliki peran yang sesuai untuk langkah ini
            if (!$this->workflowService->isApproverForStep($approver, $currentStep, $request)) {
                throw ValidationException::withMessages(['authorization' => 'You are not authorized to approve this step.']);
            }

            // 1. CATAT RIWAYAT PERSETUJUAN
            ApprovalHistory::create([
                'approvable_id' => $request->id,
                'approvable_type' => LeaveRequest::class,
                'workflow_step_id' => $currentStep->id,
                'approver_user_id' => $approver->id,
                'action' => $action,
                'comments' => $comments,
                'signature_path' => $signaturePath,
            ]);

            // 2. PERBARUI STATUS PERMINTAAN CUTI
            $nextStep = $this->workflowService->getNextStep($request->workflow, $currentStep);

            if ($action === 'Rejected') {
                $request->update([
                    'current_status' => 'Rejected',
                    'current_workflow_step_id' => null, // Hentikan alur kerja
                ]);
            } elseif ($action === 'Approved') {
                // Logic to find the next valid step with an approver
                $nextStep = $this->workflowService->getNextStep($request->workflow, $currentStep);
                $nextApprover = null;

                while ($nextStep) {
                    $nextApprover = $this->workflowService->findApproverForStep($request->user, $nextStep);
                    
                    if ($nextApprover) {
                        // Found an approver for this step, break the loop
                        break;
                    }

                    // No approver found for this step, automatically approve/skip it
                    // Log the auto-approval
                    ApprovalHistory::create([
                        'approvable_id' => $request->id,
                        'approvable_type' => LeaveRequest::class,
                        'workflow_step_id' => $nextStep->id,
                        'approver_user_id' => null, // System
                        'action' => 'Auto-Approved',
                        'comments' => 'System: Step skipped (No approver found).',
                        'acted_at' => now(),
                    ]);

                    // Move to the next step
                    $nextStep = $this->workflowService->getNextStep($request->workflow, $nextStep);
                }

                if (!$nextStep) {
                    // No more steps, workflow finished
                    $request->update([
                        'current_status' => 'Approved',
                        'current_workflow_step_id' => null,
                    ]);
                    // Deduct leave balance
                    $this->entitlementService->deductLeaveBalance($request);
                    
                    // Notify user of final approval
                    // (Notification logic is at the end of method)
                } else {
                    // Move to the next valid step
                    $request->update([
                        'current_status' => 'In Progress',
                        'current_workflow_step_id' => $nextStep->id,
                    ]);

                    // Notify the next approver
                    if ($nextApprover) {
                        SendLeaveRequestNotification::dispatch($nextApprover, $request->fresh());
                    }
                }
            }
        });

        // 3. KIRIM NOTIFIKASI KE PENGGUNA
        // Muat ulang model untuk mendapatkan status terbaru sebelum mengirim notifikasi
        $leaveRequest = $request->fresh();
        $reason = ($action === 'Rejected') ? $comments : null;
        $leaveRequest->user->notify(new LeaveRequestStatusUpdated($leaveRequest, $reason));
    }
}