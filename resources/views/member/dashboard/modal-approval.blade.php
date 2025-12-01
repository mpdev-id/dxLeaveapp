<!-- Approval Modal -->
<dialog id="approval_modal" class="modal">
    <div class="modal-box">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                @click="approvalData.comments = ''; approvalData.signature = ''; clearSignature(); hideModal('approval_modal')">✕</button>
        <h3 class="font-bold text-lg">Process Leave Request</h3>
        <p class="py-2 text-sm">You are about to process the leave request for <strong
                x-text="selectedRequest?.user.name"></strong>.</p>

        <form @submit.prevent="submitApproval()">
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
                
                <div>
                    <label class="label">
                        <span class="label-text">Comments (Optional)</span>
                    </label>
                    <textarea x-model="approvalData.comments" class="textarea textarea-bordered w-full"
                        rows="3"></textarea>
                </div>

                <!-- Signature Pad -->
                <div x-show="approvalData.action === 'Approved'">
                    <label class="label">
                        <span class="label-text">Signature</span>
                    </label>
                    
                    <div class="flex flex-col gap-4">
                        <!-- Option to use saved signature -->
                        <div x-show="userSignature" class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" x-model="useSavedSignature" class="checkbox checkbox-primary" />
                                <span class="label-text">Use my saved signature</span>
                            </label>
                            <div x-show="useSavedSignature" class="mt-2 p-4 border rounded-lg bg-base-100 w-fit">
                                <img :src="userSignature" alt="Saved Signature" class="h-20 object-contain">
                            </div>
                        </div>

                        <!-- Signature Pad -->
                        <div x-show="!useSavedSignature" class="w-full flex flex-col items-center">
                            <div class="w-full max-w-sm aspect-square border-2 border-dashed border-gray-300 rounded-lg bg-white relative">
                                <canvas id="signature-pad" class="absolute inset-0 w-full h-full touch-none"></canvas>
                            </div>
                            <div class="w-full max-w-sm flex justify-between mt-2">
                                <span class="text-xs text-gray-500">Sign above</span>
                                <div class="flex gap-2">
                                    <button type="button" @click="undoSignature" class="btn btn-xs btn-ghost" :disabled="historyStep < 0" title="Undo">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                    </button>
                                    <button type="button" @click="redoSignature" class="btn btn-xs btn-ghost" :disabled="historyStep >= history.length - 1" title="Redo">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" /></svg>
                                    </button>
                                    <button type="button" @click="clearSignature" class="btn btn-xs btn-ghost text-error" title="Clear">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" x-model="approvalData.signature">
                </div>
            </div>

            <div class="modal-action">
                <button type="button" class="btn" @click="hideModal('approval_modal')">Cancel</button>
                <button type="submit" class="btn"
                    :class="{'btn-success': approvalData.action === 'Approved', 'btn-error': approvalData.action === 'Rejected'}"
                    x-text="approvalData.action"></button>
            </div>
        </form>
    </div>
</dialog>
