@extends('template.admin')
@section('title', 'Admin | Manage Public Holidays')
@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8" x-data="publicHolidaysTable('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Public Holidays</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Public Holiday</button>
        </div>
        <!-- Add/Edit Public Holiday Modal -->
        <dialog id="publicholiday_modal" class="modal">
            <div class="modal-box">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" @click="errors = {}">✕</button>
                </form>
                <h3 class="font-bold text-lg mb-4" x-text="isEdit ? 'Edit Public Holiday' : 'Add New Public Holiday'"></h3>
                <form @submit.prevent="isEdit ? updatePublicHoliday() : addPublicHoliday()" class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                                Name
                            </span>
                        </label>
                        <input type="text" x-model="newPublicHoliday.name" placeholder="e.g., Christmas Day" class="input input-bordered w-full" :class="{'input-error': errors.name}" required>
                        <div x-show="errors.name" class="text-error text-sm mt-1" x-text="errors.name ? errors.name[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Date
                            </span>
                        </label>
                        <input type="date" x-model="newPublicHoliday.date" class="input input-bordered w-full" :class="{'input-error': errors.date}" required>
                        <div x-show="errors.date" class="text-error text-sm mt-1" x-text="errors.date ? errors.date[0] : ''"></div>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn btn-ghost" @click="publicholiday_modal.close(); errors = {}">Cancel</button>
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
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="3" class="text-center py-10"><span class="loading loading-spinner loading-lg"></span></td>
                        </tr>
                    </template>
                    <template x-if="!loading && publicHolidays.length === 0">
                        <tr>
                            <td colspan="3" class="text-center py-4">No public holidays found.</td>
                        </tr>
                    </template>
                    <template x-for="publicHoliday in publicHolidays" :key="publicHoliday.id">
                        <tr>
                            <td x-text="publicHoliday.name"></td>
                            <td x-text="new Date(publicHoliday.date).toLocaleDateString()"></td>
                            <td>
                                <button class="btn btn-sm btn-info" @click="openEditModal(publicHoliday)">Edit</button>
                                <button class="btn btn-sm btn-error" @click="confirmDelete(publicHoliday.id)">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex justify-between items-center mt-4">
            <div class="flex items-center gap-2">
                <select x-model="perPage" @change="fetchPublicHolidays" class="select select-bordered">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
                <input type="text" x-model.debounce.500ms="search" @input="fetchPublicHolidays" placeholder="Search..." class="input input-bordered">
            </div>
            <div class="join">
                <button @click="currentPage > 1 && (currentPage--, fetchPublicHolidays())" :disabled="currentPage === 1" class="join-item btn">«</button>
                <button class="join-item btn" x-text="`Page ${currentPage}`"></button>
                <button @click="currentPage < totalPages && (currentPage++, fetchPublicHolidays())" :disabled="currentPage === totalPages" class="join-item btn">»</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    function publicHolidaysTable(baseApiUrl) {
        return {
            publicHolidays: [],
            loading: true,
            search: '',
            perPage: 10,
            currentPage: 1,
            totalPages: 1,
            isEdit: false,
            newPublicHoliday: {
                id: null,
                name: '',
                date: ''
            },
            errors: {},
            init() {
                this.fetchPublicHolidays();
            },
            async fetchPublicHolidays() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) {
                        window.location.href = '/login';
                        return;
                    }
                    const response = await fetch(`${baseApiUrl}/admin/master/public-holidays?page=${this.currentPage}&per_page=${this.perPage}&search=${this.search}`, {
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
                    this.publicHolidays = data.data.data;
                    this.totalPages = data.data.last_page;
                } catch (error) {
                    console.error('Error fetching public holidays:', error);
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
                this.newPublicHoliday = { id: null, name: '', date: '' };
                this.errors = {};
                publicholiday_modal.showModal();
            },
            openEditModal(publicHoliday) {
                this.isEdit = true;
                this.newPublicHoliday = { ...publicHoliday };
                this.errors = {};
                publicholiday_modal.showModal();
            },
            async addPublicHoliday() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/public-holidays`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newPublicHoliday)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to add public holiday.', 'error');
                        }
                        return;
                    }
                    this.fetchPublicHolidays();
                    this.showToast(data.meta.message);
                    publicholiday_modal.close();
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error adding public holiday:', error);
                }
            },
            async updatePublicHoliday() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/public-holidays/${this.newPublicHoliday.id}`, {
                        method: 'PUT',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newPublicHoliday)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to update public holiday.', 'error');
                        }
                        return;
                    }
                    this.fetchPublicHolidays();
                    this.showToast(data.meta.message);
                    publicholiday_modal.close();
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error updating public holiday:', error);
                }
            },
            confirmDelete(publicHolidayId) {
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
                        this.deletePublicHoliday(publicHolidayId);
                    }
                });
            },
            async deletePublicHoliday(publicHolidayId) {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/public-holidays/${publicHolidayId}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        this.showToast(data.meta.message || 'Failed to delete public holiday.', 'error');
                        return;
                    }
                    this.fetchPublicHolidays();
                    this.showToast(data.meta.message);
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error deleting public holiday:', error);
                }
            }
        }
    }
</script>
@endpush
