@extends('template.member')

@section('title', 'My Profile')

@section('content')
<div x-data="userProfile('{{ config('app.base_api') }}')" x-init="init()" class="pb-20">
    
    <div class="flex flex-col items-center py-6 bg-base-100 rounded-box shadow-sm mb-6">
        <div class="avatar mb-4">
            <div class="w-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'User')}&background=random&size=128`" />
            </div>
        </div>
        <h2 class="text-xl font-bold" x-text="user.name"></h2>
        <p class="text-sm text-base-content/70" x-text="user.employee_code"></p>
        <div class="badge badge-primary mt-2" x-text="user.department?.name || 'No Department'"></div>
    </div>

    <div class="bg-base-100 rounded-box shadow-sm overflow-hidden">
        <div class="p-4 border-b border-base-200 font-bold">Personal Information</div>
        <div class="p-4 space-y-4">
            <div>
                <label class="text-xs text-base-content/60 uppercase block">Email</label>
                <div class="font-medium" x-text="user.email"></div>
            </div>
            <div>
                <label class="text-xs text-base-content/60 uppercase block">Phone Number</label>
                <div class="font-medium" x-text="user.phone_number || '-'"></div>
            </div>
            <div>
                <label class="text-xs text-base-content/60 uppercase block">Hire Date</label>
                <div class="font-medium">
                    <span x-text="formatDate(user.hire_date)"></span>
                    <span class="text-sm opacity-70 ml-1" x-text="calculateTenure(user.hire_date)"></span>
                </div>
            </div>
            
            <!-- <div>
                <label class="text-xs text-base-content/60 uppercase block">Manager</label>
                <div class="font-medium" x-text="user.manager?.name || '-'"></div>
            </div> -->
        </div>
    </div>

    <div class="bg-base-100 rounded-box shadow-sm overflow-hidden mt-6">
        <div class="p-4 border-b border-base-200 font-bold">Leave Balances</div>
        <div class="p-4">
            <div class="grid grid-cols-2 gap-3">
                <template x-for="balance in balances" :key="balance.leave_type_id">
                    <div class="stat bg-base-100 shadow-sm rounded-box p-3 border border-base-200">
                        <div class="stat-title text-[10px] font-bold uppercase tracking-wider truncate" x-text="balance.leave_type_name"></div>
                        <div class="stat-value text-primary text-xl" x-text="balance.remaining_days"></div>
                        <div class="stat-desc text-[10px]">Days Remaining</div>
                    </div>
                </template>
                <template x-if="balances.length === 0">
                    <div class="col-span-2 text-center text-sm opacity-50 py-2">No leave balance data available.</div>
                </template>
            </div>
        </div>
    </div>

    <div class="mt-6 space-y-3">
        <template x-if="user.role && user.role.includes('Super Admin')">
            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-primary btn-outline w-full gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                Admin Dashboard
            </a>
        </template>
        <button @click="logout" class="btn btn-error btn-outline w-full gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            Logout
        </button>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function userProfile(baseApiUrl) {
        return {
            user: {},
            balances: [],
            token: localStorage.getItem('authToken'),

            async init() {
                if (!this.token) return;
                await Promise.all([
                    this.fetchUser(),
                    this.fetchBalances()
                ]);
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
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                return new Date(dateString).toLocaleDateString('en-GB', {
                    day: 'numeric', month: 'long', year: 'numeric'
                });
            },

            calculateTenure(dateString) {
                if (!dateString) return '';
                const start = new Date(dateString);
                const end = new Date();
                
                let years = end.getFullYear() - start.getFullYear();
                let months = end.getMonth() - start.getMonth();
                let days = end.getDate() - start.getDate();

                if (days < 0) {
                    months--;
                    // Get days in previous month
                    const prevMonthDate = new Date(end.getFullYear(), end.getMonth(), 0);
                    days += prevMonthDate.getDate();
                }
                
                if (months < 0) {
                    years--;
                    months += 12;
                }
                
                let result = '(';
                if (years > 0) result += `${years} Tahun `;
                if (months > 0) result += `${months} Bulan `;
                if (days > 0) result += `${days} Hari`;
                
                // If exactly 0 days diff (today), handle gracefully
                if (result === '(') return '(Hari Pertama)';
                
                return result.trim() + ')';
            },

            logout() {
                Swal.fire({
                    title: 'Logout?',
                    text: "Are you sure you want to logout?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, logout!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Call global logout function defined in layout or handle here
                        // We can reuse the logic from layout if accessible, or rewrite it.
                        // Layout has logoutApi(event). We can simulate it or just call fetch.
                        
                        fetch(`${baseApiUrl}/logout`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            }
                        })
                        .finally(() => {
                            localStorage.removeItem('authToken');
                            window.location.href = '/login';
                        });
                    }
                });
            }
        }
    }
</script>
@endpush
