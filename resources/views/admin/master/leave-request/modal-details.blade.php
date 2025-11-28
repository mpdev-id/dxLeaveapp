<!-- Details Modal -->
<dialog id="details_modal" class="modal">
    <div class="modal-box w-11/12 max-w-3xl">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="hideModal('details_modal')">✕</button>
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
                    <div x-show="typeof selectedRequest.initial_balance !== 'undefined'">
                        <p class="font-semibold">Remaining Leave</p>
                        <p x-text="selectedRequest.initial_balance + ' day(s)'"></p>
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
                        <a :href="`/storage/${selectedRequest.supporting_attachment_path}`" class="link link-primary">View Document</a>
                    </div>
                    <div class="md:col-span-3">
                        <p class="font-semibold">Reason</p>
                        <p class="whitespace-pre-wrap bg-base-200 p-2 rounded-md" x-text="selectedRequest.reason"></p>
                    </div>
                </div>

                <!-- Approval Workflow -->
                <div class="mt-6 w-full overflow-x-auto">
                    <h4 class="font-bold mb-2">Approval Workflow</h4>
                    <ul class="timeline timeline-horizontal">
                        <template x-for="(step, index) in selectedRequest.workflow.steps" :key="step.id">
                            <li>
                                <hr x-show="index > 0" />
                                <div class="timeline-middle">
                                    <template x-if="step.status === 'Approved'">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-7 h-7 text-success">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                    <template x-if="step.status === 'Rejected'">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-7 h-7 text-error">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94l-1.72-1.72z" clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                    <template x-if="step.status === 'Pending'">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-warning">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </template>
                                </div>
                                <div class="timeline-end timeline-box min-w-[150px] p-3 rounded-box shadow-md bg-base-100 border"
                                    :class="{
                                        'border-success': step.status === 'Approved',
                                        'border-error': step.status === 'Rejected',
                                        'border-warning': step.status === 'Pending'
                                    }">
                                    <div class="font-bold text-sm" x-text="step.approver_role.name"></div>
                                    <div class="text-xs text-gray-700" x-text="step.approver_user ? step.approver_user.name : 'Not Assigned'"></div>
                                    <span class="badge badge-sm mt-1"
                                        :class="{
                                            'badge-success': step.status === 'Approved',
                                            'badge-error': step.status === 'Rejected',
                                            'badge-warning': step.status === 'Pending',
                                            'badge-ghost': !step.status
                                        }"
                                        x-text="step.status ? step.status.charAt(0).toUpperCase() + step.status.slice(1) : 'Pending'">
                                    </span>
                                </div>
                                <hr x-show="index < selectedRequest.workflow.steps.length - 1" />
                            </li>
                        </template>
                    </ul>
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
            <button class="btn" onclick="hideModal('details_modal')">Close</button>
        </div>
    </div>
</dialog>
