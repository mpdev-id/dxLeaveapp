<?php

namespace Database\Seeders;

use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class LeaveApprovalWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Definisikan nama alur kerja
            $workflowName = 'Persetujuan Cuti 5 Langkah';

            // Hapus workflow lama jika ada untuk menghindari duplikasi
            Workflow::where('name', $workflowName)->delete();

            // 2. Buat record Workflow utama
            $leaveWorkflow = Workflow::create([
                'name' => $workflowName,
                'applicable_model' => \App\Models\LeaveRequest::class,
            ]);

            // 4. Buat setiap langkah (WorkflowStep) secara berurutan
            $roles = [
                'SPV', 
                'SL', 
                'ASMEN', 
                'TeamLeader', 
                'Manager'
            ];

            foreach ($roles as $index => $roleName) {
                $role = Role::where('name', $roleName)->first();

                // Pastikan role ada sebelum membuat step
                if ($role) {
                    WorkflowStep::create([
                        'workflow_id' => $leaveWorkflow->id,
                        'approver_role_id' => $role->id,
                        'step_number' => $index + 1,
                        'required_approver_type' => 'Role',
                        'is_final_step' => ($index == count($roles) - 1),
                    ]);
                }
            }
        });
    }
}