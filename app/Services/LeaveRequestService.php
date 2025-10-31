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
            // ... (Logika validasi dan pencatatan riwayat yang sudah ada)

            // 6. PERBARUI STATUS PERMINTAAN CUTI
            if ($action === 'Rejected') {
                $request->update(['current_status' => 'Rejected']);
            } else { // Action is 'Approved'
                // Cek langkah berikutnya setelah tindakan ini
                $nextStep = $this->workflowService->getNextPendingStep($request, $request->workflow);

                if (!$nextStep) {
                    // Alur kerja selesai, ubah status menjadi Approved
                    $request->update(['current_status' => 'Approved']);
                    
                    // Panggil fungsi untuk mengurangi jatah cuti
                    $this->entitlementService->deductEntitlement($request);

                } else {
                    // Masih ada langkah selanjutnya
                    $request->update(['current_status' => 'In Progress']);
                }
            }
        });
    }
}