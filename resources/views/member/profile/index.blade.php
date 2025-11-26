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
                <label class="text-xs text-base-content/60 uppercase block">Phone Number / WhatsApp</label>
                <div class="flex items-center justify-between">
                    <div class="font-medium" x-text="user.phone_number || '-'"></div>
                    <button @click="openEditPhoneModal" class="btn btn-xs btn-ghost btn-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </button>
                </div>
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
        <button @click="openChangePasswordModal" class="btn btn-warning btn-outline w-full gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            Change Password
        </button>
        <button @click="logout" class="btn btn-error btn-outline w-full gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            Logout
        </button>
    </div>

    <!-- Edit Phone Number Modal -->
    <dialog id="editPhoneModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Edit Phone Number / WhatsApp</h3>
            <form @submit.prevent="updatePhoneNumber">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Phone Number</span>
                    </label>
                    <input 
                        type="tel" 
                        x-model="editPhone" 
                        placeholder="08123456789 or 628123456789" 
                        class="input input-bordered" 
                        required 
                        pattern="[0-9]+"
                        minlength="10"
                        maxlength="15"
                    />
                    <label class="label">
                        <span class="label-text-alt text-info">Format: 08xxx atau 628xxx</span>
                    </label>
                </div>
                <div class="modal-action">
                    <button type="button" @click="closeEditPhoneModal" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary" :disabled="updatingPhone">
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
            <h3 class="font-bold text-lg mb-4">Change Password</h3>
            <form @submit.prevent="changePassword">
                <div class="form-control mb-4">
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
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">New Password</span>
                    </label>
                    <input 
                        type="password" 
                        x-model="passwordForm.new_password" 
                        placeholder="Enter new password (min 8 characters)" 
                        class="input input-bordered" 
                        required 
                        minlength="8"
                    />
                </div>
                <div class="form-control mb-4">
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
                this.editPhone = this.user.phone_number || '';
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
                        
                        // Show success message
                        await Swal.fire({
                            title: 'Success!',
                            text: 'Phone number updated successfully',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });

                        // Send WhatsApp notification
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
                
                // Format phone number for WhatsApp (remove + and spaces)
                const formattedPhone = phoneNumber.replace(/[^0-9]/g, '');
                
                // Create WhatsApp URL
                const whatsappUrl = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}`;
                
                // Show confirmation dialog
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
                        // Open WhatsApp in new tab
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
