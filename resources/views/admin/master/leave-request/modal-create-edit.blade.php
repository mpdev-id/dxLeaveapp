<!-- Create/Edit Modal -->
<dialog id="create_edit_modal" class="modal">
    <div class="modal-box w-11/12 max-w-2xl">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" @click="resetForm()" onclick="hideModal('create_edit_modal')">✕</button>
        <h3 class="font-bold text-lg" x-text="editMode ? 'Edit Leave Request' : 'Create New Leave Request'"></h3>

        <form @submit.prevent="saveRequest()">
            <div class="py-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Leave Period -->
                  <div>
                    <label class="label">
                        <span class="label-text">Leave Period</span>
                    </label>
                    <select x-model="formData.leave_period"
                        @change="if ($event.target.value !== 'full_day' && formData.start_date) formData.end_date = formData.start_date"
                        class="select select-bordered w-full">
                        <option value="full_day">Full Day</option>
                        <option value="half_day_morning">Half Day (Morning)</option>
                        <option value="half_day_afternoon">Half Day (Afternoon)</option>
                    </select>
                     <p x-show="formErrors.leave_period" class="text-error text-sm mt-1" x-text="formErrors.leave_period"></p>
                </div>
                <!-- User -->
                <div>
                    <label class="label">
                        <span class="label-text">User</span>
                    </label>
                    <select x-model="formData.user_id" class="select select-bordered w-full"
                        @change="formData.leave_type_id = ''"
                        :disabled="editMode">
                        <option disabled value="">Select User</option>
                        <template x-for="user in users" :key="user.id">
                            <option :value="user.id" x-text="user.name"></option>
                        </template>
                    </select>
                    <p x-show="formErrors.user_id" class="text-error text-sm mt-1" x-text="formErrors.user_id"></p>
                </div>

                <!-- Leave Type -->
                <div>
                    <label class="label">
                        <span class="label-text">Leave Type</span>
                    </label>
                    <select x-model="formData.leave_type_id" class="select select-bordered w-full" :disabled="!formData.user_id">
                        <option disabled value="">Select Leave Type</option>
                        <template x-for="leaveType in leaveTypes.filter(lt => employeeEntitlements.some(e => e.user_id == formData.user_id && e.leave_type_id == lt.id))" :key="leaveType.id">
                            <option :value="leaveType.id" x-text="leaveType.name + ' - ' + (employeeEntitlements.find(e => e.user_id == formData.user_id && e.leave_type_id == leaveType.id).initial_balance - employeeEntitlements.find(e => e.user_id == formData.user_id && e.leave_type_id == leaveType.id).days_taken) + ' hari'"></option>
                        </template>
                    </select>
                    <p x-show="formErrors.leave_type_id" class="text-error text-sm mt-1" x-text="formErrors.leave_type_id"></p>
                </div>
                
                <!-- Workflow -->
                <div>
                    <label class="label">
                        <span class="label-text">Workflow</span>
                    </label>
                    <select x-model="formData.workflow_id" class="select select-bordered w-full">
                        <option disabled value="">Select Workflow</option>
                        <template x-for="workflow in workflows" :key="workflow.id">
                            <option :value="workflow.id" x-text="workflow.name"></option>
                        </template>
                    </select>
                    <p x-show="formErrors.workflow_id" class="text-error text-sm mt-1" x-text="formErrors.workflow_id"></p>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="label">
                        <span class="label-text">Start Date</span>
                    </label>
                    <input type="date" x-model="formData.start_date"
                        @change="if (formData.leave_period !== 'full_day') formData.end_date = formData.start_date"
                        class="input input-bordered w-full">
                    <p x-show="formErrors.start_date" class="text-error text-sm mt-1" x-text="formErrors.start_date"></p>
                </div>

                <!-- End Date -->
                <div>
                    <label class="label">
                        <span class="label-text">End Date</span>
                    </label>
                    <input type="date" x-model="formData.end_date"
                        :disabled="formData.leave_period !== 'full_day'"
                        class="input input-bordered w-full disabled:bg-base-200">
                     <p x-show="formErrors.end_date" class="text-error text-sm mt-1" x-text="formErrors.end_date"></p>
                </div>

                
                <!-- Status (Editable) -->
                <div x-show="!editMode || (editMode && formData.current_status !== 'Draft' && formData.current_status !== 'Approved')">
                    <label class="label">
                        <span class="label-text" x-text="editMode ? 'Current Status' : 'Initial Status'"></span>
                    </label>
                    <select x-model="formData.current_status" class="select select-bordered w-full">
                        <!-- Create Mode Options -->
                        <option value="Draft" x-show="!editMode">Save as Draft</option>
                        <option value="Pending" x-show="!editMode">Submit for Approval</option>
                        
                        <!-- Edit Mode Options -->
                        <option value="Draft" x-show="editMode">Draft</option>
                        <option value="Pending" x-show="editMode">Pending</option>
                        <option value="Approved" x-show="editMode">Approved</option>
                        <option value="Rejected" x-show="editMode">Rejected</option>
                        <option value="Canceled" x-show="editMode">Canceled</option>
                    </select>
                    <p x-show="formErrors.current_status" class="text-error text-sm mt-1" x-text="formErrors.current_status"></p>
                    <p x-show="editMode && formData.current_status === 'Draft'" class="text-warning text-sm mt-1">
                        ⚠️ Changing to Draft will reset the workflow progress
                    </p>
                </div>

                <!-- Status (Read-only for Draft and Approved) -->
                <div x-show="editMode && (formData.current_status === 'Draft' || formData.current_status === 'Approved')">
                    <label class="label">
                        <span class="label-text">Current Status</span>
                    </label>
                    <div class="flex items-center gap-2 p-3 bg-base-200 rounded-lg">
                        <span class="badge badge-lg" 
                              :class="{
                                  'badge-neutral': formData.current_status === 'Draft',
                                  'badge-success': formData.current_status === 'Approved'
                              }"
                              x-text="formData.current_status"></span>
                        <span class="text-sm text-base-content/70" x-text="formData.current_status === 'Draft' ? '(Cannot edit draft status)' : '(Cannot edit approved status)'"></span>
                    </div>
                </div>

                <!-- Reason -->
                <div class="col-span-1 md:col-span-2">
                    <label class="label">
                        <span class="label-text">Reason</span>
                    </label>
                    <textarea x-model="formData.reason" class="textarea textarea-bordered w-full"
                        rows="3"></textarea>
                    <p x-show="formErrors.reason" class="text-error text-sm mt-1" x-text="formErrors.reason"></p>
                </div>

                <!-- Attachment -->
                <div class="col-span-1 md:col-span-2">
                    <label class="label">
                        <span class="label-text">Supporting Document</span>
                    </label>
                     <input type="file" @change="handleFileSelect" id="supporting_document" class="file-input file-input-bordered w-full">
                     <p x-show="formErrors.supporting_document" class="text-error text-sm mt-1" x-text="formErrors.supporting_document"></p>
                     <div x-show="editMode && formData.supporting_document_path" class="text-sm mt-2">
                        Current file: <a :href="`/storage/${formData.supporting_document_path}`" target="_blank" class="link" x-text="formData.supporting_document_path.split('/').pop()"></a>
                     </div>
                </div>
            </div>

            <div class="modal-action">
                <button type="button" class="btn" onclick="hideModal('create_edit_modal')">Cancel</button>
                <button type="submit" class="btn btn-primary" x-text="editMode ? 'Update' : 'Create'"></button>
            </div>
        </form>
    </div>
</dialog>
