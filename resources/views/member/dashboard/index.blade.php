@extends('template.member')

@section('title', 'Dashboard')

@section('content')
<div x-data="memberDashboard('{{ config('app.base_api') }}')" x-init="init()" class="space-y-6">
    
    {{-- Welcome Section --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Hello, <span x-text="user.name || 'User'"></span>!</h1>
            <p class="text-sm text-base-content/70" x-text="user.employee_code"></p>
        </div>
        <div class="badge badge-primary badge-lg" x-text="user.department?.name || 'No Dept'"></div>
    </div>

    {{-- Leave Balances --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <template x-for="balance in balances" :key="balance.leave_type_id">
            <div class="stats shadow bg-base-100 border border-base-200">
                <div class="stat">
                    <div class="stat-title font-bold text-base-content" x-text="balance.leave_type_name"></div>
                    <div class="stat-value text-primary text-3xl" x-text="balance.remaining_days"></div>
                    <div class="stat-desc text-xs mt-1">Days Remaining</div>
                    
                    <div class="divider my-1"></div>
                    
                    <div class="flex justify-between text-xs mt-2">
                        <div class="flex flex-col items-center">
                            <span class="font-semibold text-warning">Pending</span>
                            <span x-text="getPendingCount(balance.leave_type_id)"></span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="font-semibold text-error">Rejected</span>
                            <span x-text="getRejectedCount(balance.leave_type_id)"></span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="font-semibold text-success">Approved</span>
                            <span x-text="getApprovedCount(balance.leave_type_id)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        {{-- Loading State for Balances --}}
        <template x-if="loadingBalances">
            <div class="col-span-full flex justify-center py-8">
                <span class="loading loading-dots loading-lg text-primary"></span>
            </div>
        </template>
    </div>

    {{-- Approvals Section (For Approvers) --}}
    <div x-show="approvals.length > 0" class="space-y-4">
        <h2 class="text-lg font-bold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            Waiting for Your Approval
        </h2>
        <div class="grid gap-4 md:grid-cols-2">
            <template x-for="request in approvals" :key="request.id">
                <div class="card bg-base-100 shadow-md border-l-4 border-warning">
                    <div class="card-body p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold" x-text="request.user.name"></h3>
                                <p class="text-xs text-base-content/70" x-text="request.leave_type.name"></p>
                            </div>
                            <div class="badge badge-ghost text-xs" x-text="formatDate(request.created_at)"></div>
                        </div>
                        <div class="mt-2 text-sm">
                            <div class="flex gap-2">
                                <span class="font-semibold">Date:</span>
                                <span x-text="`${formatDate(request.start_date)} - ${formatDate(request.end_date)}`"></span>
                            </div>
                            <div class="flex gap-2">
                                <span class="font-semibold">Duration:</span>
                                <span x-text="`${request.duration_days} Days`"></span>
                            </div>
                            <div class="mt-1 text-base-content/80 italic" x-text="`Reason: ${request.reason}`"></div>
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <button @click="handleApproval(request.id, 'Rejected')" class="btn btn-sm btn-error btn-outline">Reject</button>
                            <button @click="handleApproval(request.id, 'Approved')" class="btn btn-sm btn-success text-white">Approve</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- My Recent Requests --}}
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-lg font-bold">My Recent Requests</h2>
            
            {{-- Filters --}}
            <div class="flex flex-wrap gap-2 w-full md:w-auto">
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    placeholder="Search..." 
                    class="input input-bordered input-sm w-full md:w-48"
                >
                <select x-model="filterStatus" class="select select-bordered select-sm w-full md:w-32">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Draft">Draft</option>
                </select>
                <select x-model="sortBy" class="select select-bordered select-sm w-full md:w-40">
                    <option value="date_desc">Newest First</option>
                    <option value="date_asc">Oldest First</option>
                    <option value="start_date_desc">Start Date (Latest)</option>
                    <option value="start_date_asc">Start Date (Earliest)</option>
                </select>
                <button @click="resetFilters" class="btn btn-ghost btn-sm btn-square" title="Reset Filters">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto bg-base-100 rounded-box shadow">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
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
                                <div class="badge badge-sm" :class="getStatusColor(request.current_status)" x-text="request.current_status"></div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredRequests.length === 0 && !loadingRequests">
                        <tr>
                            <td colspan="3" class="text-center py-4 text-base-content/50">No leave requests found.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="text-xs text-right text-base-content/60" x-show="myRequests.length > 0">
            Showing <span x-text="filteredRequests.length"></span> of <span x-text="myRequests.length"></span> requests
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function memberDashboard(baseApiUrl) {
        return {
            user: {},
            balances: [],
            approvals: [],
            myRequests: [],
            loadingBalances: true,
            loadingRequests: true,
            token: localStorage.getItem('authToken'),
            
            // Filter states
            searchQuery: '',
            filterStatus: '',
            sortBy: 'date_desc',

            async init() {
                if (!this.token) return; 
                
                await this.fetchUser();
                // We can run these in parallel after user is fetched
                await Promise.all([
                    this.fetchBalances(),
                    this.fetchRequests()
                ]);
            },

            get filteredRequests() {
                let filtered = this.myRequests;

                // Apply search filter
                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    filtered = filtered.filter(r => 
                        r.leave_type.name.toLowerCase().includes(query) ||
                        (r.reason && r.reason.toLowerCase().includes(query))
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

            async fetchUser() {
                try {
                    const response = await fetch(`${baseApiUrl}/user`, {
                        headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (response.ok) {
                        this.user = data.data;
                        const avatarEl = document.getElementById('user-avatar-nav');
                        if (avatarEl) avatarEl.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(this.user.name)}&background=random`;
                    }
                } catch (e) { console.error('Error fetching user:', e); }
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

            async fetchRequests() {
                try {
                    const response = await fetch(`${baseApiUrl}/leave-requests`, {
                        headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (response.ok) {
                        this.processRequests(data.data);
                    }
                } catch (e) { console.error('Error fetching requests:', e); }
                finally { this.loadingRequests = false; }
            },
            
            processRequests(allRequests) {
                if (!this.user || !this.user.id) return;
                this.myRequests = allRequests.filter(r => r.user.id === this.user.id);
                this.approvals = allRequests.filter(r => r.user.id !== this.user.id);
            },

            async handleApproval(id, action) {
                // Ensure action matches the ENUM values expected by the backend (Approved/Rejected)
                // The buttons pass 'Approved' or 'Rejected', so we should check against those.
                const isApproved = action === 'Approved';
                
                const { value: text } = await Swal.fire({
                    input: 'textarea',
                    inputLabel: isApproved ? 'Approval Comment (Optional)' : 'Rejection Reason (Required)',
                    inputPlaceholder: 'Type your message here...',
                    inputAttributes: {
                        'aria-label': 'Type your message here'
                    },
                    showCancelButton: true,
                    confirmButtonText: isApproved ? 'Approve' : 'Reject',
                    confirmButtonColor: isApproved ? '#36D399' : '#F87272',
                    inputValidator: (value) => {
                        if (!isApproved && !value) {
                            return 'You need to write a reason for rejection!'
                        }
                    }
                });

                if (text !== undefined) { 
                    try {
                        const response = await fetch(`${baseApiUrl}/leave-requests/${id}/approve`, {
                            method: 'PATCH',
                            headers: {
                                'Authorization': `Bearer ${this.token}`,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            // Force action to be 'Approved' or 'Rejected' based on the boolean check
                            body: JSON.stringify({ action: isApproved ? 'Approved' : 'Rejected', comments: text })
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok) {
                            Swal.fire('Success', result.meta.message, 'success');
                            this.fetchRequests(); 
                        } else {
                            Swal.fire('Error', result.meta.message || 'Action failed', 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Network error', 'error');
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

            getPendingCount(leaveTypeId) {
                return this.myRequests.filter(r => r.leave_type.id === leaveTypeId && r.current_status === 'Pending').length;
            },

            getRejectedCount(leaveTypeId) {
                return this.myRequests.filter(r => r.leave_type.id === leaveTypeId && r.current_status === 'Rejected').length;
            },

            getApprovedCount(leaveTypeId) {
                return this.myRequests.filter(r => r.leave_type.id === leaveTypeId && r.current_status === 'Approved').length;
            }
        }
    }

</script>
@endpush
