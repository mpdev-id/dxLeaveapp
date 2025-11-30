<?php

namespace Database\Seeders;

use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UniversalLeaveWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates a universal workflow for leave approval that works across all teams.
     * Steps are role-based and will auto-skip if the role doesn't exist in the user's hierarchy.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Define workflow name
            $workflowName = 'Universal Leave Approval';

            // Delete old workflow if exists to avoid duplication
            Workflow::where('name', $workflowName)->delete();

            // Create main Workflow record
            $leaveWorkflow = Workflow::create([
                'name' => $workflowName,
                'applicable_model' => \App\Models\LeaveRequest::class,
            ]);

            // Define approval hierarchy
            // SPV, SL, ASMEN are optional (will auto-skip if not found)
            // TL and Manager are required
            $approvalSteps = [
                [
                    'role_name' => 'SPV',
                    'step_number' => 1,
                    'is_optional' => true,
                ],
                [
                    'role_name' => 'SL',
                    'step_number' => 2,
                    'is_optional' => true,
                ],
                [
                    'role_name' => 'ASMEN',
                    'step_number' => 3,
                    'is_optional' => true,
                ],
                [
                    'role_name' => 'TL',
                    'step_number' => 4,
                    'is_optional' => false, // Required
                ],
                [
                    'role_name' => 'Manager',
                    'step_number' => 5,
                    'is_optional' => false, // Required
                ],
            ];

            // Create workflow steps
            foreach ($approvalSteps as $index => $stepData) {
                $role = Role::where('name', $stepData['role_name'])->first();

                if ($role) {
                    WorkflowStep::create([
                        'workflow_id' => $leaveWorkflow->id,
                        'approver_role_id' => $role->id,
                        'step_number' => $stepData['step_number'],
                        'required_approver_type' => 'Role',
                        'is_final_step' => ($index == count($approvalSteps) - 1),
                        'approver_user_id' => null, // Role-based, not user-specific
                    ]);

                    $this->command->info("✓ Created step {$stepData['step_number']}: {$stepData['role_name']}" . 
                        ($stepData['is_optional'] ? ' (Optional - will auto-skip if not found)' : ' (Required)'));
                } else {
                    $this->command->warn("⚠ Role '{$stepData['role_name']}' not found. Skipping step.");
                }
            }

            $this->command->info("\n✅ Universal Leave Approval workflow created successfully!");
            $this->command->info("📋 This workflow will work for all teams and departments.");
            $this->command->info("🔄 Steps will auto-skip if the role doesn't exist in the user's hierarchy.");
        });
    }
}
