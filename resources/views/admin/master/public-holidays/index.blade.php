@extends('template.admin')

@section('title', 'Admin | Manage Public Holidays')

@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8" x-data="publicHolidaysTable()" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Public Holidays</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Public Holiday</button>
        </div>

        <!-- Add/Edit Public Holiday Modal -->
        <dialog id="publicholiday_modal" class="modal">
            <div class="modal-box">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                </form>
                <h3 class="font-bold text-lg" x-text="isEdit ? 'Edit Public Holiday' : 'Add New Public Holiday'"></h3>
                <form @submit.prevent="isEdit ? updatePublicHoliday() : addPublicHoliday()">
                    <div class="form-control">
                        <label class="label">Name</label>
                        <input type="text" x-model="newPublicHoliday.name" class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label">Date</label>
                        <input type="date" x-model="newPublicHoliday.date" class="input input-bordered" required>
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
                <p>Are you sure you want to delete this public holiday?</p>
                <div class="modal-action">
                    <button class="btn btn-error" @click="deletePublicHoliday()">Delete</button>
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
                        <th>Date</th>
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
                    <template x-for="publicHoliday in filteredPublicHolidays" :key="publicHoliday.id">
                        <tr>
                            <td x-text="publicHoliday.name"></td>
                            <td x-text="new Date(publicHoliday.date).toLocaleDateString()"></td>
                            <td>
                                <button class="btn btn-sm btn-info" @click="openEditModal(publicHoliday)">Edit</button>
                                <button class="btn btn-sm btn-error" @click="openDeleteModal(publicHoliday.id)">Delete</button>
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
    function publicHolidaysTable() {
        return {
            publicHolidays: [],
            loading: true,
            search: '',
            perPage: 10,
            isEdit: false,
            newPublicHoliday: {
                id: null,
                name: '',
                date: ''
            },
            publicHolidayToDelete: null,
            
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

                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/public-holidays', {
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
                } catch (error) {
                    console.error('Error fetching public holidays:', error);
                } finally {
                    this.loading = false;
                }
            },

            openAddModal() {
                this.isEdit = false;
                this.newPublicHoliday = { id: null, name: '', date: '' };
                publicholiday_modal.showModal();
            },

            openEditModal(publicHoliday) {
                this.isEdit = true;
                this.newPublicHoliday = { ...publicHoliday };
                publicholiday_modal.showModal();
            },

            openDeleteModal(publicHolidayId) {
                this.publicHolidayToDelete = publicHolidayId;
                delete_modal.showModal();
            },

            async addPublicHoliday() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch('http://leaveapp.redirect.my.id/api/admin/master/public-holidays', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newPublicHoliday)
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to add public holiday.');
                    }

                    this.fetchPublicHolidays();
                    publicholiday_modal.close();
                } catch (error) {
                    console.error('Error adding public holiday:', error);
                    alert(error.message);
                }
            },

            async updatePublicHoliday() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`http://leaveapp.redirect.my.id/api/admin/master/public-holidays/${this.newPublicHoliday.id}`, {
                        method: 'PUT',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newPublicHoliday)
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to update public holiday.');
                    }

                    this.fetchPublicHolidays();
                    publicholiday_modal.close();
                } catch (error) {
                    console.error('Error updating public holiday:', error);
                    alert(error.message);
                }
            },

            async deletePublicHoliday() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`http://leaveapp.redirect.my.id/api/admin/master/public-holidays/${this.publicHolidayToDelete}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        throw new Error(data.meta.message || 'Failed to delete public holiday.');
                    }

                    this.fetchPublicHolidays();
                    delete_modal.close();
                } catch (error) {
                    console.error('Error deleting public holiday:', error);
                    alert(error.message);
                }
            },

            get filteredPublicHolidays() {
                if (this.search === '') {
                    return this.publicHolidays.slice(0, this.perPage);
                }
                return this.publicHolidays.filter(publicHoliday => {
                    return publicHoliday.name.toLowerCase().includes(this.search.toLowerCase());
                }).slice(0, this.perPage);
            }
        }
    }
</script>
@endpush