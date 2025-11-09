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
        // Prioritas 1: Jika langkah alur kerja memiliki approverUser spesifik
        try {
            return $step->approverUser;
        } catch (\Throwable $th) {
            Log::warning('Gagal menemukan approverUser untuk langkah alur kerja', [
                'step' => $step,
                'error' => $th->getMessage(),
            ]);
        }

        // Prioritas 2: Jika tidak ada approverUser spesifik, cari berdasarkan peran di hierarki manajer
        // $currentApprover = $user->manager;
        // $maxLevels = 5; // Safeguard against deep hierarchies
        // $level = 0;

        // Loop ke atas melalui rantai manager_id hingga peran yang sesuai ditemukan.
        // while ($currentApprover && $level < $maxLevels) {
        //     // Periksa apakah manager saat ini memiliki peran yang dibutuhkan oleh langkah alur kerja
        //     if ($currentApprover->hasRole($step->approverRole->name)) {
        //         return $currentApprover;
        //     }

        //     // Pindah ke atasan manajer (naik satu tingkat)
        //     $currentApprover = $currentApprover->manager;
        //     $level++;
        // }

        // if ($level >= $maxLevels) {
        //     Log::warning("Could not find an approver for User ID: {$user->id} within {$maxLevels} levels based on role '{$step->approverRole->name}'. Manager hierarchy might be too deep or misconfigured.", ['user_id' => $user->id, 'step_id' => $step->id]);
        // } else {
        //     Log::warning("Could not find an approver with the required role '{$step->approverRole->name}' for User ID: {$user->id} on step: {$step->id}. No specific approver user defined and no manager with the role found.", [
        //         'user_id' => $user->id,
        //         'step_id' => $step->id,
        //         'role_needed' => $step->approverRole->name ?? 'N/A'
        //     ]);
        // }
        // return null;
    }
}
