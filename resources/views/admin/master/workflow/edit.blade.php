@extends('template.admin')

@section('title', 'Edit Workflow')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Workflow</h1>
        <a href="{{ route('admin.workflows.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <form action="{{ route('admin.workflows.update', $workflow) }}" method="POST" x-data="workflowForm()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Workflow Name</span>
                </label>
                <input type="text" name="name" class="input input-bordered" value="{{ $workflow->name }}" required>
            </div>
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Applicable Model</span>
                </label>
                <input type="text" name="applicable_model" class="input input-bordered" value="{{ $workflow->applicable_model }}" required>
            </div>
        </div>

        <h3 class="text-xl font-semibold mb-4">Workflow Steps</h3>

        <div class="space-y-4">
            <template x-for="(step, index) in steps" :key="index">
                <div class="card bg-base-200 shadow-sm border border-base-300">
                    <div class="card-body p-4">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-bold">Step <span x-text="index + 1"></span></h4>
                            <button type="button" class="btn btn-xs btn-error" @click="removeStep(index)" x-show="steps.length > 1">Remove</button>
                        </div>

                        <input type="hidden" :name="`steps[${index}][step_number]`" :value="index + 1">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Approver Type</span>
                                </label>
                                <select :name="`steps[${index}][required_approver_type]`" class="select select-bordered" x-model="step.required_approver_type" required>
                                    <option value="Role">Role</option>
                                    <option value="User">User</option>
                                    <option value="Manager">Manager</option>
                                </select>
                            </div>

                            <div class="form-control" x-show="step.required_approver_type === 'Role'">
                                <label class="label">
                                    <span class="label-text">Select Role</span>
                                </label>
                                <select :name="`steps[${index}][approver_role_id]`" class="select select-bordered" x-model="step.approver_role_id">
                                    <option value="">Select Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>

                                <!-- Show users with this role -->
                                <div x-show="step.approver_role_id" class="mt-2 p-2 bg-base-100 rounded border border-base-300">
                                    <p class="text-xs font-bold mb-1 text-gray-500">Users with this role:</p>
                                    <ul class="text-xs list-disc list-inside mb-2">
                                        <template x-for="user in getUsersByRole(step.approver_role_id)" :key="user.id">
                                            <li x-text="user.name"></li>
                                        </template>
                                        <li x-show="getUsersByRole(step.approver_role_id).length === 0" class="text-gray-400 italic">No users found with this role.</li>
                                    </ul>
                                    
                                    <label class="label cursor-pointer justify-start gap-2">
                                        <span class="label-text text-xs">Specific Approver (Optional)</span>
                                    </label>
                                    <select :name="`steps[${index}][approver_user_id]`" class="select select-bordered select-sm w-full" x-model="step.approver_user_id">
                                        <option value="">Any user with this role</option>
                                        <template x-for="user in getUsersByRole(step.approver_role_id)" :key="user.id">
                                            <option :value="user.id" x-text="user.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div class="form-control" x-show="step.required_approver_type === 'User'">
                                <label class="label">
                                    <span class="label-text">Select User</span>
                                </label>
                                <select :name="`steps[${index}][approver_user_id]`" class="select select-bordered" x-model="step.approver_user_id">
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Required Approvals</span>
                                </label>
                                <select :name="`steps[${index}][required_approvals]`" class="select select-bordered" x-model="step.required_approvals" required>
                                    <option value="1">Required</option>
                                    <option value="0">Optional</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-control mt-2">
                            <label class="cursor-pointer label justify-start gap-2">
                                <input type="hidden" :name="`steps[${index}][is_final_step]`" value="0">
                                <input type="checkbox" :name="`steps[${index}][is_final_step]`" class="checkbox checkbox-primary" value="1" x-model="step.is_final_step">
                                <span class="label-text">Is Final Step?</span>
                            </label>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-4">
            <button type="button" class="btn btn-secondary btn-sm" @click="addStep()">+ Add Step</button>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="btn btn-primary">Update Workflow</button>
        </div>
    </form>

    @php
        $stepsData = $workflow->steps->map(function($step) {
            return [
                'required_approver_type' => $step->required_approver_type,
                'approver_role_id' => $step->approver_role_id,
                'approver_user_id' => $step->approver_user_id,
                'required_approvals' => $step->required_approvals,
                'is_final_step' => (bool) $step->is_final_step,
            ];
        });
    @endphp

    <script>
        function workflowForm() {
            return {
                steps: @json($stepsData),
                users: @json($users),
                roles: @json($roles),
                getUsersByRole(roleId) {
                    if (!roleId) return [];
                    return this.users.filter(user => {
                        return user.roles.some(r => r.id == roleId);
                    });
                },
                addStep() {
                    this.steps.push({
                        required_approver_type: 'Role',
                        approver_role_id: '',
                        approver_user_id: '',
                        required_approvals: 1,
                        is_final_step: false
                    });
                },
                removeStep(index) {
                    this.steps.splice(index, 1);
                }
            }
        }
    </script>
@endsection
