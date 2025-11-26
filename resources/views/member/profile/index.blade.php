@extends('template.member')

@section('title', 'My Profile')

@section('content')
<div x-data="userProfile('{{ config('app.base_api') }}')" x-init="init()" class="max-w-4xl mx-auto">
    
    <!-- Profile Header Card -->
    <div class="card bg-gradient-to-br from-primary to-secondary text-primary-content shadow-xl mb-6">
        <div class="card-body items-center text-center py-8">
            <div class="avatar online placeholder mb-4">
                <div class="bg-base-100 text-primary rounded-full w-24 ring ring-primary-content ring-offset-base-100 ring-offset-2">
                    <span class="text-3xl font-bold" x-text="user.name ? user.name.charAt(0).toUpperCase() : 'U'"></span>
                </div>
            </div>
            <h2 class="card-title text-2xl font-bold" x-text="user.name">Loading...</h2>
            <div class="badge badge-lg badge-ghost" x-text="user.employee_code">-</div>
            <div class="flex gap-2 mt-2">
                <div class="badge badge-outline" x-text="user.department?.name || 'No Department'"></div>
                <div class="badge badge-outline" x-text="user.role || 'Employee'"></div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Email Card -->
        <div class="stat bg-base-100 shadow rounded-box">
            <div class="stat-figure text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>
            <div class="stat-title">Email</div>
            <div class="stat-value text-sm" x-text="user.email">-</div>
        </div>

        <!-- Phone Card -->
        <div class="stat bg-base-100 shadow rounded-box">
            <div class="stat-figure text-success">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                </svg>
            </div>
            <div class="stat-title">WhatsApp</div>
            <div class="stat-value text-sm flex items-center justify-between">
                <span x-text="user.phone_number || '-'">-</span>
                <button @click="openEditPhoneModal" class="btn btn-ghost btn-xs btn-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Tenure Card -->
        <div class="stat bg-base-100 shadow rounded-box">
            <div class="stat-figure text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </div>
            <div class="stat-title">Joined</div>
            <div class="stat-value text-xs" x-text="formatDate(user.hire_date)">-</div>
            <div class="stat-desc" x-text="calculateTenure(user.hire_date)"></div>
        </div>
    </div>

    <!-- Leave Balances -->
    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            <h2 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                Leave Balances
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                <template x-for="balance in balances" :key="balance.leave_type_id">
                    <div class="stat bg-base-200 rounded-box p-4">
                        <div class="stat-title text-xs" x-text="balance.leave_type_name"></div>
                        <div class="stat-value text-primary text-2xl" x-text="balance.remaining_days"></div>
                        <div class="stat-desc">Days left</div>
                    </div>
                </template>
                <template x-if="balances.length === 0">
                    <div class="col-span-2 md:col-span-4 text-center py-8 text-base-content/50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-2 opacity-30">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        <p>No leave balance data available</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Admin Dashboard (if Super Admin) -->
        <template x-if="user.role && user.role.includes('Super Admin')">
            <a href="{{ route('admin.dashboard.index') }}" class="btn btn-primary btn-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                Admin Dashboard
            </a>
        </template>

        <!-- Change Password -->
        <button @click="openChangePasswordModal" class="btn btn-warning btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            Change Password
        </button>

        <!-- Logout -->
        <button @click="logout" class="btn btn-error btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
            </svg>
            Logout
        </button>
    </div>

    <!-- Edit Phone Number Modal -->
    <dialog id="editPhoneModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-success">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                </svg>
                Edit WhatsApp Number
            </h3>
            <form @submit.prevent="updatePhoneNumber" class="mt-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Phone Number</span>
                    </label>
                    <label class="input input-bordered flex items-center gap-2">
                        <span class="text-base-content/50">+62</span>
                        <input 
                            type="tel" 
                            x-model="editPhone" 
                            placeholder="8123456789" 
                            class="grow" 
                            required 
                            pattern="[0-9]+"
                            minlength="10"
                            maxlength="15"
                        />
                    </label>
                    <label class="label">
                        <span class="label-text-alt text-info">Format: 8xxx (tanpa 0 di depan)</span>
                    </label>
                </div>
                <div class="modal-action">
                    <button type="button" @click="closeEditPhoneModal" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-success" :disabled="updatingPhone">
                        <span x-show="updatingPhone" class="loading loading-spinner"></span>
                        <span x-text="updatingPhone ? 'Updating...' : 'Update'"></span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- Change Password Modal -->
    <dialog id="changePasswordModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-warning">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                Change Password
            </h3>
            <form @submit.prevent="changePassword" class="mt-4 space-y-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Current Password</span>
                    </label>
                    <input 
                        type="password" 
                        x-model="passwordForm.current_password" 
                        placeholder="Enter current password" 
                        class="input input-bordered" 
                        required 
                    />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">New Password</span>
                    </label>
                    <input 
                        type="password" 
                        x-model="passwordForm.new_password" 
                        placeholder="Min 8 characters" 
                        class="input input-bordered" 
                        required 
                        minlength="8"
                    />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Confirm New Password</span>
                    </label>
                    <input 
                        type="password" 
                        x-model="passwordForm.new_password_confirmation" 
                        placeholder="Confirm new password" 
                        class="input input-bordered" 
                        required 
                        minlength="8"
                    />
                </div>
                <div class="modal-action">
                    <button type="button" @click="closeChangePasswordModal" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-warning" :disabled="changingPassword">
                        <span x-show="changingPassword" class="loading loading-spinner"></span>
                        <span x-text="changingPassword ? 'Changing...' : 'Change Password'"></span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

</div>
@endsection

@push('scripts')
<script>
    function userProfile(baseApiUrl) {
        return {
            user: {},
            balances: [],
            token: localStorage.getItem('authToken'),
            editPhone: '',
            updatingPhone: false,
            passwordForm: {
                current_password: '',
                new_password: '',
                new_password_confirmation: ''
            },
            changingPassword: false,

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

            openEditPhoneModal() {
                this.editPhone = this.user.phone_number ? this.user.phone_number.replace(/^62/, '') : '';
                document.getElementById('editPhoneModal').showModal();
            },

            closeEditPhoneModal() {
                document.getElementById('editPhoneModal').close();
                this.editPhone = '';
            },

            async updatePhoneNumber() {
                this.updatingPhone = true;
                try {
                    const response = await fetch(`${baseApiUrl}/user/update-phone`, {
                        method: 'PATCH',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            phone_number: this.editPhone
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.user = data.data;
                        this.closeEditPhoneModal();
                        
                        await Swal.fire({
                            title: 'Success!',
                            text: 'Phone number updated successfully',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });

                        this.sendWhatsAppNotification(this.user.phone_number);
                    } else {
                        throw new Error(data.meta?.message || 'Failed to update phone number');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Failed to update phone number',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } finally {
                    this.updatingPhone = false;
                }
            },

            sendWhatsAppNotification(phoneNumber) {
                const message = `Halo ${this.user.name},\n\nNomor WhatsApp Anda telah berhasil diperbarui di sistem Cutikuy.\n\n📱 Nomor Baru: ${phoneNumber}\n\nJika Anda tidak melakukan perubahan ini, segera hubungi administrator.\n\nTerima kasih,\nTim Cutikuy`;
                
                const formattedPhone = phoneNumber.replace(/[^0-9]/g, '');
                const whatsappUrl = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}`;
                
                Swal.fire({
                    title: 'Send WhatsApp Notification?',
                    text: 'Would you like to send a confirmation message to your new WhatsApp number?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#25D366',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, send it!',
                    cancelButtonText: 'Skip'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.open(whatsappUrl, '_blank');
                    }
                });
            },

            openChangePasswordModal() {
                this.passwordForm = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: ''
                };
                document.getElementById('changePasswordModal').showModal();
            },

            closeChangePasswordModal() {
                document.getElementById('changePasswordModal').close();
                this.passwordForm = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: ''
                };
            },

            async changePassword() {
                if (this.passwordForm.new_password !== this.passwordForm.new_password_confirmation) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'New passwords do not match',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                this.changingPassword = true;
                try {
                    const response = await fetch(`${baseApiUrl}/user/change-password`, {
                        method: 'PATCH',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(this.passwordForm)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.closeChangePasswordModal();
                        Swal.fire({
                            title: 'Success!',
                            text: 'Password changed successfully',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        throw new Error(data.meta?.message || data.data?.message || 'Failed to change password');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Failed to change password',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } finally {
                    this.changingPassword = false;
                }
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                return new Date(dateString).toLocaleDateString('en-GB', {
                    day: 'numeric', month: 'short', year: 'numeric'
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
                    const prevMonthDate = new Date(end.getFullYear(), end.getMonth(), 0);
                    days += prevMonthDate.getDate();
                }
                
                if (months < 0) {
                    years--;
                    months += 12;
                }
                
                let result = '';
                if (years > 0) result += `${years}y `;
                if (months > 0) result += `${months}m `;
                if (days > 0 && years === 0) result += `${days}d`;
                
                return result.trim() || 'Today';
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
