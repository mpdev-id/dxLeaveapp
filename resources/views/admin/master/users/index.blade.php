@extends('template.admin')
@section('title', 'Admin | Manage Users')
@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8" x-data="usersTable('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Users</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add User</button>
        </div>
        <!-- Add/Edit User Modal -->
        <dialog id="user_modal" class="modal">
            <div class="modal-box w-11/12 max-w-5xl">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="hideModal('user_modal')">✕</button>
                <h3 class="font-bold text-lg mb-4" x-text="isEdit ? 'Edit User' : 'Add New User'"></h3>
                <form @submit.prevent="isEdit ? updateUser() : addUser()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    Name
                                </span>
                            </label>
                            <input type="text" x-model="newUser.name" placeholder="Full Name" class="input input-bordered w-full" :class="{'input-error': errors.name}" required>
                            <div x-show="errors.name" class="text-error text-sm mt-1" x-text="errors.name ? errors.name[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Email
                                </span>
                            </label>
                            <input type="email" x-model="newUser.email" placeholder="email@example.com" class="input input-bordered w-full" :class="{'input-error': errors.email}" required>
                            <div x-show="errors.email" class="text-error text-sm mt-1" x-text="errors.email ? errors.email[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    Employee Code
                                </span>
                            </label>
                            <input type="text" x-model="newUser.employee_code" placeholder="e.g., EMP001" class="input input-bordered w-full" :class="{'input-error': errors.employee_code}">
                             <div x-show="errors.employee_code" class="text-error text-sm mt-1" x-text="errors.employee_code ? errors.employee_code[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    Phone Number
                                </span>
                            </label>
                            <input type="text" x-model="newUser.phone_number" placeholder="e.g., +628123456789" class="input input-bordered w-full" :class="{'input-error': errors.phone_number}">
                             <div x-show="errors.phone_number" class="text-error text-sm mt-1" x-text="errors.phone_number ? errors.phone_number[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    Password (leave blank if not changing)
                                </span>
                            </label>
                            <input type="password" x-model="newUser.password" placeholder="********" class="input input-bordered w-full" :class="{'input-error': errors.password}">
                             <div x-show="errors.password" class="text-error text-sm mt-1" x-text="errors.password ? errors.password[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                                    Department
                                </span>
                            </label>
                            <select x-model="newUser.department_id" class="select select-bordered w-full" :class="{'select-error': errors.department_id}">
                                <option value="">Select Department</option>
                                <template x-for="department in departments" :key="department.id">
                                    <option :value="department.id" x-text="department.name"></option>
                                </template>
                            </select>
                             <div x-show="errors.department_id" class="text-error text-sm mt-1" x-text="errors.department_id ? errors.department_id[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h2a2 2 0 002-2V8a2 2 0 00-2-2h-2M14 10l-4 4-4-4m8 4H5" /></svg>
                                    Manager
                                </span>
                            </label>
                            <select x-model="newUser.manager_id" class="select select-bordered w-full" :class="{'select-error': errors.manager_id}">
                                <option value="">Select Manager</option>
                                <template x-for="manager in users" :key="manager.id">
                                    <option :value="manager.id" x-text="manager.name"></option>
                                </template>
                            </select>
                             <div x-show="errors.manager_id" class="text-error text-sm mt-1" x-text="errors.manager_id ? errors.manager_id[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Status
                                </span>
                            </label>
                            <select x-model="newUser.status" class="select select-bordered w-full" :class="{'select-error': errors.status}">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                             <div x-show="errors.status" class="text-error text-sm mt-1" x-text="errors.status ? errors.status[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Hire Date
                                </span>
                            </label>
                            <input type="date" x-model="newUser.hire_date" class="input input-bordered w-full" :class="{'input-error': errors.hire_date}">
                             <div x-show="errors.hire_date" class="text-error text-sm mt-1" x-text="errors.hire_date ? errors.hire_date[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm-6-6a4 4 0 100-8 4 4 0 000 8zm6 11l6-6-6-6v12z" /></svg>
                                    Roles
                                </span>
                            </label>
                            <select x-model="newUser.roles" class="select select-bordered w-full" :class="{'select-error': errors.roles}" multiple>
                                <template x-for="role in roles" :key="role.id">
                                    <option :value="role.name" x-text="role.name"></option>
                                </template>
                            </select>
                             <div x-show="errors.roles" class="text-error text-sm mt-1" x-text="errors.roles ? errors.roles[0] : ''"></div>
                        </div>
                    </div>
                    <div class="modal-action mt-6">
                        <button type="button" class="btn btn-ghost" onclick="hideModal('user_modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="loading" x-text="isEdit ? 'Update' : 'Create'"></button>
                    </div>
                </form>
            </div>
        </dialog>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Level</th>
                        <th>Join Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="5" class="text-center py-10"><span class="loading loading-spinner loading-lg"></span></td>
                        </tr>
                    </template>
                    <template x-if="!loading && users.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-4">No users found.</td>
                        </tr>
                    </template>
                    <template x-for="user in users" :key="user.id">
                        <tr>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <div class="avatar">
                                        <div class="mask mask-squircle w-12 h-12 bg-gradient-to-br from-primary to-secondary">
                                            <img 
                                                :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff&size=128&bold=true`" 
                                                :alt="user.name"
                                                class="w-full h-full object-cover"
                                                loading="lazy"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold" x-text="user.name"></div>
                                        <div class="text-sm opacity-50" x-text="user.email"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <template x-if="user.roles && user.roles.length > 0">
                                        <template x-for="role in user.roles" :key="role.id">
                                            <span class="badge badge-primary badge-sm" x-text="role.name"></span>
                                        </template>
                                    </template>
                                    <template x-if="!user.roles || user.roles.length === 0">
                                        <span class="badge badge-ghost badge-sm">No Role</span>
                                    </template>
                                </div>
                            </td>
                            <td x-text="user.hire_date ? new Date(user.hire_date).toLocaleDateString() : 'N/A'"></td>
                            <td>
                                <span class="badge rounded-3xl" :class="user.status === 'active' ? 'badge-success' : 'badge-error'" x-text="user.status"></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info  rounded-3xl" @click="openEditModal(user)">Edit</button>
                                <button class="btn btn-sm btn-error  rounded-3xl" @click="confirmDelete(user.id)">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex justify-between items-center mt-4">
            <div class="flex items-center gap-2">
                <select x-model="perPage" @change="fetchUsers" class="select select-bordered">
                    <option value="5">5 per page</option>
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
                <input type="text" x-model.debounce.500ms="search" @input="fetchUsers" placeholder="Search..." class="input input-bordered">
            </div>
            <div class="join">
                <button @click="currentPage > 1 && (currentPage--, fetchUsers())" :disabled="currentPage === 1" class="join-item btn">«</button>
                <button class="join-item btn" x-text="`Page ${currentPage}`"></button>
                <button @click="currentPage < totalPages && (currentPage++, fetchUsers())" :disabled="currentPage === totalPages" class="join-item btn">»</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    function usersTable(baseApiUrl) {
        return {
            users: [],
            departments: [],
            roles: [],
            loading: true,
            search: '',
            perPage: 10,
            currentPage: 1,
            totalPages: 1,
            isEdit: false,
            newUser: {
                id: null,
                name: '',
                employee_code: '',
                email: '',
                phone_number: '',
                password: '',
                department_id: '',
                manager_id: '',
                status: 'active',
                hire_date: '',
                roles: []
            },
            errors: {},
            init() {
                this.fetchUsers();
                this.fetchDepartments();
                this.fetchRoles();
            },
            async fetchUsers() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) {
                        window.location.href = '/login';
                        return;
                    }
                    const response = await fetch(`${baseApiUrl}/admin/master/users?page=${this.currentPage}&per_page=${this.perPage}&search=${this.search}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    if(response.status === 401) {
                        localStorage.removeItem('authToken');
                        window.location.href = '/login';
                        return;
                    }
                    const data = await response.json();
                    this.users = data.data.data;
                    this.totalPages = data.data.last_page;
                } catch (error) {
                    console.error('Error fetching users:', error);
                } finally {
                    this.loading = false;
                }
            },
            async fetchDepartments() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/departments?all=true`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    this.departments = data.data;
                } catch (error) {
                    console.error('Error fetching departments:', error);
                }
            },
            async fetchRoles() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/roles`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    this.roles = data.data;
                } catch (error) {
                    console.error('Error fetching roles:', error);
                }
            },
            showToast(message, icon = 'success') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icon,
                    title: message,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            openAddModal() {
                this.isEdit = false;
                this.newUser = {
                    id: null, name: '', employee_code: '', email: '', phone_number: '',
                    password: '', department_id: '', manager_id: '', status: 'active',
                    hire_date: '', roles: []
                };
                this.errors = {};
                showModal('user_modal');
            },
            openEditModal(user) {
                this.isEdit = true;
                this.newUser = {
                    id: user.id, name: user.name, employee_code: user.employee_code,
                    email: user.email, phone_number: user.phone_number, password: '',
                    department_id: user.department_id ? Number(user.department_id) : '',
                    manager_id: user.manager_id ? Number(user.manager_id) : '',
                    status: user.status || 'active', hire_date: user.hire_date || '',
                    roles: user.roles ? user.roles.map(role => role.name) : []
                };
                this.errors = {};
                showModal('user_modal');
            },
            async addUser() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const userData = { ...this.newUser };
                    for (const key in userData) { if (userData[key] === '') { userData[key] = null; } }
                    
                    const response = await fetch(`${baseApiUrl}/admin/master/users`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(userData)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to add user.', 'error');
                        }
                        return;
                    }
                    this.fetchUsers();
                    this.showToast(data.meta.message);
                    hideModal('user_modal');
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error adding user:', error);
                }
            },
            async updateUser() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const userData = { ...this.newUser };
                    if (userData.password === '' || userData.password === null) { delete userData.password; }
                    for (const key in userData) { if (userData[key] === '') { userData[key] = null; } }

                    const response = await fetch(`${baseApiUrl}/admin/master/users/${this.newUser.id}`, {
                        method: 'PUT',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(userData)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to update user.', 'error');
                        }
                        return;
                    }
                    this.fetchUsers();
                    this.showToast(data.meta.message);
                    hideModal('user_modal');
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error updating user:', error);
                }
            },
            confirmDelete(userId) {
                Swal.fire({
                    title: 'Are you sure?', text: "You won't be able to revert this!", icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) { this.deleteUser(userId); }
                });
            },
            async deleteUser(userId) {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/users/${userId}`, {
                        method: 'DELETE',
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        this.showToast(data.meta.message || 'Failed to delete user.', 'error');
                        return;
                    }
                    this.fetchUsers();
                    this.showToast(data.meta.message);
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error deleting user:', error);
                }
            }
        }
    }
</script>
@endpush
