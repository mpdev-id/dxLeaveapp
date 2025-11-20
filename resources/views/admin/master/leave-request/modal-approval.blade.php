<!-- Approval Modal -->
<dialog id="approval_modal" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                @click="approvalData.comments = ''">✕</button>
        </form>
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
                        <option>Approve</option>
                        <option>Reject</option>
                    </select>
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
                <button type="button" class="btn" @click="closeModal('approval_modal')">Cancel</button>
                <button type="submit" class="btn"
                    :class="{'btn-success': approvalData.action === 'Approve', 'btn-error': approvalData.action === 'Reject'}"
                    x-text="approvalData.action"></button>
            </div>
        </form>
    </div>
</dialog>
