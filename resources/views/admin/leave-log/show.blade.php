@extends('template.admin')

@section('title')
    Leave Log for {{ $user->name }}
@endsection

@section('content')
    <div class="card bg-base-100 shadow-xl" x-data="employeeLeaveLogDetail('{{ config('app.base_api') }}', {{ $user->id }})">
        <div class="card-body">
            <h2 class="card-title">Leave History for {{ $user->name }} ({{ $user->employee_code }})</h2>
            <div x-show="loading" class="text-center">Loading leave requests...</div>
            <div x-show="!loading && leaveRequests.length === 0" class="text-center">No leave requests found for this employee.</div>
            <div x-show="!loading && leaveRequests.length > 0" class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="leaveRequest in leaveRequests" :key="leaveRequest.id">
                            <tr>
                                <td x-text="leaveRequest.leave_type.name"></td>
                                <td x-text="formatDate(leaveRequest.start_date)"></td>
                                <td x-text="formatDate(leaveRequest.end_date)"></td>
                                <td x-text="leaveRequest.duration_days + ' day(s)'"></td>
                                <td>
                                    <span class="badge" :class="getBadgeClass(leaveRequest.current_status)" x-text="leaveRequest.current_status"></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info view-details-btn" @click="showDetails(leaveRequest)">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Leave Request Detail Modal (Alpine.js native) --}}
        <dialog id="leave_request_details_modal" class="modal">
            <div class="modal-box w-11/12 max-w-3xl">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="hideModal('leave_request_details_modal')">✕</button>
                <h3 class="font-bold text-lg">Leave Request Details</h3>
                
                <template x-if="selectedLeaveRequest">
                    <div class="py-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="font-semibold">Employee</p>
                                <p x-text="selectedLeaveRequest.user?.name || 'N/A'"></p>
                            </div>
                            <div>
                                <p class="font-semibold">Department</p>
                                <p x-text="selectedLeaveRequest.user?.department?.name || 'N/A'"></p>
                            </div>
                            <div>
                                <p class="font-semibold">Submitted On</p>
                                <p x-text="new Date(selectedLeaveRequest.created_at).toLocaleString()"></p>
                            </div>
                            <div>
                                <p class="font-semibold">Leave Type</p>
                                <p x-text="selectedLeaveRequest.leave_type?.name || 'N/A'"></p>
                            </div>
                            <div>
                                <p class="font-semibold">Dates</p>
                                <p><span x-text="formatDate(selectedLeaveRequest.start_date)"></span> to <span x-text="formatDate(selectedLeaveRequest.end_date)"></span></p>
                            </div>
                            <div>
                                <p class="font-semibold">Duration</p>
                                <p x-text="selectedLeaveRequest.duration_days + ' day(s)'"></p>
                            </div>
                            <template x-if="typeof selectedLeaveRequest.remaining_leave_balance !== 'undefined'">
                                <div>
                                    <p class="font-semibold">Remaining Leave</p>
                                    <p x-text="selectedLeaveRequest.remaining_leave_balance + ' day(s)'"></p>
                                </div>
                            </template>
                            <div>
                                <p class="font-semibold">Period</p>
                                <p class="capitalize" x-text="formatPeriod(selectedLeaveRequest.leave_period)"></p>
                            </div>
                            <div>
                                <p class="font-semibold">Status</p>
                                <p><span class="badge" :class="getBadgeClass(selectedLeaveRequest.current_status)" x-text="selectedLeaveRequest.current_status"></span></p>
                            </div>
                            <template x-if="selectedLeaveRequest.supporting_attachment_path">
                                <div>
                                    <p class="font-semibold">Attachment</p>
                                    <a :href="`/storage/${selectedLeaveRequest.supporting_attachment_path}`"  class="link link-primary">View Document</a>
                                </div>
                            </template>
                            <div class="md:col-span-3">
                                <p class="font-semibold">Reason</p>
                                <p class="whitespace-pre-wrap bg-base-200 p-2 rounded-md" x-text="selectedLeaveRequest.reason || 'N/A'"></p>
                            </div>
                        </div>

                        {{-- Approval History Timeline --}}
                        <div class="mt-6" x-show="selectedLeaveRequest.approvals && selectedLeaveRequest.approvals.length > 0">
                            <h4 class="font-bold mb-2">Approval History</h4>
                            <ul class="timeline timeline-snap-icon max-md:timeline-compact timeline-vertical">
                                <template x-for="(approval, index) in selectedLeaveRequest.approvals" :key="approval.id">
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
                                            <p class="text-sm">by <span x-text="approval.approver?.name || 'N/A'"></span></p>
                                            <template x-if="approval.comments">
                                                <p class="mt-1 bg-base-200 p-2 rounded text-xs" x-text="approval.comments"></p>
                                            </template>
                                        </div>
                                        <hr x-show="index < selectedLeaveRequest.approvals.length - 1" />
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </template>
                <div class="modal-action">
                    <button class="btn" onclick="hideModal('leave_request_details_modal')">Close</button>
                </div>
            </div>
        </dialog>
    </div>
@endsection

@push('scripts')
    <script>
        // Helper function for date formatting (reused from dashboard)
        const formatDate = (dateString) => {
            if (!dateString) return 'N/A';
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        };

        // Helper function for status badge class (reused from dashboard)
        const getBadgeClass = (status) => {
            switch (status) {
                case 'Pending': return 'badge-warning';
                case 'Approved': return 'badge-success';
                case 'Rejected':
                case 'Canceled': return 'badge-error';
                case 'Draft': return 'badge-ghost';
                default: return '';
            }
        };

        // Helper function for period formatting (reused from dashboard)
        const formatPeriod = (period) => {
            if (!period) return 'N/A';
            return period.replace(/_/g, ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        };

        function employeeLeaveLogDetail(baseApiUrl, userId) {
            return {
                leaveRequests: [],
                loading: true,
                selectedLeaveRequest: null, // New Alpine state for the modal data
                
                init() {
                    this.fetchLeaveRequests();
                },

                async fetchLeaveRequests() {
                    this.loading = true;
                    const token = localStorage.getItem('authToken');
                    if (!token) {
                        window.location.href = '/login';
                        return;
                    }

                    try {
                        const response = await fetch(`${baseApiUrl}/admin/master/users/${userId}/leave-requests`, {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            if (response.redirected) {
                                 window.location.href = '/login';
                                 return;
                            }
                            throw new Error('Network response was not ok');
                        }

                        const data = await response.json();
                        if (data.data) {
                            this.leaveRequests = data.data;
                        }
                    } catch (error) {
                        console.error('Error fetching employee leave requests:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Could not fetch leave requests for this employee.',
                        });
                    } finally {
                        this.loading = false;
                    }
                },

                showDetails(leaveRequest) {
                    this.selectedLeaveRequest = leaveRequest; // Set the Alpine state
                    // console.log('Leave Request passed to modal:', leaveRequest); // Debugging
                    // console.log('selectedLeaveRequest state:', this.selectedLeaveRequest); // Debugging
                    showModal('leave_request_details_modal'); // Show the native dialog
                },
            }
        }
    </script>
@endpush
