@extends('template.admin')

@section('title', 'Dashboard')

@section('content')
    {{-- Stats --}}
    <div x-data="dashboardData('{{ config('app.base_api') }}')" x-init="fetchData()" class="mb-6">
        <div class="stats shadow w-full">
            {{-- Stat Items --}}
            <div class="stat">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="stat-title">Total Members</div>
                <div class="stat-value text-primary" x-text="loadingStats ? '...' : stats.total_users">...</div>
                <div class="stat-desc">All registered users</div>
            </div>
            
            <div class="stat">
                <div class="stat-figure text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="stat-title">Approved This Month</div>
                <div class="stat-value text-secondary" x-text="loadingStats ? '...' : stats.approved_this_month">...</div>
                <div class="stat-desc">Leave requests in current month</div>
            </div>
            
            <div class="stat">
                <div class="stat-figure text-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="stat-title">Pending Requests</div>
                <div class="stat-value text-accent" x-text="loadingStats ? '...' : stats.pending_requests">...</div>
                <div class="stat-desc">Waiting for approval</div>
            </div>
        </div>

        {{-- User Leave Balances --}}
        <div class="card bg-base-100 shadow-xl mt-6">
            <div class="card-body">
                <h2 class="card-title">My Leave Balances (Current Year)</h2>
                <div x-show="loadingBalances" class="text-center">Loading leave balances...</div>
                <div x-show="!loadingBalances && leaveBalances.length === 0" class="text-center">No leave balances found.</div>
                <div x-show="!loadingBalances && leaveBalances.length > 0" class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th>Remaining Days</th>
                                <th>Days Taken</th>
                                <th>Total Entitlement</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="balance in leaveBalances" :key="balance.leave_type_id">
                                <tr>
                                    <td x-text="balance.leave_type_name"></td>
                                    <td x-text="balance.remaining_days"></td>
                                    <td x-text="balance.days_taken"></td>
                                    <td x-text="balance.total_entitlement"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Monthly Chart --}}
        <div class="card bg-base-100 shadow-xl mt-6">
            <div class="card-body">
                <div class="flex flex-wrap justify-between items-center mb-4">
                                    <h2 class="card-title">Monthly Leave Report</h2>
                                    <div class="flex items-center gap-4">
                                        <div class="btn-group">
                                            <button @click="chartType = 'bar'; renderChart()" :class="chartType === 'bar' ? 'btn-active' : ''" class="btn btn-sm">Bar</button>
                                            <button @click="chartType = 'line'; renderChart()" :class="chartType === 'line' ? 'btn-active' : ''" class="btn btn-sm">Line</button>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <select x-model="chartYear" @change="fetchChartData()" class="select select-bordered select-sm">
                                                <template x-for="year in chartYears" :key="year">
                                                    <option :value="year" x-text="year"></option>
                                                </template>
                                            </select>
                                            <select x-model="chartMonth" @change="fetchChartData()" class="select select-bordered select-sm">
                                                <template x-for="(month, index) in chartMonths" :key="index">
                                                                                <option :value="index + 1" x-text="month" x-bind:selected="chartMonth === index + 1"></option>
                                                                            </template>
                                                                        </select>                                        </div>
                                    </div>                </div>
                <div x-show="loadingChart" class="text-center p-8">
                    <span class="loading loading-lg loading-spinner text-primary"></span>
                </div>
                <div x-show="!loadingChart">
                    <canvas id="monthlyLeaveChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Calendar --}}
        <div class="card bg-base-100 shadow-xl mt-6">
            <div class="card-body">
                <h2 class="card-title">Leave & Holiday Calendar</h2>
                <div id='calendar' class="w-full h-[70vh]"></div>
            </div>
        </div>
    </div>

    {{-- Event Detail Modal --}}
    <dialog id="event_modal" class="modal">
        <div class="modal-box">
            <h3 id="modal_title" class="font-bold text-lg"></h3>
            <div id="modal_body" class="py-4">
                {{-- Content will be injected by JS --}}
            </div>
            <div class="modal-action">
                <button class="btn" onclick="hideModal('event_modal')">Close</button>
            </div>
        </div>
    </dialog>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorClass = (status) => {
                switch (status) {
                    case 'Pending': return '#FF5107'; // warna orange
                    case 'In Progress': return '#FF5107'; // warna yellow
                    case 'Approved': return '#34c759'; // warna green
                    case 'Rejected': return '#C40000'; // warna red
                    case 'Canceled': return '#C40000'; // warna pink
                    case 'Draft': return '#230202'; // warna grey
                    default: return '';
                }
            };

             // Helper function for status badge class
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
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('event_modal');
    const modalTitle = document.getElementById('modal_title');
    const modalBody = document.getElementById('modal_body');

    const baseApiUrl = '{{ config('app.base_api') }}';
    const token = localStorage.getItem('authToken');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        initialView: 'dayGridMonth',
        editable: false,
        selectable: true,
        dayMaxEvents: true, // allow "more" link when too many events
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch(`${baseApiUrl}/admin/dashboard/leave-calendar?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.redirected) {
                    // If redirected, it's likely an authentication issue
                    console.error('Authentication failed, redirected to login.');
                    Swal.fire({
                        icon: 'error',
                        title: 'Authentication Required',
                        text: 'Your session has expired or you are not authorized. Please log in again.',
                        didClose: () => {
                            window.location.href = '/login'; // Redirect to login
                        }
                    });
                    failureCallback({ message: 'Authentication failed' });
                    return;
                }
                if (!response.ok) {
                    // Attempt to parse JSON error, or fall back to generic message
                    return response.json().then(err => {
                        failureCallback({ message: err.message || 'Network response was not ok' });
                        throw new Error(err.message || 'Network response was not ok');
                    }).catch(() => {
                        // If it's not JSON, throw a generic network error
                        failureCallback({ message: 'Network response was not ok and not JSON' });
                        throw new Error('Network response was not ok and not JSON');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.data) { // Assuming ResponseFormatter::success returns data in a 'data' key
                    const events = data.data.map(event => {
                        if (event.extendedProps.type === 'leave') {
                             const details = event.extendedProps.details;
                             return {
                                id: event.id,
                                title: details.current_status + ' | ' +details.user.name ,
                                start: event.start, // Use top-level start
                                end: event.end,   // Use top-level end
                                allDay: true,
                                color: colorClass(details.current_status), 
                                extendedProps: {
                                    type: 'leave',
                                    details: details,
                                }
                            };
                        } else if (event.extendedProps.type === 'holiday') {
                            const details = event.extendedProps.details;
                            return {
                                id: event.id,
                                title: details.name,
                                start: event.start, // Use top-level start
                                allDay: true,
                                color: '#FF96EC', // Green color for public holidays
                                className: 'bg-success text-white',
                                extendedProps: {
                                    type: 'holiday',
                                    details: details,
                                }
                            };
                        }
                        return null; // Should not happen
                    }).filter(Boolean); // Filter out nulls
                    console.log('Events array sent to FullCalendar:', events); // Added for debugging
                    successCallback(events);
                } else {
                    console.log('No events data or unexpected format received.'); // Added for debugging
                    successCallback([]); // No data or unexpected format
                }
            })
            .catch(error => {
                console.error('Error fetching calendar events:', error);
                // Swal.fire is already handled in the redirected check or specific error parsing
                // failureCallback(error); // This is handled by the specific error parsing above
            });
        },
        eventDidMount: function(info) {
            // Log event details to console for debugging
            console.log('Event Mounted:', info.event);

            // Optional: Add a custom class based on event type if needed for further styling
            if (info.event.extendedProps.type === 'leave') {
                info.el.classList.add('fc-event-leave');
            } else if (info.event.extendedProps.type === 'holiday') {
                info.el.classList.add('fc-event-holiday');
            }
        },
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            
            // Helper function for date formatting
            const formatDate = (dateString) => {
                if (!dateString) return 'N/A';
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateString).toLocaleDateString(undefined, options);
            };

            // Helper function for period formatting
            const formatPeriod = (period) => {
                if (!period) return 'N/A';
                return period.replace(/_/g, ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            };

            if (props.type === 'leave') {
                const details = props.details;
                modalTitle.innerText = 'Leave Request Details';

                let attachmentHtml = '';
                if (details.supporting_attachment_path) {
                    attachmentHtml = `
                        <div>
                            <p class="font-semibold">Attachment</p>
                            <a href="/storage/${details.supporting_attachment_path}" target="_blank" class="link link-primary">View Document</a>
                        </div>
                    `;
                }

                let approverTimelineHtml = '';
                if (details.workflow && details.workflow.steps && details.workflow.steps.length > 0) {
                    approverTimelineHtml = `
                        <div class="mt-6">
                            <h4 class="font-bold mb-2">Approval Workflow</h4>
                            <ul class="timeline timeline-horizontal">
                    `;
                    details.workflow.steps.forEach((step, index) => {
                        approverTimelineHtml += `
                            <li>
                                ${index > 0 ? '<hr />' : ''}
                                <div class="timeline-start">${step.approver_role.name}</div>
                                <div class="timeline-middle">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-info"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94l-1.72-1.72z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="timeline-end timeline-box">
                                    ${step.approver_user ? step.approver_user.name : 'N/A'}
                                </div>
                                ${index < details.workflow.steps.length - 1 ? '<hr />' : ''}
                            </li>
                        `;
                    });
                    approverTimelineHtml += `</ul></div>`;
                }

                let approvalHistoryHtml = '';
                if (details.approvals && details.approvals.length > 0) {
                    approvalHistoryHtml = `
                        <div class="mt-6">
                            <h4 class="font-bold mb-2">Approval History</h4>
                            <ul class="timeline timeline-vertical">
                    `;
                    details.approvals.forEach((approval, index) => {
                        const approvalDate = new Date(approval.created_at).toLocaleString();
                        
                        let iconColorClass = '';
                        if (approval.action === 'Approved') {
                            iconColorClass = 'text-success';
                        } else if (approval.action === 'Rejected' || approval.action === 'Canceled') {
                            iconColorClass = 'text-error';
                        } else if (approval.action === 'Pending') {
                            iconColorClass = 'text-warning';
                        }

                        approvalHistoryHtml += `
                            <li>
                                ${index > 0 ? '<hr />' : ''}
                                <div class="timeline-start">
                                    <div class="text-lg font-black">${approval.action}</div>
                                    <time class="font-mono italic text-xs">${approvalDate}</time>
                                </div>
                                <div class="timeline-middle">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 ${iconColorClass}"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 101.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="timeline-end timeline-box">
                                    <p class="text-sm">by <span>${approval.approver?.name || ''}</span></p>
                                    ${approval.comments ? `<p class="mt-1 bg-base-200 p-2 rounded text-xs">${approval.comments}</p>` : ''}
                                </div>
                                ${index < details.approvals.length - 1 ? '<hr />' : ''}
                            </li>
                        `;
                    });
                    approvalHistoryHtml += `</ul></div>`;
                }

                modalBody.innerHTML = `
                    <div class="py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="font-semibold">Employee</p>
                                <p>${details.user?.name || 'N/A'}</p>
                            </div>
                           
                            <div>
                                <p class="font-semibold">Submitted On</p>
                                <p>${new Date(details.created_at).toLocaleString()}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Leave Type</p>
                                <p class="badge badge-neutral">${details.leave_type?.name || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Dates</p>
                                <p>${formatDate(details.start_date)} to ${formatDate(details.end_date)}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Duration</p>
                                <p>${details.duration_days} day(s)</p>
                            </div>
                            <div>
                                <p class="font-semibold">Remaining Leave</p>
                                <p>${details.remaining_leave_balance !== undefined ? details.remaining_leave_balance + ' day(s)' : 'N/A'}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Period</p>
                                <p class="capitalize">${formatPeriod(details.leave_period)}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Status</p>
                                <p><span class="animate-pulse duration-75 badge ${getBadgeClass(details.current_status)}">${details.current_status}</span></p>
                            </div>
                            ${attachmentHtml}
                            <div class="md:col-span-3">
                                <p class="font-semibold">Reason</p>
                                <p class="whitespace-pre-wrap bg-base-200 p-2 rounded-md">${details.reason || 'N/A'}</p>
                            </div>
                        </div>
                        ${approverTimelineHtml}
                        ${approvalHistoryHtml}
                    </div>
                `;
            } else if (props.type === 'holiday') {
                const details = props.details;
                modalTitle.innerText = 'Public Holiday Details';
                modalBody.innerHTML = `
                    <p><strong>Holiday:</strong> ${details.name}</p>
                    <p><strong>Date:</strong> ${formatDate(details.date)}</p>
                    <p class="badge badge-success text-white mt-2">Public Holiday</p>
                `;
            }
            
            showModal('event_modal');
        },
        dateClick: function(info) {
            Swal.fire({
                title: 'Loading Leave Requests...',
                text: 'Please wait while we fetch data for ' + info.dateStr,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`${baseApiUrl}/admin/dashboard/leave-requests-by-date?date=${info.dateStr}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'Network response was not ok'); });
                }
                return response.json();
            })
            .then(data => {
                Swal.close();
                if (data.data && data.data.length > 0) {
                    modalTitle.innerText = 'Leave Requests on ' + info.dateStr;
                    let bodyContent = `<ul class="list-disc list-inside">`;
                    data.data.forEach((request, index) => {
                        const startDate = new Date(request.start_date).toLocaleDateString();
                        const endDate = new Date(request.end_date).toLocaleDateString();
                        const separator = index < data.data.length - 1 ? `<hr class="my-2 w-3/5 mx-auto text-amber-400">` : '';
                        bodyContent += `
                            <li class="mb-2">
                                <strong>${request.user.name}</strong> (${request.user.department ? request.user.department.name : 'N/A'})<br>
                                ${request.leave_type.name}: ${startDate} to ${endDate}<br>
                                Status: <span class="badge ${getBadgeClass(request.current_status)}">${request.current_status}</span>
                            </li>
                            ${separator}
                        `;
                    });
                    bodyContent += `</ul>`;
                    modalBody.innerHTML = bodyContent;
                    showModal('event_modal');
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Leave Requests',
                        text: 'No approved leave requests found for ' + info.dateStr + '.',
                    });
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error fetching leave requests by date:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Error fetching leave requests for ' + info.dateStr + ': ' + error.message,
                });
            });
        }
    });

    calendar.render();
});

function dashboardData(baseApiUrl) {
    return {
        stats: {
            total_users: 0,
            approved_this_month: 0,
            pending_requests: 0,
        },
        leaveBalances: [],
        loadingStats: true,
        loadingBalances: true,

        // Chart properties
        chart: null,
        loadingChart: true,
        chartType: 'bar', // 'bar' or 'line'
        chartData: null, // To store the fetched data
        chartYear: new Date().getFullYear(),
        chartMonth: new Date().getMonth() + 1,
        chartYears: [],
        chartMonths: [
            'January', 'February', 'March', 'April', 'May', 'June', 
            'July', 'August', 'September', 'October', 'November', 'December'
        ],

        async fetchData() {
            const token = localStorage.getItem('authToken');
            if (!token) {
                console.error('No auth token found.');
                return;
            }
            this.initChart();
            await Promise.all([
                this.fetchStats(token),
                this.fetchLeaveBalances(token),
                this.fetchChartData(token)
            ]);
        },

        async fetchStats(token) {
            this.loadingStats = true;
            try {
                const response = await fetch(`${baseApiUrl}/admin/dashboard/stats`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Failed to fetch stats');
                const data = await response.json();
                if (data.data) this.stats = data.data;
            } catch (error) {
                console.error('Error fetching stats:', error);
            } finally {
                this.loadingStats = false;
            }
        },

        async fetchLeaveBalances(token) {
            this.loadingBalances = true;
            try {
                const response = await fetch(`${baseApiUrl}/user/leave-balances`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Failed to fetch leave balances');
                const data = await response.json();
                if (data.data) this.leaveBalances = data.data;
            } catch (error) {
                console.error('Error fetching leave balances:', error);
            } finally {
                this.loadingBalances = false;
            }
        },

        // --- Chart Methods ---
        initChart() {
            const currentYear = new Date().getFullYear();
            this.chartYears = Array.from({length: 5}, (v, i) => currentYear - i);
        },

        async fetchChartData() {
            this.loadingChart = true;
            const token = localStorage.getItem('authToken');
            try {
                const params = new URLSearchParams({ year: this.chartYear, month: this.chartMonth });
                const response = await fetch(`${baseApiUrl}/admin/dashboard/leave-chart-data?${params}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Failed to fetch chart data');
                const result = await response.json();
                if (result.data) {
                    this.chartData = result.data; // Cache the data
                    this.renderChart();
                }
            } catch (error) {
                console.error('Error fetching chart data:', error);
                Swal.fire({ icon: 'error', title: 'Chart Error', text: 'Could not load chart data.' });
            } finally {
                this.loadingChart = false;
            }
        },

        renderChart() {
            if (!this.chartData) return;

            const { labels, data } = this.chartData;
            
            const datasets = [
                {
                    label: 'Approved',
                    data: data.approved,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: this.chartType === 'line' ? false : undefined,
                    tension: 0.1
                },
                {
                    label: 'In Progress',
                    data: data.inProgress,
                    backgroundColor: 'rgba(255, 159, 64, 0.5)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                    fill: this.chartType === 'line' ? false : undefined,
                    tension: 0.1
                },
                {
                    label: 'Rejected',
                    data: data.rejected,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    fill: this.chartType === 'line' ? false : undefined,
                    tension: 0.1
                }
            ];

            const ctx = document.getElementById('monthlyLeaveChart').getContext('2d');
            if (this.chart) {
                this.chart.destroy();
            }

            this.chart = new Chart(ctx, {
                type: this.chartType,
                data: {
                    labels: labels.map(day => `Day ${day}`),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            stacked: this.chartType === 'bar'
                        },
                        y: {
                            stacked: this.chartType === 'bar',
                            beginAtZero: true,
                             ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    }
}
</script>
@endpush