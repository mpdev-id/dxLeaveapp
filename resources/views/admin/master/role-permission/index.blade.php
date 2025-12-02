@extends('template.admin')
@section('title', 'Admin | Roles & Permissions')
@section('content')
<div class="container mx-auto px-4 sm:px-8 bg-base-100 border border-base-200 rounded-lg">
    <div class="py-8" x-data="rolePermissionManager('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold leading-tight">Roles & Permissions Management</h2>
            <button class="btn btn-primary" @click="openAddRoleModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Role
            </button>
        </div>

        <!-- Loading Skeleton -->
        <template x-if="loading">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="skeleton h-64 w-full"></div>
                <div class="skeleton h-64 w-full"></div>
                <div class="skeleton h-64 w-full"></div>
            </div>
        </template>

        <!-- Roles Grid -->
        <template x-if="!loading">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="role in roles" :key="role.id">
                    <div class="card bg-base-200 shadow-xl hover:shadow-2xl transition-shadow duration-300">
                        <div class="card-body">
                            <div class="flex justify-between items-start">
                                <h3 class="card-title text-lg" x-text="role.name"></h3>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                        <li><a @click="openEditPermissionsModal(role)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit Permissions
                                        </a></li>
                                        <li x-show="role.name !== 'Super Admin'">
                                            <a @click="confirmDeleteRole(role)" class="text-error">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete Role
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <p class="text-sm opacity-70 mb-2">
                                    <span x-text="role.permissions.length"></span> permissions assigned
                                </p>
                                <div class="flex flex-wrap gap-1 max-h-32 overflow-y-auto">
                                    <template x-for="permission in role.permissions.slice(0, 8)" :key="permission.id">
                                        <span class="badge badge-sm badge-outline" x-text="permission.name"></span>
                                    </template>
                                    <template x-if="role.permissions.length > 8">
                                        <span class="badge badge-sm badge-ghost" x-text="`+${role.permissions.length - 8} more`"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="card-actions justify-end mt-4">
                                <button class="btn btn-sm btn-primary" @click="openEditPermissionsModal(role)">
                                    Manage Permissions
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Add Role Modal -->
        <dialog id="add_role_modal" class="modal">
            <div class="modal-box">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="hideModal('add_role_modal')">✕</button>
                <h3 class="font-bold text-lg mb-4">Add New Role</h3>
                <form @submit.prevent="createRole()">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Role Name</span>
                        </label>
                        <input type="text" x-model="newRoleName" placeholder="e.g., HR Manager" class="input input-bordered" required>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn btn-ghost" onclick="hideModal('add_role_modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="loading">Create</button>
                    </div>
                </form>
            </div>
        </dialog>

        <!-- Edit Permissions Modal -->
        <dialog id="edit_permissions_modal" class="modal">
            <div class="modal-box w-11/12 max-w-4xl max-h-[90vh]">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" onclick="hideModal('edit_permissions_modal')">✕</button>
                <h3 class="font-bold text-lg mb-4">
                    Manage Permissions for <span x-text="selectedRole?.name" class="text-primary"></span>
                </h3>
                
                <div class="overflow-y-auto max-h-[60vh]">
                    <template x-for="(perms, module) in groupedPermissions" :key="module">
                        <div class="mb-6">
                            <h4 class="font-semibold text-md mb-3 capitalize flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <span x-text="module.replace('-', ' ')"></span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 ml-7">
                                <template x-for="permission in perms" :key="permission.id">
                                    <label class="cursor-pointer label justify-start gap-3">
                                        <input 
                                            type="checkbox" 
                                            class="checkbox checkbox-primary checkbox-sm"
                                            :value="permission.id"
                                            x-model="selectedPermissions"
                                        />
                                        <span class="label-text" x-text="permission.name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" onclick="hideModal('edit_permissions_modal')">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="updateRolePermissions()" :disabled="loading">
                        Save Changes
                    </button>
                </div>
            </div>
        </dialog>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function rolePermissionManager(baseApiUrl) {
        return {
            roles: [],
            permissions: [],
            groupedPermissions: {},
            loading: true,
            selectedRole: null,
            selectedPermissions: [],
            newRoleName: '',

            async init() {
                await this.fetchRoles();
                await this.fetchPermissions();
            },

            async fetchRoles() {
                this.loading = true;
                try {
                    const token = localStorage.getItem('authToken');
                    if (!token) {
                        window.location.href = '/login';
                        return;
                    }

                    const response = await fetch(`${baseApiUrl}/admin/master/roles-permissions/roles`, {
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
                    this.roles = data;
                } catch (error) {
                    console.error('Error fetching roles:', error);
                    this.showToast('Failed to load roles', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async fetchPermissions() {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/roles-permissions/permissions`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    this.permissions = data;
                    this.groupedPermissions = data;
                } catch (error) {
                    console.error('Error fetching permissions:', error);
                }
            },

            openAddRoleModal() {
                this.newRoleName = '';
                showModal('add_role_modal');
            },

            async createRole() {
                if (!this.newRoleName.trim()) return;

                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/roles-permissions/roles`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: this.newRoleName,
                            permissions: []
                        })
                    });

                    const data = await response.json();
                    
                    if (!response.ok) {
                        this.showToast(data.message || 'Failed to create role', 'error');
                        return;
                    }

                    this.showToast(data.message || 'Role created successfully');
                    hideModal('add_role_modal');
                    await this.fetchRoles();
                } catch (error) {
                    console.error('Error creating role:', error);
                    this.showToast('Failed to create role', 'error');
                }
            },

            openEditPermissionsModal(role) {
                this.selectedRole = role;
                this.selectedPermissions = role.permissions.map(p => p.id);
                showModal('edit_permissions_modal');
            },

            async updateRolePermissions() {
                if (!this.selectedRole) return;

                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/roles-permissions/roles/${this.selectedRole.id}/permissions`, {
                        method: 'PUT',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            permissions: this.selectedPermissions
                        })
                    });

                    const data = await response.json();
                    
                    if (!response.ok) {
                        this.showToast(data.message || 'Failed to update permissions', 'error');
                        return;
                    }

                    this.showToast(data.message || 'Permissions updated successfully');
                    hideModal('edit_permissions_modal');
                    await this.fetchRoles();
                } catch (error) {
                    console.error('Error updating permissions:', error);
                    this.showToast('Failed to update permissions', 'error');
                }
            },

            confirmDeleteRole(role) {
                if (role.name === 'Super Admin') {
                    this.showToast('Cannot delete Super Admin role', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Delete role "${role.name}"? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.deleteRole(role.id);
                    }
                });
            },

            async deleteRole(roleId) {
                try {
                    const token = localStorage.getItem('authToken');
                    const response = await fetch(`${baseApiUrl}/admin/master/roles-permissions/roles/${roleId}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    
                    if (!response.ok) {
                        this.showToast(data.message || 'Failed to delete role', 'error');
                        return;
                    }

                    this.showToast(data.message || 'Role deleted successfully');
                    await this.fetchRoles();
                } catch (error) {
                    console.error('Error deleting role:', error);
                    this.showToast('Failed to delete role', 'error');
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
            }
        }
    }
</script>
@endpush
