@extends('template.admin')
@section('title', 'Admin | Manage Employee Entitlements')
@section('content')
<div class="container mx-auto px-4 sm:px-8 bg-base-100 border border-base-200 rounded-lg">
    <div class="py-8" x-data="employeeEntitlementsTable('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Employee Entitlements</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Entitlement</button>
        </div>
        <!-- Add/Edit Entitlement Modal -->
        <dialog id="entitlement_modal" class="modal">
            <div class="modal-box">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="hideModal('entitlement_modal')">✕</button>
                <h3 class="font-bold text-lg mb-4" x-text="isEdit ? 'Edit Entitlement' : 'Add New Entitlement'"></h3>
                <form @submit.prevent="isEdit ? updateEntitlement() : addEntitlement()" class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Employee
                            </span>
                        </label>
                        <select x-model="newEntitlement.user_id" class="select select-bordered w-full" :class="{'select-error': errors.user_id}" required>
                            <option value="">Select Employee</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.user_id" class="text-error text-sm mt-1" x-text="errors.user_id ? errors.user_id[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v2a2 2 0 01-2 2h-5m-5 0a2 2 0 01-2-2v-2a2 2 0 012-2h5m-9 0a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Leave Type
                            </span>
                        </label>
                        <select x-model="newEntitlement.leave_type_id" class="select select-bordered w-full" :class="{'select-error': errors.leave_type_id}" required>
                            <option value="">Select Leave Type</option>
                            <template x-for="leaveType in leaveTypes" :key="leaveType.id">
                                <option :value="leaveType.id" x-text="leaveType.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.leave_type_id" class="text-error text-sm mt-1" x-text="errors.leave_type_id ? errors.leave_type_id[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3 .895 3 2s-1.343 2-3 2-3-.895-3-2 1.343-2 3-2zM21 12a9 9 0 11-18 0 9 9 0 0118 0zM10 16a2 2 0 012-2h.01a2 2 0 012 2v2a2 2 0 01-2 2h-4a2 2 0 01-2-2v-2z" /></svg>
                                Balance
                            </span>
                        </label>
                        <input type="number" x-model="newEntitlement.initial_balance" placeholder="e.g., 12.0" class="input input-bordered w-full" :class="{'input-error': errors.initial_balance}" required>
                        <div x-show="errors.initial_balance" class="text-error text-sm mt-1" x-text="errors.initial_balance ? errors.initial_balance[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Year
                            </span>
                        </label>
                        <input type="number" x-model="newEntitlement.year" placeholder="e.g., 2025" class="input input-bordered w-full" :class="{'input-error': errors.year}" required>
                        <div x-show="errors.year" class="text-error text-sm mt-1" x-text="errors.year ? errors.year[0] : ''"></div>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn btn-ghost" onclick="hideModal('entitlement_modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="loading" x-text="isEdit ? 'Update' : 'Create'"></button>
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
                showModal('entitlement_modal');
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
                showModal('entitlement_modal');
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
                    hideModal('entitlement_modal');
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
                    hideModal('entitlement_modal');
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
