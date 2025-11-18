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
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                </form>
                <h3 class="font-bold text-lg" x-text="isEdit ? 'Edit User' : 'Add New User'"></h3>
                <form @submit.prevent="isEdit ? updateUser() : addUser()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">Name</label>
                            <input type="text" x-model="newUser.name" class="input input-bordered" required>
                        </div>
                        <div class="form-control">
                            <label class="label">Email</label>
                            <input type="email" x-model="newUser.email" class="input input-bordered" required>
                        </div>
                        <div class="form-control">
                            <label class="label">Employee Code</label>
                            <input type="text" x-model="newUser.employee_code" class="input input-bordered">
                        </div>
                        <div class="form-control">
                            <label class="label">Phone Number</label>
                            <input type="text" x-model="newUser.phone_number" class="input input-bordered">
                        </div>
                        <div class="form-control">
                            <label class="label">Password (leave blank if not changing)</label>
                            <input type="password" x-model="newUser.password" class="input input-bordered">
                        </div>
                        <div class="form-control">
                            <label class="label">Department</label>
                            <select x-model="newUser.department_id" class="select select-bordered">
                                <option value="">Select Department</option>
                                <template x-for="department in departments" :key="department.id">
                                    <option :value="department.id" x-text="department.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label">Manager</label>
                            <select x-model="newUser.manager_id" class="select select-bordered">
                                <option value="">Select Manager</option>
                                <template x-for="manager in users" :key="manager.id">
                                    <option :value="manager.id" x-text="manager.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label">Status</label>
                            <select x-model="newUser.status" class="select select-bordered">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label">Hire Date</label>
                            <input type="date" x-model="newUser.hire_date" class="input input-bordered">
                        </div>
                        <div class="form-control">
                            <label class="label">Roles</label>
                            <select x-model="newUser.roles" class="select select-bordered" multiple>
                                <template x-for="role in roles" :key="role.id">
                                    <option :value="role.name" x-text="role.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div class="modal-action mt-4">
                        <button type="submit" class="btn btn-primary" x-text="isEdit ? 'Update' : 'Create'"></button>
                        <form method="dialog">
                            <button class="btn">Cancel</button>
                        </form>
                    </div>
                </form>
            </div>
        </dialog>

        <!-- Delete Confirmation Modal -->
        <dialog id="delete_modal" class="modal">
            <div class="modal-box">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                </form>
                <h3 class="font-bold text-lg">Confirm Deletion</h3>
                <p>Are you sure you want to delete this user?</p>
                <div class="modal-action">
                    <button class="btn btn-error" @click="deleteUser()">Delete</button>
                    <form method="dialog">
                        <button class="btn">Cancel</button>
                    </form>
                </div>
            </div>
        </dialog>

        <div class="my-4 flex justify-between items-center">
            <div class="flex items-center">
                <select x-model="perPage" class="select select-bordered w-full max-w-xs">
                    <option>5</option>
                    <option>10</option>
                    <option>20</option>
                </select>
            </div>
            <div class="relative">
                <input type="text" placeholder="Search" x-model="search" class="input input-bordered w-full max-w-xs" />
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Hire Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <span class="loading loading-spinner loading-lg"></span>
                            </td>
                        </tr>
                    </template>
                    <template x-for="user in filteredUsers" :key="user.id">
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
                                    </div>
                                </div>
                            </td>
                            <td x-text="user.email"></td>
                            <td x-text="user.department ? user.department.name : 'N/A'"></td>
                            <td x-text="user.hire_date ? new Date(user.hire_date).toLocaleDateString() : 'N/A'"></td>
                            <td>
                                <span class="badge" :class="user.status === 'active' ? 'badge-success' : 'badge-error'" x-text="user.status"></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" @click="openEditModal(user)">Edit</button>
                                <button class="btn btn-sm btn-error" @click="openDeleteModal(user.id)">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
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
            userToDelete: null,
            
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

                    const response = await fetch(`${baseApiUrl}/admin/master/users`, {
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
                    this.users = data.data;
                } catch (error) {
                    console.error('Error fetching users:', error);
                } finally {
                    this.loading = false;
                }
            },

            async fetchDepartments() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/departments`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    this.departments = data.data.data;
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

            openAddModal() {
                this.isEdit = false;
                this.newUser = {
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
                };
                document.getElementById('user_modal').showModal();
            },

            openEditModal(user) {
                this.isEdit = true;
                this.newUser = {
                    id: user.id,
                    name: user.name,
                    employee_code: user.employee_code,
                    email: user.email,
                    phone_number: user.phone_number,
                    password: '',
                    department_id: user.department_id ? Number(user.department_id) : '',
                    manager_id: user.manager_id ? Number(user.manager_id) : '',
                    status: user.status || 'active',
                    hire_date: user.hire_date || '',
                    roles: user.roles ? user.roles.map(role => role.name) : []
                };
                document.getElementById('user_modal').showModal();
            },

            openDeleteModal(userId) {
                this.userToDelete = userId;
                document.getElementById('delete_modal').showModal();
            },

            async addUser() {
                try {
                    const token = localStorage.getItem('authToken');
                    const userData = { ...this.newUser };

                    for (const key in userData) {
                        if (userData[key] === '') {
                            userData[key] = null;
                        }
                    }

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
                    if (!response.ok || data.meta.status !== 'success') {
                        let errorMessage = data.meta.message || 'Failed to add user.';
                        if (data.data && data.data.errors) {
                            const errors = Object.values(data.data.errors).flat().join('\n');
                            errorMessage += '\n' + errors;
                        }
                        throw new Error(errorMessage);
                    }

                    this.fetchUsers();
                    document.getElementById('user_modal').close();
                } catch (error) {
                    console.error('Error adding user:', error);
                    alert(error.message);
                }
            },

            async updateUser() {
                try {
                    const token = localStorage.getItem('authToken');
                    const userData = { ...this.newUser };

                    if (userData.password === '' || userData.password === null) {
                        delete userData.password;
                    }

                    for (const key in userData) {
                        if (userData[key] === '') {
                            userData[key] = null;
                        }
                    }

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
                    if (!response.ok || data.meta.status !== 'success') {
                        let errorMessage = data.meta.message || 'Failed to update user.';
                        if (data.data && data.data.errors) {
                            const errors = Object.values(data.data.errors).flat().join('\n');
                            errorMessage += '\n' + errors;
                        }
                        throw new Error(errorMessage);
                    }

                    this.fetchUsers();
                    document.getElementById('user_modal').close();
                } catch (error) {
                    console.error('Error updating user:', error);
                    alert(error.message);
                }
            },

            async deleteUser() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/users/${this.userToDelete}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Failed to delete user.');
                    }

                    this.fetchUsers();
                    document.getElementById('delete_modal').close();
                } catch (error) {
                    console.error('Error deleting user:', error);
                    alert(error.message);
                }
            },

            get filteredUsers() {
                if (this.search === '') {
                    return this.users.slice(0, this.perPage);
                }
                return this.users.filter(user => {
                    return user.name.toLowerCase().includes(this.search.toLowerCase()) ||
                           user.email.toLowerCase().includes(this.search.toLowerCase());
                }).slice(0, this.perPage);
            }
        }
    }
</script>
@endpush