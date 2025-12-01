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

    {{-- Chart and Rank Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Chart Section (80%) --}}
        <div class="lg:col-span-4 card bg-base-100 shadow">
            <div class="card-body p-4">
                {{-- Loading Skeleton --}}
                <template x-if="loading">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <div class="skeleton h-8 w-48"></div>
                            <div class="skeleton h-8 w-24"></div>
                        </div>
                        <div class="skeleton h-64 w-full rounded-box"></div>
                    </div>
                </template>

                {{-- Actual Content --}}
                <template x-if="!loading">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg">Approval Statistics</h3>
                            <select x-model="chartYear" @change="updateChart" class="select select-bordered select-sm">
                                <template x-for="year in availableYears" :key="year">
                                    <option :value="year" x-text="year"></option>
                                </template>
                            </select>
                        </div>
                        <div class="h-64 w-full">
                            <canvas id="approvalChart"></canvas>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Rank Table Section (20%) --}}
        <div class="lg:col-span-1 card bg-base-100 shadow">
            <div class="card-body p-4">
                {{-- Loading Skeleton --}}
                <template x-if="loading">
                    <div class="space-y-4">
                        <div class="skeleton h-8 w-32 mb-4"></div>
                        <div class="space-y-2">
                            <div class="skeleton h-6 w-full"></div>
                            <div class="skeleton h-6 w-full"></div>
                            <div class="skeleton h-6 w-full"></div>
                            <div class="skeleton h-6 w-full"></div>
                            <div class="skeleton h-6 w-full"></div>
                        </div>
                    </div>
                </template>

                {{-- Actual Content --}}
                <template x-if="!loading">
                    <div>
                        <h3 class="font-bold text-lg mb-4">Top Approved</h3>
                        <div class="overflow-x-auto">
                            <table class="table table-xs">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(user, index) in rankData" :key="user.name">
                                        <tr>
                                            <th x-text="index + 1"></th>
                                            <td x-text="user.name" class="truncate max-w-[100px]" :title="user.name"></td>
                                            <td x-text="user.count" class="font-bold text-success"></td>
                                        </tr>
                                    </template>
                                    <template x-if="rankData.length === 0">
                                        <tr><td colspan="3" class="text-center opacity-50">No data</td></tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>
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
                    <button @click="resetFilters" class="btn btn-sm btn-ghost">Reset Filtere</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <template x-if="loading">
        <div class="card bg-base-100 shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th><div class="skeleton h-4 w-16"></div></th>
                            <th><div class="skeleton h-4 w-24"></div></th>
                            <th><div class="skeleton h-4 w-20"></div></th>
                            <th><div class="skeleton h-4 w-16"></div></th>
                            <th><div class="skeleton h-4 w-16"></div></th>
                            <th><div class="skeleton h-4 w-32"></div></th>
                            <th><div class="skeleton h-4 w-16"></div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="i in 5">
                            <tr>
                                <td><div class="skeleton h-4 w-24"></div><div class="skeleton h-3 w-16 mt-1"></div></td>
                                <td><div class="skeleton h-4 w-32"></div><div class="skeleton h-3 w-20 mt-1"></div></td>
                                <td><div class="skeleton h-4 w-24"></div><div class="skeleton h-3 w-32 mt-1"></div></td>
                                <td><div class="skeleton h-6 w-16 rounded-full"></div></td>
                                <td><div class="skeleton h-6 w-20 rounded-full"></div></td>
                                <td><div class="skeleton h-4 w-40"></div></td>
                                <td><div class="skeleton h-6 w-20 rounded-full"></div></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
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
                        {{-- Loading Skeleton --}}
                        <template x-if="loading">
                            <template x-for="i in 5">
                                <tr>
                                    <td><div class="skeleton h-4 w-24"></div><div class="skeleton h-3 w-16 mt-1"></div></td>
                                    <td><div class="skeleton h-4 w-32"></div><div class="skeleton h-3 w-20 mt-1"></div></td>
                                    <td><div class="skeleton h-4 w-24"></div><div class="skeleton h-3 w-32 mt-1"></div></td>
                                    <td><div class="skeleton h-6 w-16 rounded-full"></div></td>
                                    <td><div class="skeleton h-6 w-20 rounded-full"></div></td>
                                    <td><div class="skeleton h-4 w-40"></div></td>
                                    <td><div class="skeleton h-6 w-20 rounded-full"></div></td>
                                </tr>
                            </template>
                        </template>

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
                        <template x-if="filteredApprovals.length === 0 && !loading">
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
        // Store chart instance outside Alpine's reactive scope to prevent "Maximum call stack size exceeded"
        let chartInstance = null;

        return {
            approvals: [],
            loading: true,
            filterAction: '',
            searchQuery: '',
            token: localStorage.getItem('authToken'),
            
            // Chart & Rank State
            chartYear: new Date().getFullYear(),

            async init() {
                if (!this.token) {
                    window.location.href = '/login';
                    return;
                }
                await this.fetchApprovals();
                // Initialize chart after data is fetched and DOM is ready
                this.$nextTick(() => {
                    this.initChart();
                });
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
                        this.$nextTick(() => {
                            this.updateChart();
                        });
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

            // --- Chart & Rank Logic ---

            get availableYears() {
                const currentYear = new Date().getFullYear();
                const years = new Set([currentYear]);
                this.approvals.forEach(a => {
                    if (a.acted_at) {
                        years.add(new Date(a.acted_at).getFullYear());
                    }
                });
                return Array.from(years).sort((a, b) => b - a);
            },

            get rankData() {
                const counts = {};
                this.approvals.forEach(a => {
                    if (!a.acted_at) return;
                    const year = new Date(a.acted_at).getFullYear();
                    // Rank based on Approved requests only, filtered by selected year
                    if (year == this.chartYear && a.action === 'Approved') {
                        counts[a.employee_name] = (counts[a.employee_name] || 0) + 1;
                    }
                });
                return Object.entries(counts)
                    .map(([name, count]) => ({ name, count }))
                    .sort((a, b) => b.count - a.count);
            },

            initChart() {
                const ctx = document.getElementById('approvalChart');
                if (!ctx) return;

                if (chartInstance) {
                    chartInstance.destroy();
                }

                // Ensure we have a valid context before creating
                chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: this.getChartData(),
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false, // Disable animation to prevent potential conflicts during updates
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: false,
                                text: 'Monthly Approvals'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            },

            updateChart() {
                if (chartInstance) {
                    chartInstance.data = this.getChartData();
                    chartInstance.update();
                } else {
                    this.initChart();
                }
            },

            getChartData() {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const approved = new Array(12).fill(0);
                const rejected = new Array(12).fill(0);

                // Use a plain array copy to avoid reactivity issues during iteration if necessary, 
                // though forEach on proxy is usually fine.
                const approvals = this.approvals; 

                approvals.forEach(a => {
                    if (!a.acted_at) return;
                    const date = new Date(a.acted_at);
                    if (date.getFullYear() == this.chartYear) {
                        const month = date.getMonth();
                        if (a.action === 'Approved') approved[month]++;
                        if (a.action === 'Rejected') rejected[month]++;
                    }
                });

                return {
                    labels: months,
                    datasets: [
                        {
                            label: 'Approved',
                            data: approved,
                            backgroundColor: '#36D399', // DaisyUI Success
                            borderRadius: 4
                        },
                        {
                            label: 'Rejected',
                            data: rejected,
                            backgroundColor: '#F87272', // DaisyUI Error
                            borderRadius: 4
                        }
                    ]
                };
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
