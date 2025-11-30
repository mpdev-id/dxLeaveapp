@extends('template.admin')
@section('title', 'Admin | Manage Departments')
@section('content')
<div class="container mx-auto px-4 sm:px-8 bg-base-100 border border-base-200 rounded-lg">
    <div class="py-8" x-data="departmentsTable('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Departments</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Department</button>
        </div>
        <!-- Add/Edit Department Modal -->
        <dialog id="department_modal" class="modal">
            <div class="modal-box">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="hideModal('department_modal')">✕</button>
                <h3 class="font-bold text-lg mb-4" x-text="isEdit ? 'Edit Department' : 'Add New Department'"></h3>
                <form @submit.prevent="isEdit ? updateDepartment() : addDepartment()" class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                                Name
                            </span>
                        </label>
                        <input type="text" x-model="newDepartment.name" placeholder="e.g., Human Resources" class="input input-bordered w-full" :class="{'input-error': errors.name}" required>
                        <div x-show="errors.name" class="text-error text-sm mt-1" x-text="errors.name ? errors.name[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Head of Department</span>
                        </label>
                        <select x-model="newDepartment.head_id" class="select select-bordered w-full" :class="{'select-error': errors.head_id}">
                            <option value="">Select Head</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.head_id" class="text-error text-sm mt-1" x-text="errors.head_id ? errors.head_id[0] : ''"></div>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn btn-ghost" onclick="hideModal('department_modal')">Cancel</button>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="2" class="text-center py-10"><span class="loading loading-spinner loading-lg"></span></td>
                        </tr>
                    </template>
                    <template x-if="!loading && departments.length === 0">
                        <tr>
                            <td colspan="2" class="text-center py-4">No departments found.</td>
                        </tr>
                    </template>
                    <template x-for="department in departments" :key="department.id">
                        <tr>
                            <td x-text="department.name"></td>
                            <td>
                                <button @click="openEditModal(department)" class="btn btn-sm btn-warning">Edit</button>
                                <button @click="confirmDelete(department.id)" class="btn btn-sm btn-error">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex justify-between items-center mt-4">
            <div class="flex items-center gap-2">
                <select x-model="perPage" @change="fetchDepartments" class="select select-bordered">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
                <input type="text" x-model.debounce.500ms="search" @input="fetchDepartments" placeholder="Search..." class="input input-bordered">
            </div>
            <div class="join">
                <button @click="currentPage > 1 && (currentPage--, fetchDepartments())" :disabled="currentPage === 1" class="join-item btn">«</button>
                <button class="join-item btn" x-text="`Page ${currentPage}`"></button>
                <button @click="currentPage < totalPages && (currentPage++, fetchDepartments())" :disabled="currentPage === totalPages" class="join-item btn">»</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function departmentsTable(baseApiUrl) {
        return {
            departments: [],
            users: [],
            loading: true,
            search: '',
            perPage: 10,
            currentPage: 1,
            totalPages: 1,
            isEdit: false,
            newDepartment: { id: null, name: '', head_id: '' },
            errors: {},

            init() {
                this.fetchDepartments();
                this.fetchUsers();
            },

            async fetchUsers() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/users?all=true`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    this.users = data.data;
                } catch (error) {
                    console.error('Error fetching users:', error);
                }
            },

            async fetchDepartments() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) { window.location.href = '/login'; return; }
                    const response = await fetch(`${baseApiUrl}/admin/master/departments?page=${this.currentPage}&per_page=${this.perPage}&search=${this.search}`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    if (response.status === 401) { localStorage.removeItem('authToken'); window.location.href = '/login'; return; }
                    const data = await response.json();
                    this.departments = data.data.data;
                    this.totalPages = data.data.last_page;
                } catch (error) {
                    console.error('Error fetching departments:', error);
                    this.showToast('Failed to fetch departments.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            showToast(message, icon = 'success') {
                Swal.fire({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, icon: icon, title: message
                });
            },

            openAddModal() {
                this.isEdit = false;
                this.newDepartment = { id: null, name: '', head_id: '' };
                this.errors = {};
                showModal('department_modal');
            },

            openEditModal(department) {
                this.isEdit = true;
                this.newDepartment = { 
                    id: department.id, 
                    name: department.name,
                    head_id: department.head_id
                };
                this.errors = {};
                showModal('department_modal');
            },

            async addDepartment() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/departments`, {
                        method: 'POST',
                        headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(this.newDepartment)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to add department.', 'error');
                        }
                        return;
                    }
                    this.fetchDepartments();
                    this.showToast(data.meta.message);
                    hideModal('department_modal');
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error adding department:', error);
                }
            },

            async updateDepartment() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/departments/${this.newDepartment.id}`, {
                        method: 'PUT',
                        headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(this.newDepartment)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to update department.', 'error');
                        }
                        return;
                    }
                    this.fetchDepartments();
                    this.showToast(data.meta.message);
                    hideModal('department_modal');
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error updating department:', error);
                }
            },

            confirmDelete(departmentId) {
                Swal.fire({
                    title: 'Are you sure?', text: "You won't be able to revert this!", icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) { this.deleteDepartment(departmentId); }
                });
            },

            async deleteDepartment(departmentId) {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/departments/${departmentId}`, {
                        method: 'DELETE',
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        this.showToast(data.meta.message || 'Failed to delete department.', 'error');
                        return;
                    }
                    this.fetchDepartments();
                    this.showToast(data.meta.message);
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error deleting department:', error);
                }
            }
        }
    }
</script>
@endpush
