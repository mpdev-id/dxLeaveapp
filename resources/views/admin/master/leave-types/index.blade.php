@extends('template.admin')
@section('title', 'Admin | Manage Leave Types')
@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8" x-data="leaveTypesTable('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Leave Types</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Leave Type</button>
        </div>
        <!-- Add/Edit Leave Type Modal -->
        <dialog id="leavetype_modal" class="modal">
            <div class="modal-box">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="hideModal('leavetype_modal')">✕</button>
                <h3 class="font-bold text-lg mb-4" x-text="isEdit ? 'Edit Leave Type' : 'Add New Leave Type'"></h3>
                <form @submit.prevent="isEdit ? updateLeaveType() : addLeaveType()" class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Name *</span>
                        </label>
                        <input type="text" x-model="newLeaveType.name" placeholder="e.g., Annual Leave" class="input input-bordered w-full" :class="{'input-error': errors.name}" required>
                        <div x-show="errors.name" class="text-error text-sm mt-1" x-text="errors.name ? errors.name[0] : ''"></div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Default Entitlement Days *</span>
                        </label>
                        <input type="number" x-model="newLeaveType.default_entitlement_days" placeholder="e.g., 12" class="input input-bordered w-full" :class="{'input-error': errors.default_entitlement_days}" required min="0">
                        <div x-show="errors.default_entitlement_days" class="text-error text-sm mt-1" x-text="errors.default_entitlement_days ? errors.default_entitlement_days[0] : ''"></div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Max Carry Over Days</span>
                        </label>
                        <input type="number" x-model="newLeaveType.max_carry_over_days" placeholder="e.g., 5 (optional)" class="input input-bordered w-full" :class="{'input-error': errors.max_carry_over_days}" min="0">
                        <label class="label">
                            <span class="label-text-alt">Leave blank if no carry over allowed</span>
                        </label>
                        <div x-show="errors.max_carry_over_days" class="text-error text-sm mt-1" x-text="errors.max_carry_over_days ? errors.max_carry_over_days[0] : ''"></div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Accrual Frequency</span>
                        </label>
                        <select x-model="newLeaveType.accrual_frequency" class="select select-bordered w-full" :class="{'select-error': errors.accrual_frequency}">
                            <option value="">None</option>
                            <option value="Annually">Annually</option>
                            <option value="Monthly">Monthly</option>
                            <option value="LumpSum">Lump Sum</option>
                            <option value="Per Request">Per Request</option>
                        </select>
                        <div x-show="errors.accrual_frequency" class="text-error text-sm mt-1" x-text="errors.accrual_frequency ? errors.accrual_frequency[0] : ''"></div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" x-model="newLeaveType.is_paid" class="checkbox checkbox-primary">
                            <span class="label-text font-semibold">Is Paid Leave?</span>
                        </label>
                        <div x-show="errors.is_paid" class="text-error text-sm mt-1" x-text="errors.is_paid ? errors.is_paid[0] : ''"></div>
                    </div>
                    
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" x-model="newLeaveType.requires_attachment" class="checkbox checkbox-primary">
                            <span class="label-text font-semibold">Requires Attachment?</span>
                        </label>
                        <div x-show="errors.requires_attachment" class="text-error text-sm mt-1" x-text="errors.requires_attachment ? errors.requires_attachment[0] : ''"></div>
                    </div>
                    
                    <div class="modal-action">
                        <button type="button" class="btn btn-ghost" onclick="hideModal('leavetype_modal')">Cancel</button>
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
                        <th>Days</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="3" class="text-center py-10"><span class="loading loading-spinner loading-lg"></span></td>
                        </tr>
                    </template>
                    <template x-if="!loading && leaveTypes.length === 0">
                        <tr>
                            <td colspan="3" class="text-center py-4">No leave types found.</td>
                        </tr>
                    </template>
                    <template x-for="leaveType in leaveTypes" :key="leaveType.id">
                        <tr>
                            <td x-text="leaveType.name"></td>
                            <td x-text="parseFloat(leaveType.default_entitlement_days).toFixed(2)"></td>
                            <td>
                                <button class="btn btn-sm btn-info" @click="openEditModal(leaveType)">Edit</button>
                                <button class="btn btn-sm btn-error" @click="confirmDelete(leaveType.id)">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex justify-between items-center mt-4">
            <div class="flex items-center gap-2">
                <select x-model="perPage" @change="fetchLeaveTypes" class="select select-bordered">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
                <input type="text" x-model.debounce.500ms="search" @input="fetchLeaveTypes" placeholder="Search..." class="input input-bordered">
            </div>
            <div class="join">
                <button @click="currentPage > 1 && (currentPage--, fetchLeaveTypes())" :disabled="currentPage === 1" class="join-item btn">«</button>
                <button class="join-item btn" x-text="`Page ${currentPage}`"></button>
                <button @click="currentPage < totalPages && (currentPage++, fetchLeaveTypes())" :disabled="currentPage === totalPages" class="join-item btn">»</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function leaveTypesTable(baseApiUrl) {
        return {
            leaveTypes: [],
            loading: true,
            search: '',
            perPage: 10,
            currentPage: 1,
            totalPages: 1,
            isEdit: false,
            newLeaveType: {
                id: null,
                name: '',
                default_entitlement_days: '',
                max_carry_over_days: '',
                accrual_frequency: '',
                is_paid: true,
                requires_attachment: false
            },
            errors: {},

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
                    const response = await fetch(`${baseApiUrl}/admin/master/leave-types?page=${this.currentPage}&per_page=${this.perPage}&search=${this.search}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    if (response.status === 401) {
                        localStorage.removeItem('authToken');
                        window.location.href = '/login';
                        return;
                    }
                    const data = await response.json();
                    this.leaveTypes = data.data.data;
                    this.totalPages = data.data.last_page;
                } catch (error) {
                    console.error('Error fetching leave types:', error);
                } finally {
                    this.loading = false;
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
                this.newLeaveType = { 
                    id: null, 
                    name: '', 
                    default_entitlement_days: '',
                    max_carry_over_days: '',
                    accrual_frequency: '',
                    is_paid: true,
                    requires_attachment: false
                };
                this.errors = {};
                showModal('leavetype_modal');
            },

            openEditModal(leaveType) {
                this.isEdit = true;
                this.newLeaveType = { 
                    id: leaveType.id,
                    name: leaveType.name,
                    default_entitlement_days: leaveType.default_entitlement_days || '',
                    max_carry_over_days: leaveType.max_carry_over_days || '',
                    accrual_frequency: leaveType.accrual_frequency || '',
                    is_paid: leaveType.is_paid === 1 || leaveType.is_paid === true,
                    requires_attachment: leaveType.requires_attachment === 1 || leaveType.requires_attachment === true
                };
                this.errors = {};
                showModal('leavetype_modal');
            },

            async addLeaveType() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/leave-types`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newLeaveType)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to add leave type.', 'error');
                        }
                        return;
                    }
                    this.fetchLeaveTypes();
                    this.showToast(data.meta.message);
                    hideModal('leavetype_modal');
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error adding leave type:', error);
                }
            },

            async updateLeaveType() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/leave-types/${this.newLeaveType.id}`, {
                        method: 'PUT',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newLeaveType)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to update leave type.', 'error');
                        }
                        return;
                    }
                    this.fetchLeaveTypes();
                    this.showToast(data.meta.message);
                    hideModal('leavetype_modal');
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error updating leave type:', error);
                }
            },

            confirmDelete(leaveTypeId) {
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
                        this.deleteLeaveType(leaveTypeId);
                    }
                });
            },

            async deleteLeaveType(leaveTypeId) {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/leave-types/${leaveTypeId}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        this.showToast(data.meta.message || 'Failed to delete leave type.', 'error');
                        return;
                    }
                    this.fetchLeaveTypes();
                    this.showToast(data.meta.message);
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error deleting leave type:', error);
                }
            }
        }
    }
</script>
@endpush