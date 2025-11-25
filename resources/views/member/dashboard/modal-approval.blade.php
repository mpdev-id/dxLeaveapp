<!-- Approval Modal -->
<dialog id="approval_modal" class="modal">
    <div class="modal-box">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                @click="approvalData.comments = ''; approvalData.signature = ''; clearSignature()" onclick="hideModal('approval_modal')">✕</button>
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
                    <div :class="{
                        'fixed inset-0 z-50 bg-white p-4 flex flex-col': isFullScreen && !isLandscape,
                        'fixed inset-0 z-50 bg-white p-4 flex flex-col origin-center': isFullScreen && isLandscape,
                        'rounded-lg p-2 bg-white': !isFullScreen
                    }"
                    :style="isFullScreen && isLandscape ? 'width: 100vh; height: 100vw; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(90deg);' : ''">
                        <div class="flex justify-between items-center mb-2" x-show="isFullScreen">
                             <h3 class="font-bold text-lg">Sign Here</h3>
                             <button type="button" class="btn btn-sm btn-circle btn-ghost" @click="toggleFullScreen()">✕</button>
                        </div>
                        
                        <div class="flex-grow relative w-full" :class="{'h-full': isFullScreen, 'h-40': !isFullScreen}">
                            <canvas id="signature-pad" class="w-full h-full touch-none block"></canvas>
                        </div>
                        
                        <div class="flex justify-between mt-2">
                             <button type="button" class="btn btn-xs btn-outline" @click="toggleFullScreen()">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                                <span x-text="isFullScreen ? 'Exit Full Screen' : 'Full Screen'"></span>
                             </button>
                             <button type="button" class="btn btn-xs btn-outline btn-error" @click="clearSignature()">Clear Signature</button>
                        </div>
                    </div>
                    <input type="hidden" x-model="approvalData.signature">
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
