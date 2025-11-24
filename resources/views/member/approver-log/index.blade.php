@extends('template.member')

@section('title', 'Approver Log')

@section('content')
<div x-data="approverLog('{{ config('app.base_api') }}')" x-init="init()" class="space-y-6">
    
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Approver Log</h1>
            <p class="text-sm text-base-content/70">History of your approval actions</p>
        </div>
        <div class="badge badge-primary badge-lg" x-text="`Total: ${approvals.length}`"></div>
    </div>

    {{-- Filter Section --}}
    <div class="card bg-base-100 shadow">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Filter by Action</span>
                    </label>
                    <select x-model="filterAction" class="select select-bordered select-sm">
                        <option value="">All Actions</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Search Employee</span>
                    </label>
                    <input type="text" x-model="searchQuery" placeholder="Search by name or code..." class="input input-bordered input-sm">
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">&nbsp;</span>
                    </label>
                    <button @click="resetFilters" class="btn btn-sm btn-ghost">Reset Filters</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <template x-if="loading">
        <div class="flex justify-center py-12">
            <span class="loading loading-dots loading-lg text-primary"></span>
        </div>
    </template>

    {{-- Approvals Table --}}
    <template x-if="!loading">
        <div class="card bg-base-100 shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Duration</th>
                            <th>Action</th>
                            <th>Comments</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="approval in filteredApprovals" :key="approval.approval_id">
                            <tr>
                                <td>
                                    <div class="text-sm font-semibold" x-text="formatDate(approval.acted_at)"></div>
                                    <div class="text-xs opacity-50" x-text="formatTime(approval.acted_at)"></div>
                                </td>
                                <td>
                                    <div class="font-bold text-sm" x-text="approval.employee_name"></div>
                                    <div class="text-xs opacity-50" x-text="approval.employee_code"></div>
                                </td>
                                <td>
                                    <div class="text-sm" x-text="approval.leave_type_name"></div>
                                    <div class="text-xs opacity-50">
                                        <span x-text="formatDate(approval.start_date)"></span> - 
                                        <span x-text="formatDate(approval.end_date)"></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-ghost badge-sm" x-text="`${approval.duration_days} Days`"></span>
                                </td>
                                <td>
                                    <div class="badge badge-sm" :class="getActionColor(approval.action)" x-text="approval.action"></div>
                                </td>
                                <td>
                                    <div class="text-sm max-w-xs truncate" :title="approval.comments || '-'" x-text="approval.comments || '-'"></div>
                                </td>
                                <td>
                                    <div class="badge badge-sm" :class="getStatusColor(approval.current_status)" x-text="approval.current_status"></div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredApprovals.length === 0">
                            <tr>
                                <td colspan="7" class="text-center py-8 text-base-content/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    No approval history found
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </template>

</div>
@endsection

@push('scripts')
<script>
    function approverLog(baseApiUrl) {
        return {
            approvals: [],
            loading: true,
            filterAction: '',
            searchQuery: '',
            token: localStorage.getItem('authToken'),

            async init() {
                if (!this.token) {
                    window.location.href = '/login';
                    return;
                }
                await this.fetchApprovals();
            },

            async fetchApprovals() {
                try {
                    const response = await fetch(`${baseApiUrl}/approver-log`, {
                        headers: { 
                            'Authorization': `Bearer ${this.token}`, 
                            'Accept': 'application/json' 
                        }
                    });
                    const data = await response.json();
                    if (response.ok) {
                        this.approvals = data.data;
                    } else {
                        Swal.fire('Error', data.meta?.message || 'Failed to load approver log', 'error');
                    }
                } catch (e) {
                    console.error('Error fetching approver log:', e);
                    Swal.fire('Error', 'Network error', 'error');
                } finally {
                    this.loading = false;
                }
            },

            get filteredApprovals() {
                return this.approvals.filter(approval => {
                    const matchesAction = !this.filterAction || approval.action === this.filterAction;
                    const matchesSearch = !this.searchQuery || 
                        approval.employee_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        approval.employee_code.toLowerCase().includes(this.searchQuery.toLowerCase());
                    return matchesAction && matchesSearch;
                });
            },

            resetFilters() {
                this.filterAction = '';
                this.searchQuery = '';
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                return new Date(dateString).toLocaleDateString('en-GB', {
                    day: 'numeric', month: 'short', year: 'numeric'
                });
            },

            formatTime(dateString) {
                if (!dateString) return '-';
                return new Date(dateString).toLocaleTimeString('en-GB', {
                    hour: '2-digit', minute: '2-digit'
                });
            },

            getActionColor(action) {
                switch(action) {
                    case 'Approved': return 'badge-success text-white';
                    case 'Rejected': return 'badge-error text-white';
                    case 'Pending': return 'badge-warning';
                    default: return 'badge-ghost';
                }
            },

            getStatusColor(status) {
                switch(status) {
                    case 'Approved': return 'badge-success text-white';
                    case 'Rejected': return 'badge-error text-white';
                    case 'Pending': return 'badge-warning';
                    case 'In Progress': return 'badge-info';
                    case 'Draft': return 'badge-ghost';
                    default: return 'badge-ghost';
                }
            }
        }
    }
</script>
@endpush
