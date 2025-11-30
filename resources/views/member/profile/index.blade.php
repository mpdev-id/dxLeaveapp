@extends('template.member')

@section('title', 'My Profile')

@section('content')
<div x-data="userProfile('{{ config('app.base_api') }}', '{{ config('webpush.vapid.public_key') }}')" x-init="init()" class="max-w-5xl mx-auto space-y-6 pb-20 md:pb-6">
    
    <!-- Profile Header -->
    <div class="card bg-base-100 shadow-xl overflow-hidden border border-base-200">
        <div class="h-32 bg-gradient-to-r from-primary to-secondary relative">
            <div class="absolute inset-0 bg-white/10 pattern-dots"></div>
        </div>
        <div class="card-body pt-0 relative">
            <div class="flex flex-col md:flex-row items-center md:items-end -mt-16 mb-4 gap-6">
                <div class="avatar online placeholder">
                    <div class="w-32 rounded-full ring ring-base-100 ring-offset-base-100 ring-offset-2 shadow-2xl bg-base-100">
                        <img
                            alt="User Avatar"
                            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'U')}&background=random&color=fff&size=128&bold=true`"
                            class="w-full h-full object-cover"
                        />
                    </div>
                </div>
                <div class="text-center md:text-left flex-1">
                    <div class="flex items-center gap-2 justify-center md:justify-start">
                        <h2 class="text-3xl font-bold" x-text="user.name">Loading...</h2>
                        <button @click="openEditProfileModal" class="btn btn-ghost btn-sm btn-circle text-primary" title="Edit Profile">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-base-content/60 font-medium" x-text="user.email">-</p>
                </div>
                <div class="flex flex-wrap justify-center gap-2 mt-4 md:mt-0">
                     <div class="badge badge-lg badge-primary" x-text="user.department?.name || 'No Department'"></div>
                     <div class="badge badge-lg badge-secondary" x-text="user.plant?.name || 'No Plant'"></div>
                     <div class="badge badge-lg badge-accent" x-text="user.plant?.team?.name || 'No Team'"></div>
                     <div class="badge badge-lg badge-outline" x-text="user.role || 'Employee'"></div>
                </div>
            </div>
            
            <div class="divider my-0"></div>

            <!-- Personal Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                <!-- Employee ID -->
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200/50 transition-colors">
                    <div class="p-3 bg-primary/10 rounded-xl text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.5 1.343-1 1.343m-1.343 0H9m1.343 0h1.343" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-base-content/60 font-medium uppercase tracking-wider">Employee ID</div>
                        <div class="font-bold text-lg" x-text="user.employee_code">-</div>
                    </div>
                </div>

                <!-- Phone -->
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200/50 transition-colors">
                    <div class="p-3 bg-success/10 rounded-xl text-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs text-base-content/60 font-medium uppercase tracking-wider">WhatsApp</div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-lg" x-text="user.phone_number || '-'">-</span>
                            <div class="flex gap-1">
                                <button @click="openEditPhoneModal" class="btn btn-ghost btn-xs btn-square text-info" title="Edit Number">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>
                                <button @click="testWhatsApp" :disabled="testingWhatsApp" class="btn btn-ghost btn-xs btn-square text-success" title="Test WhatsApp Connection">
                                    <span x-show="testingWhatsApp" class="loading loading-spinner loading-xs"></span>
                                    <svg x-show="!testingWhatsApp" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tenure -->
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200/50 transition-colors">
                    <div class="p-3 bg-warning/10 rounded-xl text-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-base-content/60 font-medium uppercase tracking-wider">Joined Date</div>
                        <div class="font-bold text-lg" x-text="formatDate(user.hire_date)">-</div>
                        <div class="text-xs text-base-content/40" x-text="calculateTenure(user.hire_date)"></div>
                    </div>
                </div>

                <!-- Digital Signature -->
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200/50 transition-colors">
                    <div class="p-3 bg-secondary/10 rounded-xl text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs text-base-content/60 font-medium uppercase tracking-wider">Digital Signature</div>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="h-10 w-24 bg-base-200 rounded flex items-center justify-center overflow-hidden border border-base-300 cursor-pointer hover:bg-base-300 transition-colors" @click="user.signature_url ? openSignaturePreview() : null">
                                <template x-if="user.signature_url">
                                    <img :src="user.signature_url" alt="Signature" class="h-full w-full object-contain">
                                </template>
                                <template x-if="!user.signature_url">
                                    <span class="text-xs text-base-content/40 italic">None</span>
                                </template>
                            </div>
                            <button @click="openSignatureModal" class="btn btn-ghost btn-xs btn-square text-secondary" title="Update Signature">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Balances Section -->
    <div>
        <h3 class="text-lg font-bold mb-4 flex items-center gap-2 px-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
            </svg>
            Leave Balances
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <template x-for="balance in balances" :key="balance.leave_type_id">
                <div class="card bg-base-100 shadow-sm hover:shadow-md transition-all duration-300 border border-base-200 group">
                    <div class="card-body p-5">
                        <div class="flex justify-between items-start">
                            <div class="text-xs text-base-content/60 font-bold uppercase tracking-wider" x-text="balance.leave_type_name"></div>
                            <div class="p-1.5 rounded-md bg-primary/5 text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-black text-base-content" x-text="balance.remaining_days"></span>
                                <span class="text-sm text-base-content/60 font-medium">days left</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="balances.length === 0">
                <div class="col-span-full card bg-base-100 border border-base-200 border-dashed">
                    <div class="card-body items-center text-center py-8 text-base-content/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-20 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p>No leave balances found.</p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Account Actions -->
    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body">
            <h3 class="card-title text-lg mb-4">Account Settings</h3>
            <div class="flex flex-wrap gap-3">
                <template x-if="user.role && user.role.includes('Super Admin')">
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Admin Dashboard
                    </a>
                </template>

                <button @click="togglePushNotifications" class="btn" :class="isPushEnabled ? 'btn-success text-white' : 'btn-outline'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span x-text="isPushEnabled ? 'Notifications Enabled' : 'Enable Notifications'"></span>
                    <span x-show="loadingPush" class="loading loading-spinner loading-xs"></span>
                </button>

                <button @click="testPushNotification" class="btn btn-info btn-outline" :disabled="!isPushEnabled || testingPush">
                    <span x-show="testingPush" class="loading loading-spinner loading-xs"></span>
                    <svg x-show="!testingPush" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span x-text="testingPush ? 'Sending...' : 'Test Push'"></span>
                </button>

                <button @click="openChangePasswordModal" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Change Password
                </button>

                <button @click="logout" class="btn btn-error btn-outline ml-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Phone Number Modal -->
    <dialog id="editPhoneModal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="font-bold text-lg flex items-center gap-2 text-success">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
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
                        <span class="text-base-content/50 font-semibold">+62</span>
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
                        <span class="label-text-alt text-info flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Format: 8xxx (without leading 0)
                        </span>
                    </label>
                </div>
                <div class="modal-action">
                    <button type="button" @click="closeEditPhoneModal" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-success" :disabled="updatingPhone">
                        <span x-show="updatingPhone" class="loading loading-spinner"></span>
                        <span x-text="updatingPhone ? 'Updating...' : 'Update Number'"></span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- Edit Profile Modal -->
    <dialog id="editProfileModal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="font-bold text-lg flex items-center gap-2 text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Profile
            </h3>
            <form @submit.prevent="updateProfile" class="mt-4 space-y-3">
                <!-- Name -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Full Name</span>
                    </label>
                    <input type="text" x-model="editForm.name" class="input input-bordered w-full" required />
                </div>

                <!-- Email -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Email Address</span>
                    </label>
                    <input type="email" x-model="editForm.email" class="input input-bordered w-full" required />
                </div>

                <!-- Department -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Department</span>
                    </label>
                    <select x-model="editForm.department_id" class="select select-bordered w-full">
                        <option value="">Select Department</option>
                        <template x-for="dept in departments" :key="dept.id">
                            <option :value="dept.id" x-text="dept.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Team (Filter) -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Team</span>
                    </label>
                    <select x-model="editForm.team_id" class="select select-bordered w-full" :disabled="!editForm.department_id">
                        <option value="">Select Team</option>
                        <template x-for="team in filteredTeams" :key="team.id">
                            <option :value="team.id" x-text="team.name"></option>
                        </template>
                    </select>
                </div>

                <!-- Plant -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Plant</span>
                    </label>
                    <select x-model="editForm.plant_id" class="select select-bordered w-full" :disabled="!editForm.team_id">
                        <option value="">Select Plant</option>
                        <template x-for="plant in filteredPlants" :key="plant.id">
                            <option :value="plant.id" x-text="plant.name"></option>
                        </template>
                    </select>
                </div>

                <div class="modal-action">
                    <button type="button" @click="closeEditProfileModal" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary" :disabled="updatingProfile">
                        <span x-show="updatingProfile" class="loading loading-spinner"></span>
                        <span x-text="updatingProfile ? 'Saving...' : 'Save Changes'"></span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- Change Password Modal -->
    <dialog id="changePasswordModal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="font-bold text-lg flex items-center gap-2 text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
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

    <!-- Update Signature Modal -->
    <dialog id="signatureModal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="font-bold text-lg flex items-center gap-2 text-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
                Update Digital Signature
            </h3>
            <div class="mt-4">
                <p class="text-sm text-base-content/70 mb-2">Draw your signature below. This will be used for your leave requests.</p>
                
                <div class="w-full flex flex-col items-center">
                    <div class="w-full max-w-sm aspect-square border-2 border-dashed border-gray-300 rounded-lg bg-white relative">
                        <canvas id="profile-signature-pad" class="absolute inset-0 w-full h-full touch-none"></canvas>
                    </div>
                    <div class="w-full max-w-sm flex justify-between mt-2">
                        <span class="text-xs text-gray-500">Sign above</span>
                        <div class="flex gap-2">
                            <button type="button" @click="undoSignature" class="btn btn-xs btn-ghost" :disabled="historyStep < 0" title="Undo">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                            </button>
                            <button type="button" @click="redoSignature" class="btn btn-xs btn-ghost" :disabled="historyStep >= history.length - 1" title="Redo">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" /></svg>
                            </button>
                            <button type="button" @click="clearSignature" class="btn btn-xs btn-ghost text-error" title="Clear">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" @click="closeSignatureModal" class="btn btn-ghost">Cancel</button>
                <button type="button" @click="saveSignature" class="btn btn-secondary" :disabled="savingSignature">
                    <span x-show="savingSignature" class="loading loading-spinner"></span>
                    <span x-text="savingSignature ? 'Saving...' : 'Save Signature'"></span>
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- Signature Preview Modal -->
    <dialog id="signaturePreviewModal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="font-bold text-lg flex items-center gap-2 text-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                My Signature
            </h3>
            <div class="mt-4 flex justify-center bg-base-200 p-4 rounded-lg">
                <img :src="user.signature_url" alt="Signature Preview" class="max-w-full max-h-64 object-contain">
            </div>
            <div class="modal-action">
                <button type="button" @click="closeSignaturePreview" class="btn btn-ghost">Close</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    function urlBase64ToUint8Array(base64String) {
        if (!base64String) {
            console.error('VAPID Public Key is missing.');
            return new Uint8Array(0);
        }
        
        // Remove any whitespace and clean the string
        const base64Clean = base64String.replace(/\s/g, '');
        
        // Calculate padding
        const padding = '='.repeat((4 - base64Clean.length % 4) % 4);
        
        // Add padding and replace URL-safe characters
        const base64 = (base64Clean + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        try {
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        } catch (e) {
            console.error('Failed to decode VAPID key:', e);
            console.log('Input string:', base64String);
            console.log('Processed string:', base64);
            throw e;
        }
    }

    function userProfile(baseApiUrl, vapidKey) {
        return {
            user: {},
            balances: [],
            departments: [],
            teams: [],
            plants: [],
            token: localStorage.getItem('authToken'),
            vapidPublicKey: vapidKey,
            editPhone: '',
            updatingPhone: false,
            editForm: {
                name: '',
                email: '',
                department_id: '',
                team_id: '', // Virtual field for filtering
                plant_id: ''
            },
            updatingProfile: false,
            passwordForm: {
                current_password: '',
                new_password: '',
                new_password_confirmation: ''
            },
            changingPassword: false,
            testingWhatsApp: false,
            isPushEnabled: false,
            loadingPush: false,
            testingPush: false,
            signaturePad: null,
            history: [],
            historyStep: -1,
            savingSignature: false,
            vapidPublicKey: '{{ config('webpush.vapid.public_key') }}'.trim(),

            async init() {
                if (!this.token) return;
                await Promise.all([
                    this.fetchUser(),
                    this.fetchBalances(),
                    this.checkPushSubscription(),
                    this.fetchDepartments(),
                    this.fetchTeams(),
                    this.fetchPlants()
                ]);
            },

            get filteredTeams() {
                if (!this.editForm.department_id || !Array.isArray(this.teams)) return [];
                return this.teams.filter(t => t.department_id == this.editForm.department_id);
            },

            get filteredPlants() {
                if (!this.editForm.team_id || !Array.isArray(this.plants)) return [];
                return this.plants.filter(p => p.team_id == this.editForm.team_id);
            },

            async fetchDepartments() {
                try {
                    const response = await fetch(`${baseApiUrl}/departments?all=true`, {
                        headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (Array.isArray(result.data)) {
                        this.departments = result.data;
                    } else if (result.data && Array.isArray(result.data.data)) {
                        this.departments = result.data.data;
                    } else {
                        this.departments = [];
                    }
                    console.log('Departments loaded:', this.departments.length);
                } catch (e) { console.error('Error fetching departments:', e); this.departments = []; }
            },

            async fetchTeams() {
                try {
                    const response = await fetch(`${baseApiUrl}/teams?all=true`, {
                        headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (Array.isArray(result.data)) {
                        this.teams = result.data;
                    } else if (result.data && Array.isArray(result.data.data)) {
                        this.teams = result.data.data;
                    } else {
                        this.teams = [];
                    }
                    console.log('Teams loaded:', this.teams.length);
                } catch (e) { 
                    console.error('Error fetching teams:', e); 
                    this.teams = [];
                }
            },

            async fetchPlants() {
                try {
                    const response = await fetch(`${baseApiUrl}/plants?all=true`, {
                        headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                    });
                    const result = await response.json();
                    if (Array.isArray(result.data)) {
                        this.plants = result.data;
                    } else if (result.data && Array.isArray(result.data.data)) {
                        this.plants = result.data.data;
                    } else {
                        this.plants = [];
                    }
                    console.log('Plants loaded:', this.plants.length);
                } catch (e) { 
                    console.error('Error fetching plants:', e); 
                    this.plants = [];
                }
            },

            async checkPushSubscription() {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    return;
                }
                
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                this.isPushEnabled = !!subscription;
            },

            async togglePushNotifications() {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    Swal.fire('Error', 'Push notifications are not supported in this browser.', 'error');
                    return;
                }

                this.loadingPush = true;

                try {
                    const registration = await navigator.serviceWorker.ready;
                    
                    if (this.isPushEnabled) {
                        // Unsubscribe
                        const subscription = await registration.pushManager.getSubscription();
                        if (subscription) {
                            await subscription.unsubscribe();
                            await this.updateServerSubscription(subscription, 'unsubscribe');
                            this.isPushEnabled = false;
                            Swal.fire('Unsubscribed', 'You will no longer receive push notifications.', 'success');
                        }
                    } else {
                        // Subscribe
                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(this.vapidPublicKey)
                        });
                        
                        await this.updateServerSubscription(subscription, 'subscribe');
                        this.isPushEnabled = true;
                        Swal.fire('Subscribed', 'You will now receive push notifications!', 'success');
                    }
                } catch (error) {
                    console.error('Push notification error:', error);
                    Swal.fire('Error', 'Failed to update push notification settings.', 'error');
                } finally {
                    this.loadingPush = false;
                }
            },

            async updateServerSubscription(subscription, action) {
                const endpoint = action === 'subscribe' ? '/push/subscribe' : '/push/unsubscribe';
                
                try {
                    const response = await fetch(`${baseApiUrl}${endpoint}`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(subscription)
                    });

                    const data = await response.json();
                    
                    if (!response.ok) {
                        console.error('Server subscription error:', data);
                        throw new Error(data.message || 'Failed to update subscription on server');
                    }
                    
                    console.log('Subscription updated on server:', data);
                    return data;
                } catch (error) {
                    console.error('Failed to update server subscription:', error);
                    throw error;
                }
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

            openEditProfileModal() {
                this.editForm = {
                    name: this.user.name,
                    email: this.user.email,
                    department_id: this.user.department_id,
                    plant_id: this.user.plant_id,
                    team_id: this.user.team?.id || ''
                };
                document.getElementById('editProfileModal').showModal();
            },

            closeEditProfileModal() {
                document.getElementById('editProfileModal').close();
            },

            async updateProfile() {
                this.updatingProfile = true;
                try {
                    const response = await fetch(`${baseApiUrl}/user/update-profile`, {
                        method: 'PATCH',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: this.editForm.name,
                            email: this.editForm.email,
                            department_id: this.editForm.department_id,
                            plant_id: this.editForm.plant_id
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.user = data.data;
                        this.closeEditProfileModal();
                        Swal.fire('Success', 'Profile updated successfully', 'success');
                    } else {
                        throw new Error(data.meta?.message || 'Failed to update profile');
                    }
                } catch (error) {
                    Swal.fire('Error', error.message || 'Failed to update profile', 'error');
                } finally {
                    this.updatingProfile = false;
                }
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

                        // this.sendWhatsAppNotification(this.user.phone_number);
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

            async testWhatsApp() {
                if (!this.user.phone_number) {
                    Swal.fire({
                        title: 'No Phone Number',
                        text: 'Please add your WhatsApp number first',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                this.testingWhatsApp = true;
                try {
                    const response = await fetch(`${baseApiUrl}/user/test-whatsapp`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            title: 'Success!',
                            html: `Test message sent to:<br><strong>${this.user.phone_number}</strong><br><br>Please check your WhatsApp! 📱`,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        throw new Error(data.meta?.message || data.data?.message || 'Failed to send test message');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Failed to send test message',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } finally {
                    this.testingWhatsApp = false;
                }
            },

            async testPushNotification() {
                if (!this.isPushEnabled) {
                    Swal.fire({
                        title: 'Push Notifications Disabled',
                        text: 'Please enable push notifications first',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                this.testingPush = true;
                try {
                    const response = await fetch(`${baseApiUrl}/user/test-push`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        Swal.fire({
                            title: 'Success!',
                            html: `Test notification sent!\u003cbr\u003e\u003cbr\u003eCheck your browser notifications! 🔔`,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        throw new Error(data.message || 'Failed to send test notification');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Failed to send test notification',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } finally {
                    this.testingPush = false;
                }
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
            },

            openSignatureModal() {
                document.getElementById('signatureModal').showModal();
                
                // Initialize pad if not already
                this.$nextTick(() => {
                    const canvas = document.getElementById('profile-signature-pad');
                    if (canvas && !this.signaturePad) {
                        this.signaturePad = new SignaturePad(canvas, {
                            backgroundColor: 'rgba(255, 255, 255, 0)'
                        });

                        this.signaturePad.addEventListener("endStroke", () => {
                            this.saveHistory();
                        });

                        window.addEventListener("resize", () => this.resizeCanvas());
                    }
                    // Always resize when opening to ensure correct dimensions
                    this.resizeCanvas();
                });
            },

            closeSignatureModal() {
                document.getElementById('signatureModal').close();
                if (this.signaturePad) {
                    this.signaturePad.clear();
                }
            },

            openSignaturePreview() {
                document.getElementById('signaturePreviewModal').showModal();
            },

            closeSignaturePreview() {
                document.getElementById('signaturePreviewModal').close();
            },

            resizeCanvas() {
                const canvas = document.getElementById('profile-signature-pad');
                if (canvas && canvas.offsetWidth > 0) {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    
                    // Store current data to restore after resize
                    let data = null;
                    if (this.signaturePad) {
                        data = this.signaturePad.toData();
                    }

                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                    
                    if (this.signaturePad) {
                        this.signaturePad.clear(); 
                        if (data) {
                            this.signaturePad.fromData(data);
                        }
                    }
                }
            },

            saveHistory() {
                this.history = this.history.slice(0, this.historyStep + 1);
                // Deep copy to avoid reference issues
                this.history.push(JSON.parse(JSON.stringify(this.signaturePad.toData())));
                this.historyStep++;
            },

            undoSignature() {
                if (this.historyStep >= 0) {
                    this.historyStep--;
                    if (this.historyStep >= 0) {
                        this.signaturePad.fromData(this.history[this.historyStep]);
                    } else {
                        this.signaturePad.clear();
                    }
                }
            },

            redoSignature() {
                if (this.historyStep < this.history.length - 1) {
                    this.historyStep++;
                    this.signaturePad.fromData(this.history[this.historyStep]);
                }
            },

            clearSignature() {
                if (this.signaturePad) {
                    this.signaturePad.clear();
                    this.saveHistory();
                }
            },

            async saveSignature() {
                if (!this.signaturePad || this.signaturePad.isEmpty()) {
                    Swal.fire('Error', 'Please provide a signature', 'error');
                    return;
                }

                this.savingSignature = true;
                try {
                    const signatureData = this.signaturePad.toDataURL();
                    
                    const response = await fetch(`${baseApiUrl}/user/update-signature`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            signature: signatureData
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.user = data.data; // Update user data to show new signature
                        this.closeSignatureModal();
                        Swal.fire('Success', 'Signature updated successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to update signature');
                    }
                } catch (error) {
                    console.error('Error saving signature:', error);
                    Swal.fire('Error', error.message || 'Failed to save signature', 'error');
                } finally {
                    this.savingSignature = false;
                }
            }
        }
    }
</script>
@endpush
