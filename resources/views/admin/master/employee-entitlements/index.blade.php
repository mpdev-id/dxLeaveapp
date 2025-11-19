@extends('template.admin')

@section('title', 'Admin | Manage Employee Entitlements')

@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8" x-data="employeeEntitlementsTable('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Employee Entitlements</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Entitlement</button>
        </div>

        <!-- Add/Edit Entitlement Modal -->
        <dialog id="entitlement_modal" class="modal">
            <div class="modal-box">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                </form>
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
                        <label class="label">initial_balance</label>
                        <input type="number" x-model="newEntitlement.initial_balance" class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label">Year</label>
                        <input type="number" x-model="newEntitlement.year" class="input input-bordered" required>
                    </div>
                    <div class="modal-action">
                        <form method="dialog">
                            <button class="btn">Cancel</button>
                        </form>
                        <button type="submit" class="btn btn-primary" x-text="isEdit ? 'Update' : 'Create'"></button>
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
                <p>Are you sure you want to delete this entitlement?</p>
                <div class="modal-action">
                    <button class="btn btn-error" @click="deleteEntitlement()">Delete</button>
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
                        <th>Employee Name</th>
                        <th>Leave Type</th>
                        <th>initial_balance</th>
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
                            <td x-text="entitlement.initial_balance"></td>
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
    function employeeEntitlementsTable(baseApiUrl) {
        return {
            entitlements: [],
            users: [],
            leaveTypes: [],
            loading: true,
            search: '',
            perPage: 10,
            isEdit: true,
            newEntitlement: {
                
                user_id: '',
                leave_type_id: '',
                initial_balance: '',
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

                    const response = await fetch(`${baseApiUrl}/admin/master/employee-entitlements`, {
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
                    this.entitlements = data.data.data;
                } catch (error) {
                    console.error('Error fetching entitlements:', error);
                } finally {
                    this.loading = false;
                }
            },

            async fetchUsers() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/users`, {
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
                    const response = await fetch(`${baseApiUrl}/admin/master/leave-types`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    this.leaveTypes = data.data.data;
                } catch (error) {
                    console.error('Error fetching leave types:', error);
                }
            },

            openAddModal() {
                this.isEdit = false;
                this.newEntitlement = { id: null, user_id: '', leave_type_id: '', initial_balance: '', year: new Date().getFullYear() };
                entitlement_modal.showModal();
            },

            openEditModal(entitlement) {
                this.isEdit = true;
                this.newEntitlement = { 
                    id: entitlement.id,
                    user_id: entitlement.user.id,
                    leave_type_id: entitlement.leave_type.id,
                    initial_balance: entitlement.initial_balance,
                    year: entitlement.year
                };
                entitlement_modal.showModal();
            },

            openDeleteModal(entitlementId) {
                this.entitlementToDelete = entitlementId;
                delete_modal.showModal();
            },

            async addEntitlement() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/employee-entitlements`, {
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
                    entitlement_modal.close();
                } catch (error) {
                    console.error('Error adding entitlement:', error);
                    alert(error.message);
                }
            },

            async updateEntitlement() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/employee-entitlements/${this.newEntitlement.id}`, {
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
                    entitlement_modal.close();
                } catch (error) {
                    console.error('Error updating entitlement:', error);
                    alert(error.message);
                }
            },

            async deleteEntitlement() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/employee-entitlements/${this.entitlementToDelete}`, {
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
                    delete_modal.close();
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