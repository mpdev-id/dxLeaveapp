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

    // Inject WorkflowService
    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Menangani tindakan persetujuan/penolakan untuk permintaan cuti.
     */
    public function processApproval(LeaveRequest $request, User $approver, string $action, ?string $comments = null): void
    {
        DB::transaction(function () use ($request, $approver, $action, $comments) {
            // 1. TENTUKAN LANGKAH AKTIF SAAT INI
            $currentStep = $this->workflowService->getNextPendingStep($request, $request->workflow);

            if (!$currentStep) {
                // Alur kerja sudah selesai
                throw ValidationException::withMessages(['approval' => 'Alur kerja sudah selesai atau tidak valid.']);
            }

            // 2. TENTUKAN SIAPA APPROVER YANG SAH UNTUK LANGKAH INI
            $expectedApprover = $this->workflowService->findApproverForStep($request->user, $currentStep);

            // 3. VALIDASI HAK AKSES DAN ORANG (Harus orang yang ditunjuk)
            if (!$expectedApprover || $expectedApprover->id !== $approver->id) {
                throw ValidationException::withMessages(['approval' => 'Anda bukan peninjau yang ditunjuk untuk langkah alur kerja saat ini (' . $currentStep->approverRole->name . ').']);
            }

            // 4. CEK BATASAN BERURUTAN (SEQUENTIAL CHECK)
            if ($currentStep->step_order > 1) {
                // Cari langkah sebelumnya
                $previousStep = $request->workflow->steps()->where('step_order', $currentStep->step_order - 1)->first();

                // Cek apakah langkah sebelumnya sudah disetujui
                $isPreviousApproved = $request->approvalsHistory()
                    ->where('workflow_step_id', $previousStep->id)
                    ->where('action', 'Approved')
                    ->exists();

                if (!$isPreviousApproved) {
                    throw ValidationException::withMessages(['approval' => 'Langkah sebelumnya (' . $previousStep->approverRole->name . ') belum disetujui. Persetujuan harus berurutan.']);
                }
            }

            // 5. CATAT RIWAYAT PERSETUJUAN
            ApprovalHistory::create([
                'approvable_id' => $request->id,
                'approvable_type' => LeaveRequest::class,
                'workflow_step_id' => $currentStep->id,
                'approver_user_id' => $approver->id,
                'action' => $action,
                'comments' => $comments,
            ]);

            // 6. PERBARUI STATUS PERMINTAAN CUTI
            if ($action === 'Rejected') {
                $request->update(['status' => 'Rejected']);
            } else {
                // Cek langkah berikutnya setelah tindakan ini
                $nextStep = $this->workflowService->getNextPendingStep($request, $request->workflow);

                if (!$nextStep) {
                    // Alur kerja selesai, ubah status menjadi Approved
                    $request->update(['status' => 'Approved']);
                    // TODO: Panggil fungsi untuk mengurangi jatah cuti (Entitlement) di sini.
                } else {
                    // Masih ada langkah selanjutnya
                    $request->update(['status' => 'In Progress']);
                }
            }
        });
    }
}
