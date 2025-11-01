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
    public function getCurrentStep(Model $requestModel): ?WorkflowStep
    {
        return $requestModel->currentStep;
    }

    public function isApproverForStep(User $approver, WorkflowStep $step): bool
    {
        // Cek apakah peran approver cocok dengan peran yang dibutuhkan di langkah ini
        return $approver->hasRole($step->approverRole->name);
    }

    public function getNextStep(Workflow $workflow, WorkflowStep $currentStep): ?WorkflowStep
    {
        // Temukan langkah berikutnya berdasarkan urutan
        return $workflow->steps()
            ->where('step_number', '>', $currentStep->step_number)
            ->orderBy('step_number', 'asc')
            ->first();
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
