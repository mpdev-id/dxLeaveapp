@extends('template.admin')

@section('title', 'Push Notifications')

@section('content')
<div x-data="pushNotificationTest('{{ config('app.base_api') }}')" x-init="init()" class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Push Notification Center</h1>
            <p class="text-sm text-base-content/60 mt-1">Send test notifications to subscribed users</p>
        </div>
        <button 
            @click="fetchSubscribedUsers" 
            class="btn btn-sm btn-ghost gap-2" 
            :disabled="loading"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{'animate-spin': loading}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh Data
        </button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card bg-base-100 border border-base-300 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide">Total Subscribed</p>
                        <p class="text-3xl font-bold mt-2" x-text="subscribedUsers.length">0</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-base-content/50 mt-2">Active users with notifications enabled</p>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide">Selected</p>
                        <p class="text-3xl font-bold mt-2" x-text="form.user_ids.length">0</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-base-content/50 mt-2">Recipients ready to receive</p>
            </div>
        </div>

        <div class="card bg-base-100 border border-base-300 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide">Filtered</p>
                        <p class="text-3xl font-bold mt-2" x-text="filteredUsers.length">0</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-info/10 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-base-content/50 mt-2">Matching current filters</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Compose Panel -->
        <div class="lg:col-span-4 space-y-4">
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold">Compose Message</h2>
                    </div>
                    
                    <form @submit.prevent="sendTestNotification" class="space-y-4">
                        <div class="form-control">
                            <label class="label pb-1">
                                <span class="label-text text-sm font-medium">Title</span>
                                <span class="label-text-alt text-xs" x-text="form.title.length + '/100'"></span>
                            </label>
                            <input 
                                type="text" 
                                x-model="form.title" 
                                placeholder="Enter notification title" 
                                class="input input-sm input-bordered w-full focus:input-primary"
                                maxlength="100"
                                required
                            />
                        </div>

                        <div class="form-control">
                            <label class="label pb-1">
                                <span class="label-text text-sm font-medium">Message</span>
                                <span class="label-text-alt text-xs" x-text="form.body.length + '/255'"></span>
                            </label>
                            <textarea 
                                x-model="form.body" 
                                placeholder="Enter notification message" 
                                class="textarea textarea-sm textarea-bordered w-full focus:textarea-primary"
                                rows="4"
                                maxlength="255"
                                required
                            ></textarea>
                        </div>

                        <!-- Preview -->
                        <div class="bg-base-200 rounded-lg p-4" x-show="form.title || form.body" x-transition>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-base-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-base-content/50 mb-1">Preview</p>
                                    <p class="font-semibold text-sm" x-text="form.title || 'Notification Title'"></p>
                                    <p class="text-xs text-base-content/70 mt-1" x-text="form.body || 'Notification message will appear here'"></p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 space-y-2">
                            <button 
                                type="submit" 
                                class="btn btn-primary btn-sm w-full gap-2"
                                :disabled="loading || form.user_ids.length === 0"
                            >
                                <span x-show="!loading">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </span>
                                <span x-show="loading" class="loading loading-dots loading-xs"></span>
                                <span x-text="loading ? 'Sending...' : 'Send to ' + form.user_ids.length + ' user(s)'"></span>
                            </button>

                            <button 
                                type="button" 
                                @click="sendToAll"
                                class="btn btn-outline btn-sm w-full gap-2"
                                :disabled="loading || subscribedUsers.length === 0"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Broadcast to All
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card bg-gradient-to-br from-info/5 to-info/10 border border-info/20">
                <div class="card-body p-4">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-info shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs space-y-1 text-base-content/70">
                            <p class="font-semibold text-sm text-base-content">Quick Tips</p>
                            <ul class="space-y-0.5 list-disc list-inside">
                                <li>Use filters to target specific users</li>
                                <li>Preview shows how notification appears</li>
                                <li>Notifications appear in user's browser</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recipients Panel -->
        <div class="lg:col-span-8">
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-secondary/10 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold">Select Recipients</h2>
                        </div>
                        <div class="flex gap-2">
                            <button @click="selectAll" class="btn btn-xs btn-ghost gap-1" :disabled="filteredUsers.length === 0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                All
                            </button>
                            <button @click="deselectAll" class="btn btn-xs btn-ghost gap-1" :disabled="form.user_ids.length === 0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                        <div class="form-control">
                            <div class="join w-full">
                                <div class="join-item bg-base-200 flex items-center px-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    x-model="searchQuery" 
                                    placeholder="Search users..." 
                                    class="input input-sm input-bordered join-item w-full focus:outline-none"
                                />
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-ghost join-item" 
                                    @click="searchQuery = ''"
                                    x-show="searchQuery"
                                    x-transition
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-control">
                            <select x-model="departmentFilter" class="select select-sm select-bordered w-full">
                                <option value="">All Departments</option>
                                <template x-for="dept in departments" :key="dept.id">
                                    <option :value="dept.id" x-text="dept.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- User List -->
                    <div class="border border-base-300 rounded-lg bg-base-50 max-h-[500px] overflow-y-auto">
                        <!-- Loading -->
                        <template x-if="loading && subscribedUsers.length === 0">
                            <div class="flex flex-col items-center justify-center py-16">
                                <span class="loading loading-dots loading-lg text-primary"></span>
                                <p class="text-sm text-base-content/60 mt-4">Loading users...</p>
                            </div>
                        </template>

                        <!-- Empty -->
                        <template x-if="!loading && subscribedUsers.length === 0">
                            <div class="flex flex-col items-center justify-center py-16 text-base-content/50">
                                <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <p class="font-semibold text-sm">No Subscribed Users</p>
                                <p class="text-xs mt-1">Users need to enable notifications in their profile</p>
                            </div>
                        </template>

                        <!-- No Results -->
                        <template x-if="!loading && filteredUsers.length === 0 && subscribedUsers.length > 0">
                            <div class="flex flex-col items-center justify-center py-16 text-base-content/50">
                                <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <p class="font-semibold text-sm">No Users Found</p>
                                <p class="text-xs mt-1">Try adjusting your filters</p>
                                <button @click="searchQuery = ''; departmentFilter = ''" class="btn btn-xs btn-ghost mt-3">
                                    Clear Filters
                                </button>
                            </div>
                        </template>

                        <!-- User Grid -->
                        <div class="p-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                            <template x-for="user in filteredUsers" :key="user.id">
                                <label 
                                    class="flex items-center gap-3 p-3 rounded-lg border border-transparent hover:border-primary/30 hover:bg-primary/5 cursor-pointer transition-all"
                                    :class="{'border-primary bg-primary/10': form.user_ids.includes(user.id)}"
                                >
                                    <input 
                                        type="checkbox" 
                                        :value="user.id" 
                                        x-model="form.user_ids" 
                                        class="checkbox checkbox-sm checkbox-primary"
                                    />
                                    <div class="avatar">
                                        <div class="w-10 rounded-lg">
                                            <img 
                                                alt="User Avatar" 
                                                :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff&size=128&bold=true`" 
                                                class="w-full h-full object-cover"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm truncate" x-text="user.name"></p>
                                        <p class="text-xs text-base-content/60 truncate" x-text="user.email"></p>
                                        <div class="flex gap-1 mt-1.5 flex-wrap">
                                            <span class="badge badge-xs badge-ghost" x-text="user.employee_code"></span>
                                            <span class="badge badge-xs badge-outline" x-text="user.department?.name || 'No Dept'"></span>
                                            <span class="badge badge-xs badge-primary gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                                <span x-text="user.push_subscriptions_count"></span>
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function pushNotificationTest(baseApiUrl) {
        return {
            subscribedUsers: [],
            departments: [],
            loading: false,
            searchQuery: '',
            departmentFilter: '',
            form: {
                user_ids: [],
                title: 'Test Notification',
                body: 'This is a test push notification from Cutikuy! 🦆'
            },
            token: localStorage.getItem('authToken'),

            get filteredUsers() {
                let filtered = this.subscribedUsers;

                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    filtered = filtered.filter(user => 
                        user.name.toLowerCase().includes(query) ||
                        user.email.toLowerCase().includes(query) ||
                        (user.employee_code && user.employee_code.toLowerCase().includes(query))
                    );
                }

                if (this.departmentFilter) {
                    filtered = filtered.filter(user => 
                        user.department && user.department.id == this.departmentFilter
                    );
                }

                return filtered;
            },

            async init() {
                await Promise.all([
                    this.fetchSubscribedUsers(),
                    this.fetchDepartments()
                ]);
            },

            async fetchDepartments() {
                try {
                    const response = await fetch(`${baseApiUrl}/departments`, {
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok) {
                        this.departments = data.data || data;
                    }
                } catch (error) {
                    console.error('Error fetching departments:', error);
                }
            },

            async fetchSubscribedUsers() {
                this.loading = true;
                try {
                    const response = await fetch(`${baseApiUrl}/admin/master/push-notifications/subscribed-users`, {
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok) {
                        this.subscribedUsers = data.data;
                    } else {
                        throw new Error(data.message || 'Failed to fetch subscribed users');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    this.loading = false;
                }
            },

            async sendTestNotification() {
                if (this.form.user_ids.length === 0) {
                    Swal.fire('Warning', 'Please select at least one user', 'warning');
                    return;
                }

                this.loading = true;
                try {
                    const response = await fetch(`${baseApiUrl}/admin/master/push-notifications/test`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await response.json();
                    if (response.ok) {
                        Swal.fire({
                            title: 'Success!',
                            html: `${data.message}<br><br>Sent to: <strong>${data.sent_to.join(', ')}</strong>`,
                            icon: 'success'
                        });
                        this.form.user_ids = [];
                    } else {
                        throw new Error(data.message || 'Failed to send notification');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    this.loading = false;
                }
            },

            async sendToAll() {
                const result = await Swal.fire({
                    title: 'Send to All Users?',
                    text: `This will send a test notification to all ${this.subscribedUsers.length} subscribed users.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, send it!',
                    cancelButtonText: 'Cancel'
                });

                if (!result.isConfirmed) return;

                this.loading = true;
                try {
                    const response = await fetch(`${baseApiUrl}/admin/master/push-notifications/test-all`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            title: this.form.title,
                            body: this.form.body
                        })
                    });

                    const data = await response.json();
                    if (response.ok) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success'
                        });
                    } else {
                        throw new Error(data.message || 'Failed to send notification');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message, 'error');
                } finally {
                    this.loading = false;
                }
            },

            selectAll() {
                this.form.user_ids = this.filteredUsers.map(u => u.id);
            },

            deselectAll() {
                this.form.user_ids = [];
            }
        }
    }
</script>
@endpush
