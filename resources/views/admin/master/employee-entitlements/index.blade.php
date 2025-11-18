@extends('template.admin')

@section('title', 'Admin | Manage Employee Entitlements')

@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8" x-data="employeeEntitlementsTable()" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Employee Entitlements</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Entitlement</button>
        </div>

        <!-- Add/Edit Entitlement Modal -->
        <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center">
            <div class="modal-box">
                <h3 class="font-bold text-lg" x-text="isEdit ? 'Edit Entitlement' : 'Add New Entitlement'"></h3>
                <form @submit.prevent="isEdit ? updateEntitlement() : addEntitlement()">
                    <div class="form-control">
                        <label class="label">Employee</label>
                        <select x-model="newEntitlement.user_id" class="select select-bordered" required>
                            <option value="">Select Employee</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label">Leave Type</label>
                        <select x-model="newEntitlement.leave_type_id" class="select select-bordered" required>
                            <option value="">Select Leave Type</option>
                            <template x-for="leaveType in leaveTypes" :key="leaveType.id">
                                <option :value="leaveType.id" x-text="leaveType.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label">Days</label>
                        <input type="number" x-model="newEntitlement.days" class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label">Year</label>
                        <input type="number" x-model="newEntitlement.year" class="input input-bordered" required>
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
                <p>Are you sure you want to delete this entitlement?</p>
                <div class="modal-action">
                    <button class="btn btn-error" @click="deleteEntitlement()">Delete</button>
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
                        <th>Employee Name</th>
                        <th>Leave Type</th>
                        <th>Days</th>
                        <th>Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="5" class="text-center py-10">
                                <span class="loading loading-spinner loading-lg"></span>
                            </td>
                        </tr>
                    </template>
                    <template x-for="entitlement in filteredEntitlements" :key="entitlement.id">
                        <tr>
                            <td x-text="entitlement.user.name"></td>
                            <td x-text="entitlement.leave_type.name"></td>
                            <td x-text="entitlement.days"></td>
                            <td x-text="entitlement.year"></td>
                            <td>
                                <button class="btn btn-sm btn-info" @click="openEditModal(entitlement)">Edit</button>
                                <button class="btn btn-sm btn-error" @click="openDeleteModal(entitlement.id)">Delete</button>
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
    function employeeEntitlementsTable() {
        return {
            entitlements: [],
            users: [],
            leaveTypes: [],
            loading: true,
            search: '',
            perPage: 10,
            showModal: false,
            showDeleteModal: false,
            isEdit: false,
            newEntitlement: {
                id: null,
                user_id: '',
                leave_type_id: '',
                days: '',
                year: new Date().getFullYear()
            },
            entitlementToDelete: null,
            
            init() {
                this.fetchEntitlements();
                this.fetchUsers();
                this.fetchLeaveTypes();
            },

            async fetchEntitlements() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) {
                        window.location.href = '/login';
                        return;
                    }

                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/employee-entitlements', {
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
                    this.entitlements = data.data;
                } catch (error) {
                    console.error('Error fetching entitlements:', error);
                } finally {
                    this.loading = false;
                }
            },

            async fetchUsers() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/users', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    this.users = data.data;
                } catch (error) {
                    console.error('Error fetching users:', error);
                }
            },

            async fetchLeaveTypes() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/leave-types', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    this.leaveTypes = data.data;
                } catch (error) {
                    console.error('Error fetching leave types:', error);
                }
            },

            openAddModal() {
                this.isEdit = false;
                this.newEntitlement = { id: null, user_id: '', leave_type_id: '', days: '', year: new Date().getFullYear() };
                this.showModal = true;
            },

            openEditModal(entitlement) {
                this.isEdit = true;
                this.newEntitlement = { ...entitlement };
                this.showModal = true;
            },

            openDeleteModal(entitlementId) {
                this.entitlementToDelete = entitlementId;
                this.showDeleteModal = true;
            },

            async addEntitlement() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/employee-entitlements', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newEntitlement)
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to add entitlement.');
                    }

                    this.fetchEntitlements();
                    this.showModal = false;
                } catch (error) {
                    console.error('Error adding entitlement:', error);
                    alert(error.message);
                }
            },

            async updateEntitlement() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`http://leaveapp.redirect.my.id/api/admin/master/employee-entitlements/${this.newEntitlement.id}`, {
                        method: 'PUT',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newEntitlement)
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to update entitlement.');
                    }

                    this.fetchEntitlements();
                    this.showModal = false;
                } catch (error) {
                    console.error('Error updating entitlement:', error);
                    alert(error.message);
                }
            },

            async deleteEntitlement() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`http://leaveapp.redirect.my.id/api/admin/master/employee-entitlements/${this.entitlementToDelete}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to delete entitlement.');
                    }

                    this.fetchEntitlements();
                    this.showDeleteModal = false;
                } catch (error) {
                    console.error('Error deleting entitlement:', error);
                    alert(error.message);
                }
            },

            get filteredEntitlements() {
                if (this.search === '') {
                    return this.entitlements.slice(0, this.perPage);
                }
                return this.entitlements.filter(entitlement => {
                    return entitlement.user.name.toLowerCase().includes(this.search.toLowerCase());
                }).slice(0, this.perPage);
            }
        }
    }
</script>
@endpush