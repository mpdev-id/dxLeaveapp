@extends('template.admin')
@section('title', 'Admin | Manage Teams')
@section('content')
<div class="container mx-auto px-4 sm:px-8 bg-base-100 border border-base-200 rounded-lg">
    <div class="py-8" x-data="teamsTable('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold leading-tight">Teams</h2>
            <button class="btn btn-primary" @click="openAddModal()">Add Team</button>
        </div>
        <!-- Add/Edit Team Modal -->
        <dialog id="team_modal" class="modal">
            <div class="modal-box">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="hideModal('team_modal')">✕</button>
                <h3 class="font-bold text-lg mb-4" x-text="isEdit ? 'Edit Team' : 'Add New Team'"></h3>
                <form @submit.prevent="isEdit ? updateTeam() : addTeam()" class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" x-model="newTeam.name" placeholder="e.g., Development Team" class="input input-bordered w-full" :class="{'input-error': errors.name}" required>
                        <div x-show="errors.name" class="text-error text-sm mt-1" x-text="errors.name ? errors.name[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Department</span>
                        </label>
                        <select x-model="newTeam.department_id" class="select select-bordered w-full" :class="{'select-error': errors.department_id}" required>
                            <option value="">Select Department</option>
                            <template x-for="dept in departments" :key="dept.id">
                                <option :value="dept.id" x-text="dept.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.department_id" class="text-error text-sm mt-1" x-text="errors.department_id ? errors.department_id[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Leader</span>
                        </label>
                        <select x-model="newTeam.leader_id" class="select select-bordered w-full" :class="{'select-error': errors.leader_id}">
                            <option value="">Select Leader</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.leader_id" class="text-error text-sm mt-1" x-text="errors.leader_id ? errors.leader_id[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Additional Leader</span>
                        </label>
                        <select x-model="newTeam.additional_leader_id" class="select select-bordered w-full" :class="{'select-error': errors.additional_leader_id}">
                            <option value="">Select Additional Leader</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.additional_leader_id" class="text-error text-sm mt-1" x-text="errors.additional_leader_id ? errors.additional_leader_id[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Shift Leader (SL) - Optional</span>
                        </label>
                        <select x-model="newTeam.sl_id" class="select select-bordered w-full" :class="{'select-error': errors.sl_id}">
                            <option value="">Select SL</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.sl_id" class="text-error text-sm mt-1" x-text="errors.sl_id ? errors.sl_id[0] : ''"></div>
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Assistant Manager (ASMEN) - Optional</span>
                        </label>
                        <select x-model="newTeam.asmen_id" class="select select-bordered w-full" :class="{'select-error': errors.asmen_id}">
                            <option value="">Select ASMEN</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="user.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.asmen_id" class="text-error text-sm mt-1" x-text="errors.asmen_id ? errors.asmen_id[0] : ''"></div>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn btn-ghost" onclick="hideModal('team_modal')">Cancel</button>
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
                        <th>Department</th>
                        <th>Leader</th>
                        <th>Add. Leader</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="loading">
                        <tr>
                            <td colspan="5" class="text-center py-10"><span class="loading loading-spinner loading-lg"></span></td>
                        </tr>
                    </template>
                    <template x-if="!loading && teams.length === 0">
                        <tr>
                            <td colspan="5" class="text-center py-4">No teams found.</td>
                        </tr>
                    </template>
                    <template x-for="team in teams" :key="team.id">
                        <tr>
                            <td x-text="team.name"></td>
                            <td x-text="team.department ? team.department.name : '-'"></td>
                            <td x-text="team.leader ? team.leader.name : '-'"></td>
                            <td x-text="team.additional_leader ? team.additional_leader.name : '-'"></td>
                            <td>
                                <button @click="openEditModal(team)" class="btn btn-sm btn-warning">Edit</button>
                                <button @click="confirmDelete(team.id)" class="btn btn-sm btn-error">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex justify-between items-center mt-4">
            <div class="flex items-center gap-2">
                <select x-model="perPage" @change="fetchTeams" class="select select-bordered">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
                <input type="text" x-model.debounce.500ms="search" @input="fetchTeams" placeholder="Search..." class="input input-bordered">
            </div>
            <div class="join">
                <button @click="currentPage > 1 && (currentPage--, fetchTeams())" :disabled="currentPage === 1" class="join-item btn">«</button>
                <button class="join-item btn" x-text="`Page ${currentPage}`"></button>
                <button @click="currentPage < totalPages && (currentPage++, fetchTeams())" :disabled="currentPage === totalPages" class="join-item btn">»</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function teamsTable(baseApiUrl) {
        return {
            teams: [],
            departments: [],
            users: [],
            loading: true,
            search: '',
            perPage: 10,
            currentPage: 1,
            totalPages: 1,
            isEdit: false,
            newTeam: { id: null, name: '', department_id: '', leader_id: '', additional_leader_id: '', sl_id: '', asmen_id: '' },
            errors: {},

            init() {
                this.fetchTeams();
                this.fetchDepartments();
                this.fetchUsers();
            },

            async fetchTeams() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) { window.location.href = '/login'; return; }
                    const response = await fetch(`${baseApiUrl}/admin/master/teams?page=${this.currentPage}&per_page=${this.perPage}&search=${this.search}`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    if (response.status === 401) { localStorage.removeItem('authToken'); window.location.href = '/login'; return; }
                    const data = await response.json();
                    this.teams = data.data.data;
                    this.totalPages = data.data.last_page;
                } catch (error) {
                    console.error('Error fetching teams:', error);
                    this.showToast('Failed to fetch teams.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async fetchDepartments() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/departments?all=true`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    this.departments = data.data;
                } catch (error) {
                    console.error('Error fetching departments:', error);
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
                this.newTeam = { id: null, name: '', department_id: '', leader_id: '', additional_leader_id: '', sl_id: '', asmen_id: '' };
                this.errors = {};
                showModal('team_modal');
            },

            openEditModal(team) {
                this.isEdit = true;
                this.newTeam = { 
                    id: team.id, 
                    name: team.name, 
                    department_id: team.department_id, 
                    leader_id: team.leader_id, 
                    additional_leader_id: team.additional_leader_id,
                    sl_id: team.sl_id,
                    asmen_id: team.asmen_id
                };
                this.errors = {};
                showModal('team_modal');
            },

            async addTeam() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/teams`, {
                        method: 'POST',
                        headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(this.newTeam)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to add team.', 'error');
                        }
                        return;
                    }
                    this.fetchTeams();
                    this.showToast(data.meta.message);
                    hideModal('team_modal');
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error adding team:', error);
                }
            },

            async updateTeam() {
                this.errors = {};
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/teams/${this.newTeam.id}`, {
                        method: 'PUT',
                        headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(this.newTeam)
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            this.showToast(data.meta.message || 'Failed to update team.', 'error');
                        }
                        return;
                    }
                    this.fetchTeams();
                    this.showToast(data.meta.message);
                    hideModal('team_modal');
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error updating team:', error);
                }
            },

            confirmDelete(teamId) {
                Swal.fire({
                    title: 'Are you sure?', text: "You won't be able to revert this!", icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) { this.deleteTeam(teamId); }
                });
            },

            async deleteTeam(teamId) {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/teams/${teamId}`, {
                        method: 'DELETE',
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (!response.ok || data.meta.status !== 'success') {
                        this.showToast(data.meta.message || 'Failed to delete team.', 'error');
                        return;
                    }
                    this.fetchTeams();
                    this.showToast(data.meta.message);
                } catch (error) {
                    this.showToast('An unexpected error occurred.', 'error');
                    console.error('Error deleting team:', error);
                }
            }
        }
    }
</script>
@endpush
