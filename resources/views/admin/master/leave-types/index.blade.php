@extends('template.admin')

@section('title', 'Admin | Manage Leave Types')

@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8" x-data="leaveTypesTable()" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Leave Types</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Leave Type</button>
        </div>

        <!-- Add/Edit Leave Type Modal -->
        <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center">
            <div class="modal-box">
                <h3 class="font-bold text-lg" x-text="isEdit ? 'Edit Leave Type' : 'Add New Leave Type'"></h3>
                <form @submit.prevent="isEdit ? updateLeaveType() : addLeaveType()">
                    <div class="form-control">
                        <label class="label">Name</label>
                        <input type="text" x-model="newLeaveType.name" class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label">Days</label>
                        <input type="number" x-model="newLeaveType.days" class="input input-bordered" required>
                    </div>
                    <div class="modal-action">
                        <button type="submit" class="btn btn-primary" x-text="isEdit ? 'Update' : 'Create'"></button>
                        <button type="button" class="btn" @click="showModal = false">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Confirm Deletion</h3>
                <p>Are you sure you want to delete this leave type?</p>
                <div class="modal-action">
                    <button class="btn btn-error" @click="deleteLeaveType()">Delete</button>
                    <button class="btn" @click="showDeleteModal = false">Cancel</button>
                </div>
            </div>
        </div>

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
                        <th>Days</th>
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
                    <template x-for="leaveType in filteredLeaveTypes" :key="leaveType.id">
                        <tr>
                            <td x-text="leaveType.name"></td>
                            <td x-text="leaveType.days"></td>
                            <td>
                                <button class="btn btn-sm btn-info" @click="openEditModal(leaveType)">Edit</button>
                                <button class="btn btn-sm btn-error" @click="openDeleteModal(leaveType.id)">Delete</button>
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
    function leaveTypesTable() {
        return {
            leaveTypes: [],
            loading: true,
            search: '',
            perPage: 10,
            showModal: false,
            showDeleteModal: false,
            isEdit: false,
            newLeaveType: {
                id: null,
                name: '',
                days: ''
            },
            leaveTypeToDelete: null,
            
            init() {
                this.fetchLeaveTypes();
            },

            async fetchLeaveTypes() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) {
                        window.location.href = '/login';
                        return;
                    }

                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/leave-types', {
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
                    this.leaveTypes = data.data;
                } catch (error) {
                    console.error('Error fetching leave types:', error);
                } finally {
                    this.loading = false;
                }
            },

            openAddModal() {
                this.isEdit = false;
                this.newLeaveType = { id: null, name: '', days: '' };
                this.showModal = true;
            },

            openEditModal(leaveType) {
                this.isEdit = true;
                this.newLeaveType = { ...leaveType };
                this.showModal = true;
            },

            openDeleteModal(leaveTypeId) {
                this.leaveTypeToDelete = leaveTypeId;
                this.showDeleteModal = true;
            },

            async addLeaveType() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/leave-types', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newLeaveType)
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to add leave type.');
                    }

                    this.fetchLeaveTypes();
                    this.showModal = false;
                } catch (error) {
                    console.error('Error adding leave type:', error);
                    alert(error.message);
                }
            },

            async updateLeaveType() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`http://leaveapp.redirect.my.id/api/admin/master/leave-types/${this.newLeaveType.id}`, {
                        method: 'PUT',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newLeaveType)
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to update leave type.');
                    }

                    this.fetchLeaveTypes();
                    this.showModal = false;
                } catch (error) {
                    console.error('Error updating leave type:', error);
                    alert(error.message);
                }
            },

            async deleteLeaveType() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`http://leaveapp.redirect.my.id/api/admin/master/leave-types/${this.leaveTypeToDelete}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to delete leave type.');
                    }

                    this.fetchLeaveTypes();
                    this.showDeleteModal = false;
                } catch (error) {
                    console.error('Error deleting leave type:', error);
                    alert(error.message);
                }
            },

            get filteredLeaveTypes() {
                if (this.search === '') {
                    return this.leaveTypes.slice(0, this.perPage);
                }
                return this.leaveTypes.filter(leaveType => {
                    return leaveType.name.toLowerCase().includes(this.search.toLowerCase());
                }).slice(0, this.perPage);
            }
        }
    }
</script>
@endpush