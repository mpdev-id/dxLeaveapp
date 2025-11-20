@extends('template.admin')

@section('content')
    <div x-data="leaveRequestData('{{ url('/') }}')" x-init="init()" class="container mx-auto p-4 relative">

        <h1 class="text-2xl font-bold mb-6">Leave Requests Management</h1>

        <!-- Controls -->
        <div class="flex flex-wrap items-center justify-between mb-4 gap-4">
            <div class="flex-grow md:flex-grow-0">
                <button @click="openCreateModal()" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    New Request
                </button>
            </div>
            <div class="relative flex-grow">
                <input type="text" placeholder="Search by user or leave type..." x-model="search"
                    @input.debounce.500ms="fetchLeaveRequests(1)" class="input input-bordered w-full pl-10">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd" />
                    </svg>
                </span>
            </div>
        </div>

        <!-- Loading state -->
        <div x-show="loading" class="text-center p-8">
            <span class="loading loading-lg loading-spinner text-primary"></span>
            <p>Loading data...</p>
        </div>

        <!-- Table -->
        <div x-show="!loading" class="overflow-x-auto bg-base-100 rounded-lg shadow">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th class="cursor-pointer" @click="changeSort('user_name')">
                            User
                            <span x-show="sortBy === 'user_name'" x-text="sortDir === 'asc' ? '▲' : '▼'"></span>
                        </th>
                        <th class="cursor-pointer" @click="changeSort('leave_type_name')">
                            Leave Type
                            <span x-show="sortBy === 'leave_type_name'" x-text="sortDir === 'asc' ? '▲' : '▼'"></span>
                        </th>
                        <th class="cursor-pointer" @click="changeSort('start_date')">
                            Date
                            <span x-show="sortBy === 'start_date'" x-text="sortDir === 'asc' ? '▲' : '▼'"></span>
                        </th>
                        <th>Duration</th>
                        <th class="cursor-pointer" @click="changeSort('current_status')">
                            Status
                            <span x-show="sortBy === 'current_status'" x-text="sortDir === 'asc' ? '▲' : '▼'"></span>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="leaveRequests.length === 0">
                        <tr>
                            <td colspan="6" class="text-center p-8">
                                <p class="font-bold">No leave requests found.</p>
                                <p class="text-gray-500">Try adjusting your search or filter.</p>
                            </td>
                        </tr>
                    </template>
                    <template x-for="req in leaveRequests" :key="req.id">
                        <tr>
                            <td>
                                <div class="font-bold" x-text="req.user.name"></div>
                                <div class="text-sm opacity-50" x-text="req.user.department?.name"></div>
                            </td>
                            <td x-text="req.leave_type.name"></td>
                            <td>
                                <span x-text="formatDate(req.start_date)"></span> -
                                <span x-text="formatDate(req.end_date)"></span>
                            </td>
                            <td>
                                <span x-text="req.duration_days + ' day(s)'"></span>
                            </td>
                            <td><span class="badge"
                                    :class="{
                                        'badge-warning': req.current_status === 'Pending',
                                        'badge-success': req.current_status === 'Approved',
                                        'badge-error': req.current_status === 'Rejected' || req.current_status === 'Canceled',
                                        'badge-ghost': req.current_status === 'Draft',
                                    }"
                                    x-text="req.current_status"></span></td>
                            <td>
                                <div class="dropdown dropdown-left">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path
                                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0"
                                        class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52 z-10">
                                        <li>
                                            <a @click="openDetailsModal(req)">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                View Details
                                            </a>
                                        </li>
                                        <template
                                            x-if="req.current_status === 'Pending'">
                                            <li>
                                                <a @click="openApprovalModal(req)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    Approve / Reject
                                                </a>
                                            </li>
                                        </template>
                                        <template x-if="req.current_status === 'Draft'">
                                            <li>
                                                <a @click="submitRequest(req.id)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path
                                                            d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                                    </svg>
                                                    Submit for Approval
                                                </a>
                                            </li>
                                        </template>
                                        <div class="divider my-1"></div>
                                        <li>
                                            <a @click="openEditModal(req)"
                                                :class="{'opacity-50 cursor-not-allowed': req.current_status !== 'Draft'}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path
                                                        d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                    <path fill-rule="evenodd"
                                                        d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a @click="confirmDelete(req.id)" class="text-error">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div x-show="!loading && pagination && pagination.total > 0"
            class="flex items-center justify-between mt-4">
            <span class="text-sm text-gray-700"
                x-text="`Showing ${pagination.from} to ${pagination.to} of ${pagination.total} results`"></span>
            <div class="btn-group">
                <button @click="fetchLeaveRequests(pagination.current_page - 1)" :disabled="!pagination.prev_page_url"
                    class="btn btn-outline">«</button>
                <button class="btn btn-outline"
                    x-text="`Page ${pagination.current_page} of ${pagination.last_page}`"></button>
                <button @click="fetchLeaveRequests(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                    class="btn btn-outline">»</button>
            </div>
        </div>

        <!-- Modals -->
        @include('admin.master.leave-request.modal-create-edit')
        @include('admin.master.leave-request.modal-details')
        @include('admin.master.leave-request.modal-approval')

    </div>

    <script>
        function leaveRequestData(baseApiUrl) {
            return {
                loading: true,
                leaveRequests: [],
                users: [],
                leaveTypes: [],
                pagination: null,
                search: '',
                sortBy: 'created_at',
                sortDir: 'desc',
                
                // Form & Modal state
                editMode: false,
                formData: {},
                formErrors: {},
                selectedRequest: null,

                // Approval Modal state
                approvalData: {
                    action: 'Approve',
                    comments: ''
                },

                init() {
                    this.resetForm();
                    this.fetchLeaveRequests();
                    this.fetchMasterData();
                },
                
                // --- UTILITIES ---
                getAuthHeaders() {
                    const token = localStorage.getItem('authToken');
                    if (!token) {
                        this.showToast('Authentication token not found. Redirecting to login.', 'error');
                        setTimeout(() => window.location.href = '/login', 2000);
                        throw new Error('Auth token not found.');
                    }
                    return {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    };
                },
                
                showToast(message, icon = 'success') {
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: icon,
                            title: message,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    } else {
                        alert(message);
                    }
                },
                
                handleError(error) {
                    console.error('API Error:', error);
                    if (error.response) {
                        if (error.response.status === 401) {
                            this.showToast('Session expired. Please log in again.', 'error');
                            localStorage.removeItem('authToken');
                            setTimeout(() => window.location.href = '/login', 2000);
                        } else if (error.response.status === 422) {
                            this.formErrors = error.response.data.errors;
                            this.showToast('Please correct the form errors.', 'error');
                        } else {
                            this.showToast(error.response.data.message || 'An unexpected error occurred.', 'error');
                        }
                    } else {
                        this.showToast('A network error occurred. Please try again.', 'error');
                    }
                },

                // --- DATA FETCHING ---
                async fetchLeaveRequests(page = 1) {
                    this.loading = true;
                    try {
                        const headers = this.getAuthHeaders();
                        const params = new URLSearchParams({
                            page: page,
                            per_page: 10,
                            search: this.search,
                            sort_by: this.sortBy,
                            sort_dir: this.sortDir
                        });

                        const response = await fetch(`${baseApiUrl}/api/admin/master/leave-requests?${params}`, { headers });

                        if (!response.ok) {
                           const errorData = await response.json();
                           throw { response: { status: response.status, data: errorData } };
                        }
                        
                        const data = await response.json();
                        this.leaveRequests = data.data;
                        this.pagination = data.meta;

                    } catch (error) {
                        this.handleError(error);
                    } finally {
                        this.loading = false;
                    }
                },
                async fetchMasterData() {
                     try {
                        const headers = this.getAuthHeaders();
                        const response = await fetch(`${baseApiUrl}/api/master-data`, { headers });
                        if (!response.ok) {
                           const errorData = await response.json();
                           throw { response: { status: response.status, data: errorData } };
                        }
                        const data = await response.json();
                        this.users = data.data.users;
                        this.leaveTypes = data.data.leave_types;
                    } catch (error) {
                        this.handleError(error);
                    }
                },

                // --- UI ACTIONS ---
                changeSort(column) {
                    if (this.sortBy === column) {
                        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortBy = column;
                        this.sortDir = 'asc';
                    }
                    this.fetchLeaveRequests();
                },
                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    const offset = date.getTimezoneOffset();
                    const correctedDate = new Date(date.getTime() + (offset*60*1000));
                    return correctedDate.toISOString().split('T')[0];
                },

                // --- MODAL & FORM ---
                openCreateModal() {
                    this.resetForm();
                    this.editMode = false;
                    document.getElementById('create_edit_modal').showModal();
                },
                openEditModal(req) {
                    if (req.current_status !== 'Draft') {
                        this.showToast("You can only edit requests that are in 'Draft' status.", 'warning');
                        return;
                    }
                    this.resetForm();
                    this.editMode = true;
                    this.formData = {
                        id: req.id,
                        user_id: req.user.id,
                        leave_type_id: req.leave_type.id,
                        start_date: this.formatDate(req.start_date),
                        end_date: this.formatDate(req.end_date),
                        leave_period: req.leave_period,
                        reason: req.reason,
                        supporting_document_path: req.supporting_attachment_path
                    };
                    document.getElementById('create_edit_modal').showModal();
                },
                openDetailsModal(req) {
                    this.selectedRequest = req;
                    document.getElementById('details_modal').showModal();
                },
                openApprovalModal(req) {
                    this.selectedRequest = req;
                    this.approvalData = { action: 'Approve', comments: '' };
                    document.getElementById('approval_modal').showModal();
                },
                closeModal(id) {
                    document.getElementById(id).close();
                },
                resetForm() {
                    this.formData = { id: null, user_id: '', leave_type_id: '', start_date: '', end_date: '', leave_period: 'full_day', reason: '', current_status: 'Draft', supporting_document: null };
                    this.formErrors = {};
                    const fileInput = document.getElementById('supporting_document');
if (fileInput) fileInput.value = '';
                },
                handleFileSelect(event) {
                    this.formData.supporting_document = event.target.files[0] || null;
                },

                // --- API ACTIONS (CRUD) ---
                async saveRequest() {
                    try {
                        const headers = this.getAuthHeaders();
                        // For FormData, we don't set Content-Type
                        delete headers['Content-Type']; 

                        const fd = new FormData();
                        const dataToSend = {...this.formData};
                        
                        let url = `${baseApiUrl}/api/admin/master/leave-requests`;

                        if (this.editMode) {
                            fd.append('_method', 'PUT');
                            url += `/${dataToSend.id}`;
                            delete dataToSend.user_id;
                            delete dataToSend.current_status;
                        }

                        for (const key in dataToSend) {
                            if (dataToSend[key] !== null && key !== 'supporting_document' && key !== 'supporting_document_path') {
                                fd.append(key, dataToSend[key]);
                            }
                        }
                        if (dataToSend.supporting_document) {
                            fd.append('supporting_document', dataToSend.supporting_document);
                        }
                        
                        const response = await fetch(url, { method: 'POST', headers, body: fd });
                        
                        const responseData = await response.json();
                        if (!response.ok) {
                            throw { response: { status: response.status, data: responseData } };
                        }

                        this.fetchLeaveRequests(this.pagination?.current_page || 1);
                        this.closeModal('create_edit_modal');
                        this.showToast(responseData.message);
                    } catch (error) {
                        this.handleError(error);
                    }
                },
                async submitRequest(id) {
                    if (!confirm('Are you sure you want to submit this request for approval?')) return;
                    try {
                        const headers = this.getAuthHeaders();
                        const response = await fetch(`${baseApiUrl}/api/admin/master/leave-requests/${id}/submit`, { method: 'POST', headers });
                        const responseData = await response.json();
                        if (!response.ok) throw { response: { status: response.status, data: responseData } };
                        
                        this.fetchLeaveRequests(this.pagination?.current_page || 1);
                        this.showToast(responseData.message);
                    } catch (error) {
                        this.handleError(error);
                    }
                },
                confirmDelete(id) {
                    if (window.Swal) {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.deleteRequest(id);
                            }
                        });
                    } else if (confirm('Are you sure you want to delete this?')) {
                        this.deleteRequest(id);
                    }
                },
                async deleteRequest(id) {
                    try {
                        const headers = this.getAuthHeaders();
                        const response = await fetch(`${baseApiUrl}/api/admin/master/leave-requests/${id}`, { method: 'DELETE', headers });
                        const responseData = await response.json();
                        if (!response.ok) throw { response: { status: response.status, data: responseData } };

                        this.fetchLeaveRequests(); // Go to first page after delete
                        this.showToast(responseData.message);
                    } catch (error) {
                        this.handleError(error);
                    }
                },
                async handleApproval() {
                    if (!confirm(`Are you sure you want to ${this.approvalData.action} this request?`)) return;
                    try {
                        const headers = { ...this.getAuthHeaders(), 'Content-Type': 'application/json' };
                        const response = await fetch(`${baseApiUrl}/api/admin/master/leave-requests/${this.selectedRequest.id}/handle-approval`, {
                            method: 'PATCH',
                            headers,
                            body: JSON.stringify(this.approvalData)
                        });
                        const responseData = await response.json();
                        if (!response.ok) throw { response: { status: response.status, data: responseData } };

                        this.fetchLeaveRequests(this.pagination?.current_page || 1);
                        this.closeModal('approval_modal');
                        this.showToast(responseData.message);
                    } catch (error) {
                        this.handleError(error);
                    }
                }
            }
        }
    </script>
@endsection