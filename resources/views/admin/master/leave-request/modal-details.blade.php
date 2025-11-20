<!-- Details Modal -->
<dialog id="details_modal" class="modal">
    <div class="modal-box w-11/12 max-w-3xl">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="font-bold text-lg">Leave Request Details</h3>

        <template x-if="selectedRequest">
            <div class="py-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="font-semibold">Employee</p>
                        <p x-text="selectedRequest.user?.name"></p>
                    </div>
                    <div>
                        <p class="font-semibold">Department</p>
                        <p x-text="selectedRequest.user?.department?.name || 'N/A'"></p>
                    </div>
                    <div>
                        <p class="font-semibold">Submitted On</p>
                        <p x-text="new Date(selectedRequest.created_at).toLocaleString()"></p>
                    </div>
                    <div>
                        <p class="font-semibold">Leave Type</p>
                        <p x-text="selectedRequest.leave_type?.name"></p>
                    </div>
                    <div>
                        <p class="font-semibold">Dates</p>
                        <p><span x-text="formatDate(selectedRequest.start_date)"></span> to <span
                                x-text="formatDate(selectedRequest.end_date)"></span></p>
                    </div>
                    <div>
                        <p class="font-semibold">Duration</p>
                        <p x-text="selectedRequest.duration_days + ' day(s)'"></p>
                    </div>
                    <div>
                        <p class="font-semibold">Period</p>
                        <p class="capitalize" x-text="selectedRequest.leave_period?.replace('_', ' ')"></p>
                    </div>
                    <div>
                        <p class="font-semibold">Status</p>
                        <p><span class="badge"
                                :class="{
                                'badge-warning': selectedRequest.current_status === 'Pending',
                                'badge-success': selectedRequest.current_status === 'Approved',
                                'badge-error': selectedRequest.current_status === 'Rejected' || selectedRequest.current_status === 'Canceled',
                                'badge-ghost': selectedRequest.current_status === 'Draft',
                            }"
                                x-text="selectedRequest.current_status"></span></p>
                    </div>
                    <div x-show="selectedRequest.supporting_attachment_path">
                        <p class="font-semibold">Attachment</p>
                        <a :href="`/storage/${selectedRequest.supporting_attachment_path}`" target="_blank"
                            class="link link-primary">View Document</a>
                    </div>
                    <div class="md:col-span-3">
                        <p class="font-semibold">Reason</p>
                        <p class="whitespace-pre-wrap bg-base-200 p-2 rounded-md" x-text="selectedRequest.reason"></p>
                    </div>
                </div>

                <!-- Approval History Timeline -->
                <div class="mt-6" x-show="selectedRequest.approvals && selectedRequest.approvals.length > 0">
                    <h4 class="font-bold mb-2">Approval History</h4>
                    <ul class="timeline timeline-snap-icon max-md:timeline-compact timeline-vertical">
                        <template x-for="(approval, index) in selectedRequest.approvals" :key="approval.id">
                            <li>
                                <div class="timeline-middle">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5"
                                    :class="{
                                        'text-success': approval.status === 'Approved',
                                        'text-error': approval.status === 'Rejected',
                                        'text-warning': approval.status === 'Pending'
                                    }"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                </div>
                                <div :class="index % 2 === 0 ? 'timeline-start md:text-end' : 'timeline-end'" class="mb-10">
                                    <time class="font-mono italic text-xs" x-text="new Date(approval.created_at).toLocaleString()"></time>
                                    <div class="text-lg font-black" x-text="approval.status"></div>
                                    <p class="text-sm">by <span x-text="approval.approver?.name"></span></p>
                                    <p x-show="approval.comments" class="mt-1 bg-base-200 p-2 rounded text-xs" x-text="approval.comments"></p>
                                </div>
                                <hr />
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </template>

        <div class="modal-action">
            <form method="dialog">
                <button class="btn">Close</button>
            </form>
        </div>
    </div>
</dialog>
