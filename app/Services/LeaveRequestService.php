<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\ApprovalHistory;
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
    public function processApproval(LeaveRequest $request, User $approver, string $action, ?string $comments = null): void
    {
        DB::transaction(function () use ($request, $approver, $action, $comments) {
            $currentStep = $this->workflowService->getCurrentStep($request);

            if (!$currentStep) {
                throw ValidationException::withMessages(['workflow' => 'No pending approval step found for this request.']);
            }

            // Cek apakah approver memiliki peran yang sesuai untuk langkah ini
            if (!$this->workflowService->isApproverForStep($approver, $currentStep)) {
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
                'acted_at' => now(),
            ]);

            // 2. PERBARUI STATUS PERMINTAAN CUTI
            $nextStep = $this->workflowService->getNextStep($request->workflow, $currentStep);

            if ($action === 'Rejected') {
                $request->update([
                    'current_status' => 'Rejected',
                    'current_workflow_step_id' => null, // Hentikan alur kerja
                ]);
            } elseif ($action === 'Approved') {
                if (!$nextStep) {
                    // Langkah terakhir, alur kerja selesai
                    $request->update([
                        'current_status' => 'Approved',
                        'current_workflow_step_id' => null,
                    ]);
                    // Kurangi jatah cuti
                    $this->entitlementService->deductLeaveBalance($request);
                } else {
                    // Lanjut ke langkah berikutnya
                    $request->update([
                        'current_status' => 'In Progress',
                        'current_workflow_step_id' => $nextStep->id,
                    ]);
                }
            }
        });
    }
}