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
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" @click="errors = {}">✕</button>
                </form>
                <h3 class="font-bold text-lg" x-text="isEdit ? 'Edit Leave Type' : 'Add New Leave Type'"></h3>
                <form @submit.prevent="isEdit ? updateLeaveType() : addLeaveType()">
                    <div class="form-control">
                        <label class="label">Name</label>
                        <input type="text" x-model="newLeaveType.name" class="input input-bordered" :class="{'input-error': errors.name}" required>
                        <div x-show="errors.name" class="text-error text-sm mt-1" x-text="errors.name ? errors.name[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">Days</label>
                        <input type="number" x-model="newLeaveType.days" class="input input-bordered" :class="{'input-error': errors.days}" required>
                        <div x-show="errors.days" class="text-error text-sm mt-1" x-text="errors.days ? errors.days[0] : ''"></div>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn" @click="leavetype_modal.close(); errors = {}">Cancel</button>
                        <button type="submit" class="btn btn-primary" x-text="isEdit ? 'Update' : 'Create'"></button>
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
                days: ''
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
                this.newLeaveType = { id: null, name: '', days: '' };
                this.errors = {};
                leavetype_modal.showModal();
            },

            openEditModal(leaveType) {
                this.isEdit = true;
                this.newLeaveType = { ...leaveType, days: parseFloat(leaveType.default_entitlement_days) };
                this.errors = {};
                leavetype_modal.showModal();
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
                    leavetype_modal.close();
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
                    leavetype_modal.close();
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