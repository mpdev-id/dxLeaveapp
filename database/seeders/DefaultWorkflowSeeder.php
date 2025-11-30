<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowStep;

class DefaultWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get the default workflow
        $workflow = Workflow::firstOrCreate(
            ['name' => 'Standard Leave Approval'],
            ['applicable_model' => 'App\Models\LeaveRequest']
        );

        // Clear existing steps
        $workflow->steps()->delete();

        // Step 1: Plant Supervisor
        WorkflowStep::create([
            'workflow_id' => $workflow->id,
            'step_number' => 1,
            'required_approver_type' => 'PlantSupervisor',
            'required_approvals' => 1,
            'is_final_step' => false,
        ]);

        // Step 2: Team Leader
        WorkflowStep::create([
            'workflow_id' => $workflow->id,
            'step_number' => 2,
            'required_approver_type' => 'TeamLeader',
            'required_approvals' => 1,
            'is_final_step' => false,
        ]);

        // Step 3: Department Head
        WorkflowStep::create([
            'workflow_id' => $workflow->id,
            'step_number' => 3,
            'required_approver_type' => 'DepartmentHead',
            'required_approvals' => 1,
            'is_final_step' => true,
        ]);
        
        $this->command->info('Default workflow seeded successfully.');
    }
}
