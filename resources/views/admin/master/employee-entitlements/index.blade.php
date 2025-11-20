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
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" @click="errors = {}">✕</button>
                </form>
                <h3 class="font-bold text-lg" x-text="isEdit ? 'Edit Entitlement' : 'Add New Entitlement'"></h3>
                <form @submit.prevent="isEdit ? updateEntitlement() : addEntitlement()">
                    <div class="form-control">
                        <label class="label">Employee</label>
                        <select x-model="newEntitlement.user_id" class="select select-bordered" :class="{'input-error': errors.user_id}" required>
                            <option value="">Select Employee</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.user_id" class="text-error text-sm mt-1" x-text="errors.user_id ? errors.user_id[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">Leave Type</label>
                        <select x-model="newEntitlement.leave_type_id" class="select select-bordered" :class="{'input-error': errors.leave_type_id}" required>
                            <option value="">Select Leave Type</option>
                            <template x-for="leaveType in leaveTypes" :key="leaveType.id">
                                <option :value="leaveType.id" x-text="leaveType.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.leave_type_id" class="text-error text-sm mt-1" x-text="errors.leave_type_id ? errors.leave_type_id[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">Balance</label>
                        <input type="number" x-model="newEntitlement.initial_balance" class="input input-bordered" :class="{'input-error': errors.initial_balance}" required>
                        <div x-show="errors.initial_balance" class="text-error text-sm mt-1" x-text="errors.initial_balance ? errors.initial_balance[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">Year</label>
                        <input type="number" x-model="newEntitlement.year" class="input input-bordered" :class="{'input-error': errors.year}" required>
                        <div x-show="errors.year" class="text-error text-sm mt-1" x-text="errors.year ? errors.year[0] : ''"></div>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn" @click="entitlement_modal.close(); errors = {}">Cancel</button>
                        <button type="submit" class="btn btn-primary" x-text="isEdit ? 'Update' : 'Create'"></button>
                    </div>
                </form>
            </div>
        </dialog>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Leave Type</th>
                        <th>Balance</th>
                        <th>Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="5" class="text-center py-10"><span class="loading loading-spinner loading-lg"></span></td>
                        </tr>
                    </template>
                    <template x-if="!loading && entitlements.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-4">No entitlements found.</td>
                        </tr>
                    </template>
                    <template x-for="entitlement in entitlements" :key="entitlement.id">
                        <tr>
                            <td x-text="entitlement.user.name"></td>
                            <td x-text="entitlement.leave_type.name"></td>
                            <td x-text="entitlement.initial_balance"></td>
                            <td x-text="entitlement.year"></td>
                            <td>
                                <button class="btn btn-sm btn-info" @click="openEditModal(entitlement)">Edit</button>
                                <button class="btn btn-sm btn-error" @click="confirmDelete(entitlement.id)">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex justify-between items-center mt-4">
            <div class="flex items-center gap-2">
                <select x-model="perPage" @change="fetchEntitlements" class="select select-bordered">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
                <input type="text" x-model.debounce.500ms="search" @input="fetchEntitlements" placeholder="Search..." class="input input-bordered">
            </div>
            <div class="join">
                <button @click="currentPage > 1 && (currentPage--, fetchEntitlements())" :disabled="currentPage === 1" class="join-item btn">«</button>
                <button class="join-item btn" x-text="`Page ${currentPage}`"></button>
                <button @click="currentPage < totalPages && (currentPage++, fetchEntitlements())" :disabled="currentPage === totalPages" class="join-item btn">»</button>
            </div>
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
            currentPage: 1,
            totalPages: 1,
            isEdit: true,
            newEntitlement: {
                user_id: '',
                leave_type_id: '',
                initial_balance: '',
                year: new Date().getFullYear()
            },
            errors: {},
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
                    const response = await fetch(`${baseApiUrl}/admin/master/employee-entitlements?page=${this.currentPage}&per_page=${this.perPage}&search=${this.search}`, {
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
                    this.totalPages = data.data.last_page;
                } catch (error) {
                    console.error('Error fetching entitlements:', error);
                } finally {
                    this.loading = false;
                }
            },
            async fetchUsers() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/users?all=true`, {
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
                    const response = await fetch(`${baseApiUrl}/admin/master/leave-types?all=true`, {
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
                this.newEntitlement = { id: null, user_id: '', leave_type_id: '', initial_balance: '', year: new Date().getFullYear() };
                this.errors = {};
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
                this.errors = {};
                entitlement_modal.showModal();
            },
            async addEntitlement() {
                this.errors = {};
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
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to add entitlement.', 'error');
                        }
                        return;
                    }
                    this.fetchEntitlements();
                    this.showToast(data.meta.message);
                    entitlement_modal.close();
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error adding entitlement:', error);
                }
            },
            async updateEntitlement() {
                this.errors = {};
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
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to update entitlement.', 'error');
                        }
                        return;
                    }
                    this.fetchEntitlements();
                    this.showToast(data.meta.message);
                    entitlement_modal.close();
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error updating entitlement:', error);
                }
            },
            confirmDelete(entitlementId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.deleteEntitlement(entitlementId);
                    }
                });
            },
            async deleteEntitlement(entitlementId) {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/employee-entitlements/${entitlementId}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        this.showToast(data.meta.message || 'Failed to delete entitlement.', 'error');
                        return;
                    }
                    this.fetchEntitlements();
                    this.showToast(data.meta.message);
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error deleting entitlement:', error);
                }
            }
        }
    }
</script>
@endpush
