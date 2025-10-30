<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Layanan untuk mengelola logika Alur Kerja (Workflow) berurutan.
 * Bertanggung jawab menentukan langkah berikutnya dan siapa approver-nya.
 */
class WorkflowService
{
    /**
     * Menemukan langkah alur kerja berikutnya yang harus disetujui.
     */
    public function getNextPendingStep(Model $requestModel, Workflow $workflow): ?WorkflowStep
    {
        // 1. Dapatkan ID dari langkah-langkah yang sudah disetujui (Approved)
        $approvedSteps = $requestModel->approvalsHistory()
            ->where('action', 'Approved')
            ->pluck('workflow_step_id');

        // 2. Dapatkan semua langkah dalam alur kerja, diurutkan
        $allSteps = $workflow->steps()->orderBy('step_order')->get();

        // 3. Temukan langkah pertama (berdasarkan step_order) yang ID-nya belum ada di approvedSteps.
        return $allSteps->first(function (WorkflowStep $step) use ($approvedSteps) {
            return !$approvedSteps->contains($step->id);
        });
    }

    /**
     * Menemukan pengguna (manajer) yang bertanggung jawab untuk langkah persetujuan saat ini.
     */
    public function findApproverForStep(User $user, WorkflowStep $step): ?User
    {
        $currentApprover = $user->manager;

        // Loop ke atas melalui rantai manager_id hingga peran yang sesuai ditemukan.
        while ($currentApprover) {
            // Periksa apakah manager saat ini memiliki peran yang dibutuhkan oleh langkah alur kerja
            // Peran diambil dari relasi approverRole di WorkflowStep Model.
            if ($currentApprover->hasRole($step->approverRole->name)) {
                return $currentApprover;
            }

            // Pindah ke atasan manajer (naik satu tingkat)
            $currentApprover = $currentApprover->manager;
        }

        // Log::warning("Tidak dapat menemukan approver untuk User ID: {$user->id} pada step: {$step->id}");
        return null;
    }
}
