@extends('template.admin')

@section('title', 'Dashboard')

@section('content')
    {{-- Stats --}}
    <div x-data="dashboardStats('{{ config('app.base_api') }}')" x-init="fetchStats()" class="mb-6">
        <div class="stats shadow w-full">
            {{-- Stat Items --}}
            <div class="stat">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="stat-title">Total Members</div>
                <div class="stat-value text-primary" x-text="loading ? '...' : stats.total_users">...</div>
                <div class="stat-desc">All registered users</div>
            </div>
            
            <div class="stat">
                <div class="stat-figure text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="stat-title">Approved This Month</div>
                <div class="stat-value text-secondary" x-text="loading ? '...' : stats.approved_this_month">...</div>
                <div class="stat-desc">Leave requests in current month</div>
            </div>
            
            <div class="stat">
                <div class="stat-figure text-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="stat-title">Pending Requests</div>
                <div class="stat-value text-accent" x-text="loading ? '...' : stats.pending_requests">...</div>
                <div class="stat-desc">Waiting for approval</div>
            </div>
        </div>
    </div>

    {{-- Calendar --}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">Leave & Holiday Calendar</h2>
            <div id='calendar' class="w-full h-[70vh]"></div>
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
                <form method="dialog">
                    <button class="btn">Close</button>
                </form>
            </div>
        </div>
    </dialog>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        initialView: 'dayGridMonth',
        editable: false,
        selectable: true,
        dayMaxEvents: true, // allow "more" link when too many events
        events: {
            url: `${baseApiUrl}/admin/dashboard/leave-calendar`,
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            failure: function(error) {
                console.error('Error fetching calendar events:', error.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Could not fetch calendar data. Please try again.',
                });
            }
        },
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;

            modalTitle.innerText = event.title;
            let bodyContent = '';

            if (props.type === 'leave') {
                const details = props.details;
                const startDate = new Date(details.start_date).toLocaleDateString();
                const endDate = new Date(details.end_date).toLocaleDateString();

                bodyContent = `
                    <p><strong>Employee:</strong> ${details.user.name}</p>
                    <p><strong>Department:</strong> ${details.user.department ? details.user.department.name : 'N/A'}</p>
                    <p><strong>Leave Type:</strong> ${details.leave_type.name}</p>
                    <p><strong>Dates:</strong> ${startDate} to ${endDate}</p>
                    <p><strong>Reason:</strong> ${details.reason}</p>
                    <p><strong>Status:</strong> <span class="badge badge-primary">${details.current_status}</span></p>
                `;
            } else if (props.type === 'holiday') {
                const details = props.details;
                const holidayDate = new Date(details.date).toLocaleDateString();
                bodyContent = `
                    <p><strong>Holiday:</strong> ${details.name}</p>
                    <p><strong>Date:</strong> ${holidayDate}</p>
                    <p class="badge badge-success text-white mt-2">Public Holiday</p>
                `;
            }
            
            modalBody.innerHTML = bodyContent;
            modal.showModal();
        }
    });

    calendar.render();
});

function dashboardStats(baseApiUrl) {
    return {
        stats: {
            total_users: 0,
            approved_this_month: 0,
            pending_requests: 0,
        },
        loading: true,
        fetchStats() {
            const token = localStorage.getItem('authToken');
            if (!token) return;

            fetch(`${baseApiUrl}/admin/dashboard/stats`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if(data.data) {
                    this.stats = data.data;
                }
            })
            .catch(error => {
                console.error('Error fetching stats:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Could not fetch dashboard stats.',
                });
            })
            .finally(() => {
                this.loading = false;
            });
        }
    }
}
</script>
@endpush