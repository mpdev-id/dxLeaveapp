@extends('template.admin')
@section('title', 'Admin | Manage Departments')
@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8" x-data="departmentsTable('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Departments</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Department</button>
        </div>
        <!-- Add/Edit Department Modal -->
        <dialog id="department_modal" class="modal">
            <div class="modal-box">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" @click="errors = {}">✕</button>
                </form>
                <h3 class="font-bold text-lg" x-text="isEdit ? 'Edit Department' : 'Add New Department'"></h3>
                <form @submit.prevent="isEdit ? updateDepartment() : addDepartment()">
                    <div class="form-control">
                        <label class="label">Name</label>
                        <input type="text" x-model="newDepartment.name" class="input input-bordered" :class="{'input-error': errors.name}" required>
                        <div x-show="errors.name" class="text-error text-sm mt-1" x-text="errors.name ? errors.name[0] : ''"></div>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn" @click="department_modal.close(); errors = {}">Cancel</button>
                        <button type="submit" class="btn btn-primary" x-text="isEdit ? 'Update' : 'Create'"></button>
                    </div>
                </form>
            </div>
        </dialog>
        
@push('scripts')
<script>
    function departmentsTable(baseApiUrl) {
        return {
            departments: [],
            loading: true,
            search: '',
            perPage: 10,
            isEdit: false,
            newDepartment: { id: null, name: '' },
            errors: {},

            init() {
                this.fetchDepartments();
            },

            async fetchDepartments() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) { window.location.href = '/login'; return; }
                    const response = await fetch(`${baseApiUrl}/admin/master/departments`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    if (response.status === 401) { localStorage.removeItem('authToken'); window.location.href = '/login'; return; }
                    const data = await response.json();
                    this.departments = data.data.data;
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
                this.newDepartment = { id: null, name: '' };
                this.errors = {};
                department_modal.showModal();
            },

            openEditModal(department) {
                this.isEdit = true;
                this.newDepartment = { ...department };
                this.errors = {};
                department_modal.showModal();
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
                    department_modal.close();
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
                    department_modal.close();
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
            },

            get filteredDepartments() {
                if (this.search === '') {
                    return this.departments.slice(0, this.perPage);
                }
                return this.departments.filter(department => {
                    return department.name.toLowerCase().includes(this.search.toLowerCase());
                }).slice(0, this.perPage);
            }
        }
    }
</script>
@endpush
