@extends('template.member')

@section('title', 'My Leaves')

@section('content')
<div x-data="myLeaves('{{ config('app.base_api') }}')" x-init="init()" class="space-y-6 pb-20">
    
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">My Leaves</h1>
        <a href="{{ route('member.leaves.create') }}" class="btn btn-primary btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            New Request
        </a>
    </div>

    {{-- Filters & Search --}}
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="form-control">
                    <label class="label py-1">
                        <span class="label-text text-xs font-semibold">Search</span>
                    </label>
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        placeholder="Search by leave type or reason..." 
                        class="input input-bordered input-sm w-full"
                    >
                </div>
                <div class="form-control">
                    <label class="label py-1">
                        <span class="label-text text-xs font-semibold">Filter by Status</span>
                    </label>
                    <select x-model="filterStatus" class="select select-bordered select-sm w-full">
                        <option value="">All Status</option>
                        <option value="Draft">Draft</option>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label py-1">
                        <span class="label-text text-xs font-semibold">Sort by</span>
                    </label>
                    <select x-model="sortBy" class="select select-bordered select-sm w-full">
                        <option value="date_desc">Date (Newest First)</option>
                        <option value="date_asc">Date (Oldest First)</option>
                        <option value="start_date_desc">Start Date (Latest)</option>
                        <option value="start_date_asc">Start Date (Earliest)</option>
                        <option value="status">Status</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-between items-center mt-2">
                <span class="text-xs text-base-content/60" x-text="`Showing ${filteredRequests.length} of ${requests.length} requests`"></span>
                <button @click="resetFilters" class="btn btn-ghost btn-xs">Reset Filters</button>
            </div>
        </div>
    </div>
    
    {{-- Leave Balances --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        <template x-for="balance in balances" :key="balance.leave_type_id">
            <div class="stat bg-base-100 shadow-sm rounded-box p-3 border border-base-200">
                <div class="stat-title text-[10px] font-bold uppercase tracking-wider truncate" x-text="balance.leave_type_name"></div>
                <div class="stat-value text-primary text-xl" x-text="balance.remaining_days"></div>
                <div class="stat-desc text-[10px]">Days Remaining</div>
            </div>
        </template>
        <template x-if="loadingBalances">
            <div class="col-span-2 flex justify-center py-4">
                <span class="loading loading-dots loading-sm"></span>
            </div>
        </template>
    </div>

    {{-- Leave List --}}
    <div class="space-y-4">
        <template x-if="!loading && filteredRequests.length === 0">
            <div class="text-center py-10 opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                <p>No leave requests found.</p>
            </div>
        </template>

        <div class="overflow-x-auto bg-base-100 shadow-sm border border-base-200 rounded-box">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loading Skeleton --}}
                    <template x-if="loading">
                        <template x-for="i in 5" :key="i">
                            <tr>
                                <td><div class="skeleton h-4 w-32"></div><div class="skeleton h-3 w-20 mt-1"></div></td>
                                <td><div class="skeleton h-4 w-24"></div><div class="skeleton h-3 w-16 mt-1"></div></td>
                                <td><div class="skeleton h-4 w-24"></div></td>
                                <td><div class="skeleton h-6 w-20 rounded-full"></div></td>
                                <td><div class="skeleton h-8 w-8 rounded-md"></div></td>
                            </tr>
                        </template>
                    </template>

                    <template x-if="!loading && filteredRequests.length > 0">
                        <template x-for="request in filteredRequests" :key="request.id">
                            <tr>
                                <td>
                                    <div class="font-bold text-sm" x-text="request.leave_type.name"></div>
                                    <div class="text-xs opacity-50" x-text="`${request.duration_days} Days`"></div>
                                </td>
                                <td class="text-sm">
                                    <div x-text="formatDate(request.start_date)"></div>
                                    <div class="text-xs opacity-50">to</div>
                                    <div x-text="formatDate(request.end_date)"></div>
                                </td>
                                <td>
                                    <div class="text-sm" x-text="formatDate(request.created_at)"></div>
                                </td>
                                <td>
                                    <div class="badge badge-sm" :class="getStatusColor(request.current_status)" x-text="request.current_status"></div>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <button @click="viewDetails(request)" class="btn btn-xs btn-ghost btn-square" title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </button>
                                        <template x-if="request.current_status === 'Draft'">
                                            <a :href="`{{ url('/member/leaves') }}/${request.id}/edit`" class="btn btn-xs btn-ghost btn-square text-warning" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                        </template>
                                        <template x-if="request.current_status === 'Draft' || request.current_status === 'Pending'">
                                            <button @click="deleteRequest(request.id)" class="btn btn-xs btn-ghost btn-square text-error" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </template>
                                        <a :href="`{{ url('/member/leaves') }}/${request.id}/print`" class="btn btn-xs btn-ghost btn-square" title="Print Form">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>
                    <template x-if="filteredRequests.length === 0 && !loading">
                        <tr>
                            <td colspan="5" class="text-center py-8 text-base-content/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                No leave requests found
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- The original card-based display is removed as it's replaced by the table --}}
    </div>

    {{-- Details Modal --}}
    <dialog id="leave_details_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg" x-text="selectedRequest?.leave_type?.name"></h3>
            <div class="py-4 space-y-4">
                <div>
                    <span class="font-semibold block text-xs opacity-70">Reason</span>
                    <span x-text="selectedRequest?.reason || '-'"></span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="font-semibold block text-xs opacity-70">Start Date</span>
                        <span x-text="formatDate(selectedRequest?.start_date)"></span>
                    </div>
                    <div>
                        <span class="font-semibold block text-xs opacity-70">End Date</span>
                        <span x-text="formatDate(selectedRequest?.end_date)"></span>
                    </div>
                </div>
                <div>
                    <span class="font-semibold block text-xs opacity-70">Duration</span>
                    <span x-text="`${selectedRequest?.duration_days} Days`"></span>
                </div>
                
                <div class="divider">Approval History</div>
                <ul class="steps steps-vertical w-full overflow-x-hidden">
                    <template x-for="item in getTimeline(selectedRequest)" :key="item.step_name">
                        <li class="step" :class="getStepClass(item.status)">
                            <div class="text-left w-full ml-2">
                                <div class="font-bold text-xs" x-text="item.approver_name || item.step_name"></div>
                                <div class="text-[10px] uppercase font-semibold" :class="getStatusTextColor(item.status)" x-text="item.status"></div>
                                <div x-show="item.comments" class="text-xs italic opacity-70" x-text="`Comment: ${item.comments}`"></div>
                                <div class="text-[10px] opacity-50" x-show="item.date" x-text="formatDate(item.date)"></div>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Close</button>
                </form>
            </div>
        </div>
    </dialog>
</div>
@endsection

@push('scripts')
<script>
    function myLeaves(baseApiUrl) {
        return {
            requests: [],
            balances: [],
            loading: true,
            loadingBalances: true,
            token: localStorage.getItem('authToken'),
            user: null,
            searchQuery: '',
            filterStatus: '',
            sortBy: 'date_desc',
            selectedRequest: null,

            viewDetails(request) {
                this.selectedRequest = request;
                document.getElementById('leave_details_modal').showModal();
            },

            async init() {
                if (!this.token) return;
                await this.fetchUser();
                await Promise.all([
                    this.fetchRequests(),
                    this.fetchBalances()
                ]);
            },

            get filteredRequests() {
                let filtered = this.requests;

                // Apply search filter
                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    filtered = filtered.filter(r => 
                        r.leave_type.name.toLowerCase().includes(query) ||
                        r.reason.toLowerCase().includes(query)
                    );
                }

                // Apply status filter
                if (this.filterStatus) {
                    filtered = filtered.filter(r => r.current_status === this.filterStatus);
                }

                // Apply sorting
                filtered = [...filtered].sort((a, b) => {
                    switch(this.sortBy) {
                        case 'date_desc':
                            return new Date(b.created_at) - new Date(a.created_at);
                        case 'date_asc':
                            return new Date(a.created_at) - new Date(b.created_at);
                        case 'start_date_desc':
                            return new Date(b.start_date) - new Date(a.start_date);
                        case 'start_date_asc':
                            return new Date(a.start_date) - new Date(b.start_date);
                        case 'status':
                            return a.current_status.localeCompare(b.current_status);
                        default:
                            return 0;
                    }
                });

                return filtered;
            },

            resetFilters() {
                this.searchQuery = '';
                this.filterStatus = '';
                this.sortBy = 'date_desc';
            },

            async fetchBalances() {
                try {
                    const response = await fetch(`${baseApiUrl}/user/leave-balances`, {
                        headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (response.ok) {
                        this.balances = data.data;
                    }
                } catch (e) { console.error('Error fetching balances:', e); }
                finally { this.loadingBalances = false; }
            },

            async fetchUser() {
                try {
                    const response = await fetch(`${baseApiUrl}/user`, {
                        headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (response.ok) {
                        this.user = data.data;
                    }
                } catch (e) { console.error('Error fetching user:', e); }
            },

            async fetchRequests() {
                try {
                    const response = await fetch(`${baseApiUrl}/leave-requests`, {
                        headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (response.ok) {
                        // Store all data first
                        const allRequests = data.data || [];
                        console.log('API returned:', allRequests.length, 'requests');
                        
                        // Filter for current user only (API returns all for approvers, so we must filter)
                        if (this.user) {
                            this.requests = allRequests.filter(r => r.user && r.user.id === this.user.id);
                            console.log('Filtered to:', this.requests.length, 'requests for user', this.user.id);
                        } else {
                            console.warn('User not loaded yet, showing all requests');
                            this.requests = allRequests;
                        }
                    } else {
                        console.error('Failed to fetch requests:', data);
                    }
                } catch (e) { 
                    console.error('Error fetching requests:', e); 
                }
                finally { this.loading = false; }
            },

            async deleteRequest(id) {
                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                });

                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`${baseApiUrl}/leave-requests/${id}`, {
                            method: 'DELETE',
                            headers: { 
                                'Authorization': `Bearer ${this.token}`,
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            Swal.fire('Deleted!', 'Your file has been deleted.', 'success');
                            this.fetchRequests(); // Refresh list
                            this.fetchBalances(); // Refresh balances as deleting might restore balance
                        } else {
                            const data = await response.json();
                            Swal.fire('Error', data.meta?.message || 'Failed to delete request', 'error');
                        }
                    } catch (e) {
                        console.error('Error deleting request:', e);
                        Swal.fire('Error', 'An unexpected error occurred', 'error');
                    }
                }
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                return new Date(dateString).toLocaleDateString('en-GB', {
                    day: 'numeric', month: 'short', year: 'numeric'
                });
            },

            getStatusColor(status) {
                switch(status) {
                    case 'Approved': return 'badge-success text-white';
                    case 'Rejected': return 'badge-error text-white';
                    case 'Pending': return 'badge-warning';
                    case 'Draft': return 'badge-ghost';
                    default: return 'badge-info';
                }
            },

            getStepClass(status) {
                const s = (status || '').toLowerCase();
                if (s === 'Approved') return 'step-success';
                if (s === 'Rejected') return 'step-error';
                return ''; 
            },

            getStatusTextColor(status) {
                const s = (status || '').toLowerCase();
                if (s === 'Approved') return 'text-success';
                if (s === 'Rejected') return 'text-error';
                return 'text-base-content/50';
            },

            getTimeline(request) {
                if (!request.workflow) return [];
                
                const stepsData = request.workflow.steps || [];
                if (stepsData.length === 0) return [];
                
                // Sort steps by step_number
                const steps = [...stepsData].sort((a, b) => a.step_number - b.step_number);
                
                return steps.map(step => {
                    // Find approval for this step
                    const approval = request.approvals ? request.approvals.find(a => a.workflow_step_id === step.id) : null;
                    
                    let status = 'Pending';
                    let approverName = null;
                    let comments = null;
                    let date = null;

                    if (approval) {
                        status = approval.status; // 'Approved' or 'Rejected'
                        approverName = approval.approver ? approval.approver.name : 'Unknown';
                        comments = approval.comments;
                        date = approval.created_at;
                    } else {
                        // Pending step: Show Role only (approverName remains null)
                    }

                    return {
                        step_name: step.approver_role ? step.approver_role.name : 'Approver',
                        status: status,
                        approver_name: approverName,
                        comments: comments,
                        date: date
                    };
                });
            }
        }
    }
</script>
@endpush
