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

    public function isApproverForStep(User $approver, WorkflowStep $step, ?Model $requestModel = null): bool
    {
        // 1. Super Admin selalu bisa approve (Override)
        if ($approver->hasRole('Super Admin')) {
            return true;
        }

        // 2. Cek berdasarkan tipe approver
        if ($step->required_approver_type === 'User') {
            return $approver->id === $step->approver_user_id;
        }

        // 3. Role-based approval - MUST check if approver is the designated person in hierarchy
        if ($step->required_approver_type === 'Role') {
            if (!$step->approverRole || !$requestModel) {
                return false;
            }

            // Get requester info
            $requester = $requestModel instanceof \App\Models\LeaveRequest 
                ? $requestModel->user 
                : null;

            if (!$requester) {
                return false;
            }

            $roleName = $step->approverRole->name;
            $plant = $requester->plant;
            $team = $plant?->team;
            $department = $team?->department ?? $requester->department;

            // Check if approver is the designated person for this role in the hierarchy
            switch ($roleName) {
                case 'SPV':
                    // Must be the supervisor of requester's plant
                    return $plant && $plant->supervisor_id === $approver->id;

                case 'SL':
                    // Must be the SL of requester's team
                    return $team && $team->sl_id === $approver->id;

                case 'ASMEN':
                    // Must be the ASMEN of requester's team
                    return $team && $team->asmen_id === $approver->id;

                case 'TL':
                    // Must be the leader or additional leader of requester's team
                    if (!$team) return false;
                    return $team->leader_id === $approver->id 
                        || $team->additional_leader_id === $approver->id;

                case 'Manager':
                    // Must be the head of requester's department
                    return $department && $department->head_id === $approver->id;

                default:
                    // For other roles, check if approver has the role AND is in manager hierarchy
                    if (!$approver->hasRole($roleName)) {
                        return false;
                    }
                    // Additional check: must be in the manager hierarchy
                    return $this->isInManagerHierarchy($requester, $approver);
            }
        }

        // 4. Legacy logic for specific approver types (for backward compatibility)
        if ($requestModel && $requestModel instanceof \App\Models\LeaveRequest) {
            $requester = $requestModel->user;
            
            if ($step->required_approver_type === 'PlantSupervisor') {
                $supervisor = $requester->plant?->supervisor;
                return $supervisor && $supervisor->id === $approver->id;
            }

            if ($step->required_approver_type === 'TeamLeader') {
                $team = $requester->plant?->team;
                if (!$team) return false;
                
                // Leader or Additional Leader
                if ($team->leader_id === $approver->id) return true;
                if ($team->additional_leader_id === $approver->id) return true;
                
                return false;
            }

            if ($step->required_approver_type === 'ShiftLeader') {
                $team = $requester->plant?->team;
                if (!$team) return false;
                
                return $team->sl_id === $approver->id;
            }

            if ($step->required_approver_type === 'AssistantManager') {
                $team = $requester->plant?->team;
                if (!$team) return false;
                
                return $team->asmen_id === $approver->id;
            }

            if ($step->required_approver_type === 'DepartmentHead') {
                // Try via hierarchy first, then direct department
                $deptHead = $requester->plant?->team?->department?->head 
                         ?? $requester->department?->head;
                         
                return $deptHead && $deptHead->id === $approver->id;
            }
        }

        return false;
    }

    /**
     * Check if approver is in the manager hierarchy of the requester
     */
    private function isInManagerHierarchy(User $requester, User $approver): bool
    {
        $currentManager = $requester->manager;
        $maxLevels = 5;
        $level = 0;

        while ($currentManager && $level < $maxLevels) {
            if ($currentManager->id === $approver->id) {
                return true;
            }
            $currentManager = $currentManager->manager;
            $level++;
        }

        return false;
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
        if ($step->approver_user_id) {
            return $step->approverUser;
        }

        // Prioritas 2: Role-based approval - cari di team/plant hierarchy
        if ($step->required_approver_type === 'Role' && $step->approverRole) {
            $roleName = $step->approverRole->name;
            
            // Get user's team and plant
            $plant = $user->plant;
            $team = $plant?->team;
            $department = $team?->department ?? $user->department;
            
            // Map role names to specific positions in hierarchy
            switch ($roleName) {
                case 'SPV':
                    // Supervisor is at plant level
                    return $plant?->supervisor;
                    
                case 'SL':
                    // Section Leader (Shift Leader) is at team level
                    return $team?->sl;
                    
                case 'ASMEN':
                    // Assistant Manager is at team level
                    return $team?->asmen;
                    
                case 'TL':
                    // Team Leader - return primary leader, or additional leader as fallback
                    $leader = $team?->leader;
                    if ($leader) return $leader;
                    return $team?->additionalLeader;
                    
                case 'Manager':
                    // Department Head/Manager
                    return $department?->head;
                    
                default:
                    // For other roles, search up the manager hierarchy
                    return $this->findApproverByRoleInHierarchy($user, $roleName);
            }
        }

        // Legacy logic for specific approver types
        if ($step->required_approver_type === 'PlantSupervisor') {
            return $user->plant?->supervisor;
        }

        if ($step->required_approver_type === 'TeamLeader') {
            $team = $user->plant?->team;
            return $team?->leader ?? $team?->additionalLeader;
        }

        if ($step->required_approver_type === 'ShiftLeader') {
            return $user->plant?->team?->sl;
        }

        if ($step->required_approver_type === 'AssistantManager') {
            return $user->plant?->team?->asmen;
        }

        if ($step->required_approver_type === 'DepartmentHead') {
             return $user->plant?->team?->department?->head 
                 ?? $user->department?->head;
        }

        // Fallback: Manager type without specific role
        if ($step->required_approver_type === 'Manager') {
            return $user->manager;
        }

        return null;
    }

    /**
     * Helper method to find approver by role in manager hierarchy
     */
    private function findApproverByRoleInHierarchy(User $user, string $roleName): ?User
    {
        $currentApprover = $user->manager;
        $maxLevels = 5; // Safeguard against deep hierarchies
        $level = 0;

        // Loop up through manager_id chain until matching role is found
        while ($currentApprover && $level < $maxLevels) {
            if ($currentApprover->hasRole($roleName)) {
                return $currentApprover;
            }
            
            $currentApprover = $currentApprover->manager;
            $level++;
        }

        if ($level >= $maxLevels) {
            Log::warning("Could not find approver with role '{$roleName}' for User ID: {$user->id} within {$maxLevels} levels.", [
                'user_id' => $user->id, 
                'role' => $roleName
            ]);
        }
        
        return null;
    }
}
