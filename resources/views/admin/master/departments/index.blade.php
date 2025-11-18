@extends('template.admin')

@section('title', 'Admin | Manage Departments')

@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8" x-data="departmentsTable()" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Departments</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Department</button>
        </div>

        <!-- Add/Edit Department Modal -->
        <dialog id="department_modal" class="modal">
            <div class="modal-box">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                </form>
                <h3 class="font-bold text-lg" x-text="isEdit ? 'Edit Department' : 'Add New Department'"></h3>
                <form @submit.prevent="isEdit ? updateDepartment() : addDepartment()">
                    <div class="form-control">
                        <label class="label">Name</label>
                        <input type="text" x-model="newDepartment.name" class="input input-bordered" required>
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
                <p>Are you sure you want to delete this department?</p>
                <div class="modal-action">
                    <button class="btn btn-error" @click="deleteDepartment()">Delete</button>
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
                        <th>Total Employees</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="3" class="text-center py-10">
                                <span class="loading loading-spinner loading-lg"></span>
                            </td>
                        </tr>
                    </template>
                    <template x-for="department in filteredDepartments" :key="department.id">
                        
                        <tr>
                            <td x-text="department.name"></td>
                            <td x-text="department.users_count"></td>
                            <td>
                                <button class="btn btn-sm btn-info" @click="openEditModal(department)">Edit</button>
                                <button class="btn btn-sm btn-error" @click="openDeleteModal(department.id)">Delete</button>
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
    function departmentsTable() {
        return {
            departments: [],
            loading: true,
            search: '',
            perPage: 10,
            isEdit: false,
            newDepartment: {
                id: null,
                name: ''
            },
            departmentToDelete: null,
            
            init() {
                this.fetchDepartments();
            },

            async fetchDepartments() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) {
                        window.location.href = '/login';
                        return;
                    }

                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/departments', {
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
                    this.departments = data.data.data;
                } catch (error) {
                    console.error('Error fetching departments:', error);
                } finally {
                    this.loading = false;
                }
            },

            openAddModal() {
                this.isEdit = false;
                this.newDepartment = { id: null, name: '' };
                department_modal.showModal();
            },

            openEditModal(department) {
                this.isEdit = true;
                this.newDepartment = { ...department };
                department_modal.showModal();
            },

            openDeleteModal(departmentId) {
                this.departmentToDelete = departmentId;
                delete_modal.showModal();
            },

            async addDepartment() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/departments', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newDepartment)
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to add department.');
                    }

                    this.fetchDepartments();
                    department_modal.close();
                } catch (error) {
                    console.error('Error adding department:', error);
                    alert(error.message);
                }
            },

            async updateDepartment() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`http://leaveapp.redirect.my.id/api/admin/master/departments/${this.newDepartment.id}`, {
                        method: 'PUT',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newDepartment)
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to update department.');
                    }

                    this.fetchDepartments();
                    department_modal.close();
                } catch (error) {
                    console.error('Error updating department:', error);
                    alert(error.message);
                }
            },

            async deleteDepartment() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`http://leaveapp.redirect.my.id/api/admin/master/departments/${this.departmentToDelete}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to delete department.');
                    }

                    this.fetchDepartments();
                    delete_modal.close();
                } catch (error) {
                    console.error('Error deleting department:', error);
                    alert(error.message);
                }
            },

            get filteredDepartments() {
                if (this.search
                 === '') {
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