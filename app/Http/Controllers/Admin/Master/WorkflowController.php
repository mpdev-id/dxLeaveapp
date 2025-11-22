<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{
    public function index()
    {
        $workflows = Workflow::with('steps')->get();
        return view('admin.master.workflow.index', compact('workflows'));
    }

    public function create()
    {
        $roles = Role::all();
        $users = User::with('roles')->get();
        return view('admin.master.workflow.create', compact('roles', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'applicable_model' => 'required|string|max:255',
            'steps' => 'required|array|min:1',
            'steps.*.step_number' => 'required|integer',
            'steps.*.required_approver_type' => 'required|in:Role,User,Manager',
            'steps.*.approver_role_id' => 'nullable|exists:roles,id',
            'steps.*.approver_user_id' => 'nullable|exists:users,id',
            'steps.*.required_approvals' => 'required|integer|min:0',
            'steps.*.is_final_step' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            $workflow = Workflow::create([
                'name' => $request->name,
                'applicable_model' => $request->applicable_model,
            ]);

            foreach ($request->steps as $stepData) {
                $workflow->steps()->create([
                    'step_number' => $stepData['step_number'],
                    'required_approver_type' => $stepData['required_approver_type'],
                    'approver_role_id' => !empty($stepData['approver_role_id']) ? $stepData['approver_role_id'] : null,
                    'approver_user_id' => !empty($stepData['approver_user_id']) ? $stepData['approver_user_id'] : null,
                    'required_approvals' => $stepData['required_approvals'],
                    'is_final_step' => $stepData['is_final_step'] ?? false,
                ]);
            }
        });

        return redirect()->route('admin.workflows.index')->with('success', 'Workflow created successfully.');
    }

    public function edit(Workflow $workflow)
    {
        $workflow->load('steps');
        $roles = Role::all();
        $users = User::with('roles')->get();
        return view('admin.master.workflow.edit', compact('workflow', 'roles', 'users'));
    }

    public function update(Request $request, Workflow $workflow)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'applicable_model' => 'required|string|max:255',
            'steps' => 'required|array|min:1',
            'steps.*.step_number' => 'required|integer',
            'steps.*.required_approver_type' => 'required|in:Role,User,Manager',
            'steps.*.approver_role_id' => 'nullable|exists:roles,id',
            'steps.*.approver_user_id' => 'nullable|exists:users,id',
            'steps.*.required_approvals' => 'required|integer|min:0',
            'steps.*.is_final_step' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $workflow) {
            $workflow->update([
                'name' => $request->name,
                'applicable_model' => $request->applicable_model,
            ]);

            // Delete existing steps and recreate them (simplest approach for now)
            // Or sync them if we want to keep IDs, but recreating is safer for order changes
            $workflow->steps()->delete();

            foreach ($request->steps as $stepData) {
                $workflow->steps()->create([
                    'step_number' => $stepData['step_number'],
                    'required_approver_type' => $stepData['required_approver_type'],
                    'approver_role_id' => !empty($stepData['approver_role_id']) ? $stepData['approver_role_id'] : null,
                    'approver_user_id' => !empty($stepData['approver_user_id']) ? $stepData['approver_user_id'] : null,
                    'required_approvals' => $stepData['required_approvals'],
                    'is_final_step' => $stepData['is_final_step'] ?? false,
                ]);
            }
        });

        return redirect()->route('admin.workflows.index')->with('success', 'Workflow updated successfully.');
    }

    public function destroy(Workflow $workflow)
    {
        $workflow->steps()->delete();
        $workflow->delete();
        return redirect()->route('admin.workflows.index')->with('success', 'Workflow deleted successfully.');
    }
}
