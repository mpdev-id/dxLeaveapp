<!-- Approval Modal -->
<dialog id="approval_modal" class="modal">
    <div class="modal-box">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                @click="approvalData.comments = ''" onclick="hideModal('approval_modal')">✕</button>
        <h3 class="font-bold text-lg">Process Leave Request</h3>
        <p class="py-2 text-sm">You are about to process the leave request for <strong
                x-text="selectedRequest?.user.name"></strong>.</p>

        <form @submit.prevent="handleApproval()">
            <div class="py-4 space-y-4">
                <div>
                    <label class="label">
                        <span class="label-text">Action</span>
                    </label>
                    <select x-model="approvalData.action" class="select select-bordered w-full">
                        <option value="Approved">Approve</option>
                        <option value="Rejected">Reject</option>
                    </select>
                </div>
                
                <!-- Admin Override: Select Approver -->
                <div x-show="isAdmin" class="form-control">
                    <label class="label">
                        <span class="label-text">Approve as (Admin Override)</span>
                        <span class="label-text-alt text-warning">Optional</span>
                    </label>
                    <select x-model="approvalData.approver_id" class="select select-bordered w-full">
                        <option value="">Myself ({{ optional(Auth::user())->name ?? 'Myself' }})</option>
                        <template x-for="option in suggestedApprovers" :key="option.id + '-' + option.step_number">
                            <option :value="option.id" x-text="`Step ${option.step_number}: ${option.name} (${option.step_role})`"></option>
                        </template>
                    </select>
                    <label class="label">
                        <span class="label-text-alt text-xs">Leave blank to approve as yourself. Select a user to approve on their behalf.</span>
                    </label>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text">Comments (Optional)</span>
                    </label>
                    <textarea x-model="approvalData.comments" class="textarea textarea-bordered w-full"
                        rows="3"></textarea>
                </div>
            </div>

            <div class="modal-action">
                <button type="button" class="btn" onclick="hideModal('approval_modal')">Cancel</button>
                <button type="submit" class="btn"
                    :class="{'btn-success': approvalData.action === 'Approved', 'btn-error': approvalData.action === 'Rejected'}"
                    x-text="approvalData.action"></button>
            </div>
        </form>
    </div>
</dialog>
