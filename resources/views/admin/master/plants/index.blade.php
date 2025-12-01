@extends('template.admin')
@section('title', 'Admin | Manage Plants')
@section('content')
<div class="container mx-auto px-4 sm:px-8 bg-base-100 border border-base-200 rounded-lg">
    <div class="py-8" x-data="plantsTable('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Plants</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Plant</button>
        </div>
        <!-- Add/Edit Plant Modal -->
        <dialog id="plant_modal" class="modal">
            <div class="modal-box">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="hideModal('plant_modal')">✕</button>
                <h3 class="font-bold text-lg mb-4" x-text="isEdit ? 'Edit Plant' : 'Add New Plant'"></h3>
                <form @submit.prevent="isEdit ? updatePlant() : addPlant()" class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" x-model="newPlant.name" placeholder="e.g., Plant A" class="input input-bordered w-full" :class="{'input-error': errors.name}" required>
                        <div x-show="errors.name" class="text-error text-sm mt-1" x-text="errors.name ? errors.name[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Team</span>
                        </label>
                        <select x-model="newPlant.team_id" class="select select-bordered w-full" :class="{'select-error': errors.team_id}" required>
                            <option value="">Select Team</option>
                            <template x-for="team in teams" :key="team.id">
                                <option :value="team.id" x-text="team.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.team_id" class="text-error text-sm mt-1" x-text="errors.team_id ? errors.team_id[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Supervisor</span>
                        </label>
                        <select x-model="newPlant.supervisor_id" class="select select-bordered w-full" :class="{'select-error': errors.supervisor_id}">
                            <option value="">Select Supervisor</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.supervisor_id" class="text-error text-sm mt-1" x-text="errors.supervisor_id ? errors.supervisor_id[0] : ''"></div>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn btn-ghost" onclick="hideModal('plant_modal')">Cancel</button>
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
                        <th>Team</th>
                        <th>Supervisor</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="4" class="text-center py-10"><span class="loading loading-dots loading-lg"></span></td>
                        </tr>
                    </template>
                    <template x-if="!loading && plants.length === 0">
                        <tr>
                            <td colspan="4" class="text-center py-4">No plants found.</td>
                        </tr>
                    </template>
                    <template x-for="plant in plants" :key="plant.id">
                        <tr>
                            <td x-text="plant.name"></td>
                            <td x-text="plant.team ? plant.team.name : '-'"></td>
                            <td x-text="plant.supervisor ? plant.supervisor.name : '-'"></td>
                            <td>
                                <button @click="openEditModal(plant)" class="btn btn-sm btn-warning">Edit</button>
                                <button @click="confirmDelete(plant.id)" class="btn btn-sm btn-error">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex justify-between items-center mt-4">
            <div class="flex items-center gap-2">
                <select x-model="perPage" @change="fetchPlants" class="select select-bordered">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
                <input type="text" x-model.debounce.500ms="search" @input="fetchPlants" placeholder="Search..." class="input input-bordered">
            </div>
            <div class="join">
                <button @click="currentPage > 1 && (currentPage--, fetchPlants())" :disabled="currentPage === 1" class="join-item btn">«</button>
                <button class="join-item btn" x-text="`Page ${currentPage}`"></button>
                <button @click="currentPage < totalPages && (currentPage++, fetchPlants())" :disabled="currentPage === totalPages" class="join-item btn">»</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function plantsTable(baseApiUrl) {
        return {
            plants: [],
            teams: [],
            users: [],
            loading: true,
            search: '',
            perPage: 10,
            currentPage: 1,
            totalPages: 1,
            isEdit: false,
            newPlant: { id: null, name: '', team_id: '', supervisor_id: '' },
            errors: {},

            init() {
                this.fetchPlants();
                this.fetchTeams();
                this.fetchUsers();
            },

            async fetchPlants() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) { window.location.href = '/login'; return; }
                    const response = await fetch(`${baseApiUrl}/admin/master/plants?page=${this.currentPage}&per_page=${this.perPage}&search=${this.search}`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    if (response.status === 401) { localStorage.removeItem('authToken'); window.location.href = '/login'; return; }
                    const data = await response.json();
                    this.plants = data.data.data;
                    this.totalPages = data.data.last_page;
                } catch (error) {
                    console.error('Error fetching plants:', error);
                    this.showToast('Failed to fetch plants.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async fetchTeams() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/teams?all=true`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    this.teams = data.data;
                } catch (error) {
                    console.error('Error fetching teams:', error);
                }
            },

            async fetchUsers() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/users?all=true`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    this.users = data.data;
                } catch (error) {
                    console.error('Error fetching users:', error);
                }
            },

            showToast(message, icon = 'success') {
                Swal.fire({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, icon: icon, title: message
                });
            },

            openAddModal() {
                this.isEdit = false;
                this.newPlant = { id: null, name: '', team_id: '', supervisor_id: '' };
                this.errors = {};
                showModal('plant_modal');
            },

            openEditModal(plant) {
                this.isEdit = true;
                this.newPlant = { 
                    id: plant.id, 
                    name: plant.name, 
                    team_id: plant.team_id, 
                    supervisor_id: plant.supervisor_id 
                };
                this.errors = {};
                showModal('plant_modal');
            },

            async addPlant() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/plants`, {
                        method: 'POST',
                        headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(this.newPlant)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to add plant.', 'error');
                        }
                        return;
                    }
                    this.fetchPlants();
                    this.showToast(data.meta.message);
                    hideModal('plant_modal');
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error adding plant:', error);
                }
            },

            async updatePlant() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/plants/${this.newPlant.id}`, {
                        method: 'PUT',
                        headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(this.newPlant)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to update plant.', 'error');
                        }
                        return;
                    }
                    this.fetchPlants();
                    this.showToast(data.meta.message);
                    hideModal('plant_modal');
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error updating plant:', error);
                }
            },

            confirmDelete(plantId) {
                Swal.fire({
                    title: 'Are you sure?', text: "You won't be able to revert this!", icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) { this.deletePlant(plantId); }
                });
            },

            async deletePlant(plantId) {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/plants/${plantId}`, {
                        method: 'DELETE',
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        this.showToast(data.meta.message || 'Failed to delete plant.', 'error');
                        return;
                    }
                    this.fetchPlants();
                    this.showToast(data.meta.message);
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error deleting plant:', error);
                }
            }
        }
    }
</script>
@endpush
