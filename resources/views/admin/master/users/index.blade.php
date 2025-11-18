@extends('template.admin')

@section('title', 'Admin | Manage Users')

@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8" x-data="usersTable()" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Users</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add User</button>
        </div>
        
        <!-- Add/Edit User Modal -->
        <dialog id="user_modal" class="modal">
            <div class="modal-box">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                </form>
                <h3 class="font-bold text-lg" x-text="isEdit ? 'Edit User' : 'Add New User'"></h3>
                <form @submit.prevent="isEdit ? updateUser() : addUser()">
                    <div class="form-control">
                        <label class="label">Name</label>
                        <input type="text" x-model="newUser.name" class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label">Email</label>
                        <input type="email" x-model="newUser.email" class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label">Password (leave blank if not changing)</label>
                        <input type="password" x-model="newUser.password" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label">Department</label>
                        <select x-model="newUser.department_id" class="select select-bordered" required>
                            <option value="">Select Department</option>
                            <template x-for="department in departments" :key="department.id">
                                <option :value="department.id" x-text="department.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="modal-action">
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
                        <th>Joined</th>
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
                            <td x-text="user.department.name"></td>
                            <td x-text="new Date(user.hire_date).toLocaleDateString()"></td>
                            <td>
                                <span class="badge badge-success">Active</span>
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
    function usersTable() {
        return {
            users: [],
            departments: [],
            loading: true,
            search: '',
            perPage: 10,
            isEdit: false,
            newUser: {
                id: null,
                name: '',
                email: '',
                password: '',
                department_id: ''
            },
            userToDelete: null,
            
            init() {
                this.fetchUsers();
                this.fetchDepartments();
            },

            async fetchUsers() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) {
                        window.location.href = '/login';
                        return;
                    }

                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/users', {
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
                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/departments', {
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

            openAddModal() {
                this.isEdit = false;
                this.newUser = { id: null, name: '', email: '', password: '', department_id: '' };
                user_modal.showModal();
            },

            openEditModal(user) {
                this.isEdit = true;
                this.newUser = { ...user, department_id: user.department.id };
                user_modal.showModal();
            },

            openDeleteModal(userId) {
                this.userToDelete = userId;
                delete_modal.showModal();
            },

            async addUser() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/users', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newUser)
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to add user.');
                    }

                    this.fetchUsers();
                    user_modal.close();
                } catch (error) {
                    console.error('Error adding user:', error);
                    alert(error.message);
                }
            },

            async updateUser() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`http://leaveapp.redirect.my.id/api/admin/master/users/${this.newUser.id}`, {
                        method: 'PUT',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newUser)
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to update user.');
                    }

                    this.fetchUsers();
                    user_modal.close();
                } catch (error) {
                    console.error('Error updating user:', error);
                    alert(error.message);
                }
            },

            async deleteUser() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`http://leaveapp.redirect.my.id/api/admin/master/users/${this.userToDelete}`, {
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
                    delete_modal.close();
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