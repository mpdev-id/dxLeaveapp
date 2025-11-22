@extends('template.admin')

@section('title', 'Create Workflow')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-base-content">Create Workflow</h1>
            <p class="text-base-content/70 mt-1">Define a new approval workflow for your application.</p>
        </div>
        <a href="{{ route('admin.workflows.index') }}" class="btn btn-ghost gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Back to List
        </a>
    </div>

    <form action="{{ route('admin.workflows.store') }}" method="POST" x-data="workflowForm()" class="space-y-8">
        @csrf

        <!-- Main Workflow Info -->
        <div class="card bg-base-100 shadow-xl border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-xl mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Basic Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-medium">Workflow Name</span>
                        </label>
                        <input type="text" name="name" class="input input-bordered w-full focus:input-primary" placeholder="e.g., Leave Approval Process" required>
                    </div>
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-medium">Applicable Model</span>
                        </label>
                        <input type="text" name="applicable_model" class="input input-bordered w-full focus:input-primary font-mono text-sm" placeholder="e.g., App\Models\LeaveRequest" value="App\Models\LeaveRequest" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow Steps -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Workflow Steps
                </h3>
                <button type="button" class="btn btn-secondary btn-sm gap-2" @click="addStep()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add Step
                </button>
            </div>

            <div class="space-y-6">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="card bg-base-100 shadow-lg border border-base-200 relative overflow-visible transition-all hover:shadow-xl">
                        <!-- Step Number Badge -->
                        <div class="absolute -left-3 -top-3 z-10">
                            <div class="badge badge-primary badge-lg shadow-md font-bold p-4" x-text="`Step ${index + 1}`"></div>
                        </div>

                        <div class="card-body p-6 pt-8">
                            <div class="absolute top-4 right-4">
                                <button type="button" class="btn btn-ghost btn-square btn-sm text-error hover:bg-error/10" @click="removeStep(index)" x-show="steps.length > 1" title="Remove Step">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <input type="hidden" :name="`steps[${index}][step_number]`" :value="index + 1">

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                <!-- Approver Type -->
                                <div class="md:col-span-3 form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Approver Type</span>
                                    </label>
                                    <select :name="`steps[${index}][required_approver_type]`" class="select select-bordered w-full focus:select-primary" x-model="step.required_approver_type" required>
                                        <option value="Role">Role-based</option>
                                        <option value="User">Specific User</option>
                                        <option value="Manager">Direct Manager</option>
                                    </select>
                                </div>

                                <!-- Dynamic Selection Area -->
                                <div class="md:col-span-6">
                                    <!-- Role Selection -->
                                    <div x-show="step.required_approver_type === 'Role'" class="form-control w-full">
                                        <label class="label">
                                            <span class="label-text font-medium">Select Role</span>
                                        </label>
                                        <select :name="`steps[${index}][approver_role_id]`" class="select select-bordered w-full focus:select-primary" x-model="step.approver_role_id">
                                            <option value="">-- Choose a Role --</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        
                                        <!-- Users in Role Preview -->
                                        <div x-show="step.approver_role_id" x-transition.opacity class="mt-3 p-4 bg-base-200/50 rounded-lg border border-base-200">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Users in this role</span>
                                                <span class="badge badge-sm badge-ghost" x-text="getUsersByRole(step.approver_role_id).length + ' users'"></span>
                                            </div>
                                            
                                            <div class="flex flex-wrap gap-2 mb-3">
                                                <template x-for="user in getUsersByRole(step.approver_role_id).slice(0, 5)" :key="user.id">
                                                    <div class="badge badge-outline bg-base-100 gap-1 pr-3 py-3">
                                                        <div class="avatar placeholder">
                                                            <div class="bg-neutral-focus text-neutral-content rounded-full w-5">
                                                                <span class="text-xs" x-text="user.name.charAt(0)"></span>
                                                            </div>
                                                        </div>
                                                        <span x-text="user.name"></span>
                                                    </div>
                                                </template>
                                                <span x-show="getUsersByRole(step.approver_role_id).length > 5" class="text-xs text-base-content/50 self-center ml-1" x-text="'+' + (getUsersByRole(step.approver_role_id).length - 5) + ' more'"></span>
                                                <span x-show="getUsersByRole(step.approver_role_id).length === 0" class="text-sm text-base-content/50 italic">No users found.</span>
                                            </div>
                                            
                                            <div class="divider my-1"></div>
                                            
                                            <div class="form-control w-full">
                                                <label class="label cursor-pointer justify-start gap-2 py-0">
                                                    <span class="label-text text-xs font-medium">Assign Specific Approver (Optional)</span>
                                                    <div class="tooltip tooltip-right" data-tip="If selected, only this specific user from the role can approve.">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-info shrink-0 w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    </div>
                                                </label>
                                                <select :name="`steps[${index}][approver_user_id]`" class="select select-bordered select-sm w-full mt-2" x-model="step.approver_user_id">
                                                    <option value="">Any user with this role</option>
                                                    <template x-for="user in getUsersByRole(step.approver_role_id)" :key="user.id">
                                                        <option :value="user.id" x-text="user.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- User Selection -->
                                    <div x-show="step.required_approver_type === 'User'" class="form-control w-full">
                                        <label class="label">
                                            <span class="label-text font-medium">Select User</span>
                                        </label>
                                        <select :name="`steps[${index}][approver_user_id]`" class="select select-bordered w-full focus:select-primary" x-model="step.approver_user_id">
                                            <option value="">-- Choose a User --</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <!-- Manager Info -->
                                    <div x-show="step.required_approver_type === 'Manager'" class="alert alert-info shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>The approver will be dynamically assigned based on the requester's direct manager.</span>
                                    </div>
                                </div>

                                <!-- Step Options -->
                                <div class="md:col-span-3 space-y-4">
                                    <div class="form-control w-full">
                                        <label class="label">
                                            <span class="label-text font-medium">Approval Requirement</span>
                                        </label>
                                        <select :name="`steps[${index}][required_approvals]`" class="select select-bordered w-full" required>
                                            <option value="1">Required</option>
                                            <option value="0">Optional (Notification Only)</option>
                                        </select>
                                    </div>

                                    <div class="form-control p-3 bg-base-200/50 rounded-lg border border-base-200">
                                        <label class="cursor-pointer label justify-between">
                                            <span class="label-text font-medium">Is Final Step?</span>
                                            <input type="hidden" :name="`steps[${index}][is_final_step]`" value="0">
                                            <input type="checkbox" :name="`steps[${index}][is_final_step]`" class="checkbox checkbox-primary" value="1" x-model="step.is_final_step">
                                        </label>
                                        <p class="text-xs text-base-content/60 mt-1 px-1">If checked, approval at this step completes the workflow.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <div class="mt-8 text-center" x-show="steps.length === 0">
                <div class="alert alert-warning shadow-lg max-w-md mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>Please add at least one step to the workflow.</span>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.workflows.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary gap-2 px-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Save Workflow
            </button>
        </div>
    </form>

    <script>
        function workflowForm() {
            return {
                steps: [
                    {
                        required_approver_type: 'Role',
                        approver_role_id: '',
                        approver_user_id: '',
                        required_approvals: 1,
                        is_final_step: false
                    }
                ],
                users: @json($users),
                roles: @json($roles),
                getUsersByRole(roleId) {
                    if (!roleId) return [];
                    // Assuming users have a 'roles' relationship loaded or we filter based on a property
                    // Since $users is just User::all(), we might not have role info directly unless eager loaded.
                    // Let's assume for now we show all users, or better, we need to pass users with roles.
                    // Ideally, we should filter users who have the selected role.
                    // For this implementation, let's filter client-side if we have the data, 
                    // OR just show all users but maybe group them? 
                    // The user asked: "show who the people name with this role... please show all the user with the same role"
                    
                    // We need to pass users with their roles to the view.
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
