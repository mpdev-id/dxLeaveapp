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
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" @click="errors = {}">✕</button>
                </form>
                <h3 class="font-bold text-lg" x-text="isEdit ? 'Edit User' : 'Add New User'"></h3>
                <form @submit.prevent="isEdit ? updateUser() : addUser()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">Name</label>
                            <input type="text" x-model="newUser.name" class="input input-bordered" :class="{'input-error': errors.name}" required>
                            <div x-show="errors.name" class="text-error text-sm mt-1" x-text="errors.name ? errors.name[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">Email</label>
                            <input type="email" x-model="newUser.email" class="input input-bordered" :class="{'input-error': errors.email}" required>
                            <div x-show="errors.email" class="text-error text-sm mt-1" x-text="errors.email ? errors.email[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">Employee Code</label>
                            <input type="text" x-model="newUser.employee_code" class="input input-bordered" :class="{'input-error': errors.employee_code}">
                             <div x-show="errors.employee_code" class="text-error text-sm mt-1" x-text="errors.employee_code ? errors.employee_code[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">Phone Number</label>
                            <input type="text" x-model="newUser.phone_number" class="input input-bordered" :class="{'input-error': errors.phone_number}">
                             <div x-show="errors.phone_number" class="text-error text-sm mt-1" x-text="errors.phone_number ? errors.phone_number[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">Password (leave blank if not changing)</label>
                            <input type="password" x-model="newUser.password" class="input input-bordered" :class="{'input-error': errors.password}">
                             <div x-show="errors.password" class="text-error text-sm mt-1" x-text="errors.password ? errors.password[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">Department</label>
                            <select x-model="newUser.department_id" class="select select-bordered" :class="{'input-error': errors.department_id}">
                                <option value="">Select Department</option>
                                <template x-for="department in departments" :key="department.id">
                                    <option :value="department.id" x-text="department.name"></option>
                                </template>
                            </select>
                             <div x-show="errors.department_id" class="text-error text-sm mt-1" x-text="errors.department_id ? errors.department_id[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">Manager</label>
                            <select x-model="newUser.manager_id" class="select select-bordered" :class="{'input-error': errors.manager_id}">
                                <option value="">Select Manager</option>
                                <template x-for="manager in users" :key="manager.id">
                                    <option :value="manager.id" x-text="manager.name"></option>
                                </template>
                            </select>
                             <div x-show="errors.manager_id" class="text-error text-sm mt-1" x-text="errors.manager_id ? errors.manager_id[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">Status</label>
                            <select x-model="newUser.status" class="select select-bordered" :class="{'input-error': errors.status}">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                             <div x-show="errors.status" class="text-error text-sm mt-1" x-text="errors.status ? errors.status[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">Hire Date</label>
                            <input type="date" x-model="newUser.hire_date" class="input input-bordered" :class="{'input-error': errors.hire_date}">
                             <div x-show="errors.hire_date" class="text-error text-sm mt-1" x-text="errors.hire_date ? errors.hire_date[0] : ''"></div>
                        </div>
                        <div class="form-control">
                            <label class="label">Roles</label>
                            <select x-model="newUser.roles" class="select select-bordered" :class="{'input-error': errors.roles}" multiple>
                                <template x-for="role in roles" :key="role.id">
                                    <option :value="role.name" x-text="role.name"></option>
                                </template>
                            </select>
                             <div x-show="errors.roles" class="text-error text-sm mt-1" x-text="errors.roles ? errors.roles[0] : ''"></div>
                        </div>
                    </div>
                    <div class="modal-action mt-4">
                        <button type="button" class="btn" @click="user_modal.close(); errors = {}">Cancel</button>
                        <button type="submit" class="btn btn-primary" x-text="isEdit ? 'Update' : 'Create'"></button>
                    </div>
                </form>
            </div>
        </dialog>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Hire Date</th>
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
                                        <div class="mask mask-squircle w-12 h-12">
                                            <img :src="user.profile_photo_url" :alt="user.name" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold" x-text="user.name"></div>
                                        <div class="text-sm opacity-50" x-text="user.email"></div>
                                    </div>
                                </div>
                            </td>
                            <td x-text="user.department ? user.department.name : 'N/A'"></td>
                            <td x-text="user.hire_date ? new Date(user.hire_date).toLocaleDateString() : 'N/A'"></td>
                            <td>
                                <span class="badge" :class="user.status === 'active' ? 'badge-success' : 'badge-error'" x-text="user.status"></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" @click="openEditModal(user)">Edit</button>
                                <button class="btn btn-sm btn-error" @click="confirmDelete(user.id)">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex justify-between items-center mt-4">
            <div class="flex items-center gap-2">
                <select x-model="perPage" @change="fetchUsers" class="select select-bordered">
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
                document.getElementById('user_modal').showModal();
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
                document.getElementById('user_modal').showModal();
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
                    document.getElementById('user_modal').close();
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
                    document.getElementById('user_modal').close();
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
