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
        <template x-if="loading">
            <div class="flex justify-center py-8">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </template>

        <template x-if="!loading && filteredRequests.length === 0">
            <div class="text-center py-10 opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                <p>No leave requests found.</p>
            </div>
        </template>

        <template x-for="request in filteredRequests" :key="request.id">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-bold text-lg" x-text="request.leave_type.name"></h3>
                            <span class="text-xs text-base-content/60" x-text="`Applied on ${formatDate(request.created_at)}`"></span>
                        </div>
                        <div class="badge" :class="getStatusColor(request.current_status)" x-text="request.current_status"></div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-sm my-2">
                        <div>
                            <span class="block text-xs opacity-70">Start Date</span>
                            <span class="font-medium" x-text="formatDate(request.start_date)"></span>
                        </div>
                        <div>
                            <span class="block text-xs opacity-70">End Date</span>
                            <span class="font-medium" x-text="formatDate(request.end_date)"></span>
                        </div>
                        <div>
                            <span class="block text-xs opacity-70">Duration</span>
                            <span class="font-medium" x-text="`${request.duration_days} Days`"></span>
                        </div>
                        <div>
                            <span class="block text-xs opacity-70">Period</span>
                            <span class="font-medium capitalize" x-text="request.leave_period.replace(/_/g, ' ')"></span>
                        </div>
                    </div>

                    <div class="collapse collapse-arrow bg-base-200 mt-2 rounded-box">
                        <input type="checkbox" /> 
                        <div class="collapse-title text-sm font-medium min-h-0 py-2">
                            View Details & History
                        </div>
                        <div class="collapse-content text-sm"> 
                            <div class="flex justify-end mb-2 mt-2">
                                <a :href="`{{ url('/member/leaves') }}/${request.id}/print`" target="_blank" class="btn btn-xs btn-outline gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z" /></svg>
                                    Print Form
                                </a>
                            </div>
                            <p class="mb-2"><span class="font-semibold">Reason:</span> <span x-text="request.reason"></span></p>
                            
                            <div class="divider my-1">Approval History</div>
                            <ul class="steps steps-vertical w-full">
                                <template x-for="approval in request.approvals" :key="approval.id">
                                    <li class="step" :class="getStepClass(approval.status)">
                                        <div class="text-left w-full ml-2">
                                            <div class="font-bold text-xs" x-text="approval.approver_name || 'System'"></div>
                                            <div class="text-xs" x-text="approval.status"></div>
                                            <div x-show="approval.comments" class="text-xs italic opacity-70" x-text="`Comment: ${approval.comments}`"></div>
                                            <div class="text-[10px] opacity-50" x-text="formatDate(approval.created_at)"></div>
                                        </div>
                                    </li>
                                </template>
                                <template x-if="request.approvals.length === 0">
                                    <li class="step step-neutral">Pending Approval</li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
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
                        // Filter for current user only (API returns all for approvers, so we must filter)
                        if (this.user) {
                            this.requests = data.data.filter(r => r.user.id === this.user.id);
                        }
                    }
                } catch (e) { console.error('Error fetching requests:', e); }
                finally { this.loading = false; }
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
                switch(status) {
                    case 'approved': return 'step-success';
                    case 'rejected': return 'step-error';
                    default: return 'step-neutral';
                }
            }
        }
    }
</script>
@endpush
