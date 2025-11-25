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
        <div x-show="!loading">
            <!-- Desktop Table View -->
            <div class="hidden md:block bg-base-100 rounded-lg shadow">
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
                                            @include('admin.master.leave-request.actions-menu')
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="block md:hidden space-y-4">
                <template x-if="leaveRequests.length === 0">
                    <div class="bg-base-100 rounded-lg shadow p-8 text-center">
                        <p class="font-bold">No leave requests found.</p>
                        <p class="text-gray-500">Try adjusting your search or filter.</p>
                    </div>
                </template>
                <template x-for="req in leaveRequests" :key="req.id">
                    <div class="bg-base-100 rounded-lg shadow p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-bold" x-text="req.user.name"></div>
                                <div class="text-sm opacity-50" x-text="req.leave_type.name"></div>
                            </div>
                            <span class="badge"
                                :class="{
                                    'badge-warning': req.current_status === 'Pending',
                                    'badge-success': req.current_status === 'Approved',
                                    'badge-error': req.current_status === 'Rejected' || req.current_status === 'Canceled',
                                    'badge-ghost': req.current_status === 'Draft',
                                }"
                                x-text="req.current_status"></span>
                        </div>
                        <div class="divider my-2"></div>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <div class="font-semibold">From</div>
                                <div x-text="formatDate(req.start_date)"></div>
                            </div>
                            <div>
                                <div class="font-semibold">To</div>
                                <div x-text="formatDate(req.end_date)"></div>
                            </div>
                            <div>
                                <div class="font-semibold">Duration</div>
                                <div x-text="req.duration_days + ' day(s)'"></div>
                            </div>
                            <div>
                                <div class="font-semibold">Actions</div>
                                <div class="dropdown dropdown-left">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0"
                                        class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52 z-10">
                                        @include('admin.master.leave-request.actions-menu')
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
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
@endsection
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        function leaveRequestData(baseApiUrl) {
            return {
                loading: true,
                leaveRequests: [],
                users: [],
                leaveTypes: [],
                workflows: [],
                employeeEntitlements: [],
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
                    action: 'Approved',
                    comments: '',
                    approver_id: ''
                },
                userRoles: [],
                get isAdmin() {
                    return this.userRoles.includes('Super Admin');
                },
                get suggestedApprovers() {
                    if (!this.users || this.users.length === 0) {
                        console.warn('Master data "users" is empty or not loaded.');
                        return [];
                    }

                    if (!this.selectedRequest || !this.selectedRequest.workflow || !this.selectedRequest.workflow.steps) {
                        console.warn('Workflow steps not found in selected request.');
                        return [];
                    }

                    // Get current workflow step
                    const currentStepId = this.selectedRequest.current_workflow_step_id;
                    console.log('Current Step ID:', currentStepId);
                    console.log('Selected Request:', this.selectedRequest);
                    
                    if (!currentStepId) {
                        console.warn('No current step, request might be completed or not started');
                        return [];
                    }

                    // Find the current step
                    const currentStep = this.selectedRequest.workflow.steps.find(s => s.id === currentStepId);
                    console.log('Current Step:', currentStep);
                    console.log('All Steps:', this.selectedRequest.workflow.steps);
                    
                    if (!currentStep) {
                        console.warn('Current workflow step not found in workflow steps');
                        return [];
                    }

                    const options = [];
                    let stepApprovers = [];

                    // 1. Specific User Assigned (Calculated by Backend)
                    if (currentStep.assigned_approver) {
                         console.log('Using assigned_approver:', currentStep.assigned_approver);
                         // If backend already resolved it (which we added in LeaveRequestResource)
                         const user = this.users.find(u => u.id == currentStep.assigned_approver.id);
                         if (user) stepApprovers.push(user);
                         else stepApprovers.push(currentStep.assigned_approver); // Fallback if not in users list
                    }
                    // 2. Specific User ID in Step Definition (Fallback if assigned_approver missing)
                    else if (currentStep.required_approver_type === 'User' && currentStep.approver_user_id) {
                        console.log('Using specific user:', currentStep.approver_user_id);
                        const user = this.users.find(u => u.id == currentStep.approver_user_id);
                        if (user) stepApprovers.push(user);
                    }
                    // 3. Manager Type - Requester's Manager
                    else if (currentStep.required_approver_type === 'Manager') {
                        console.log('Using Manager type');
                        if (this.selectedRequest.user && this.selectedRequest.user.manager) {
                             const managerId = this.selectedRequest.user.manager.id;
                             const manager = this.users.find(u => u.id == managerId) || this.selectedRequest.user.manager;
                             if (manager) stepApprovers.push(manager);
                        }
                    }
                    // 4. Role Based
                    else if (currentStep.required_approver_type === 'Role' && currentStep.approver_role) {
                        console.log('Using Role type:', currentStep.approver_role.name);
                        const roleName = currentStep.approver_role.name;
                        const roleUsers = this.users.filter(u => {
                            if (!u.role) return false;
                            const userRoles = Array.isArray(u.role) ? u.role : [u.role];
                            return userRoles.some(r => r === roleName);
                        });
                        console.log('Role users found:', roleUsers);
                        stepApprovers = stepApprovers.concat(roleUsers);
                    }

                    console.log('Step Approvers:', stepApprovers);

                    // Add to options with step info (only current step)
                    stepApprovers.forEach(user => {
                        options.push({
                            id: user.id,
                            name: user.name,
                            role: user.role, 
                            step_number: currentStep.step_number,
                            step_role: currentStep.approver_role ? currentStep.approver_role.name : currentStep.required_approver_type
                        });
                    });

                    console.log('Final options:', options);

                    // Sort by name
                    return options.sort((a, b) => a.name.localeCompare(b.name));
                },

                init() {
                    this.resetForm();
                    this.fetchCurrentUser();
                    this.fetchLeaveRequests();
                    this.fetchMasterData();
                },

                async fetchCurrentUser() {
                    try {
                        const headers = this.getAuthHeaders();
                        const response = await fetch(`${baseApiUrl}/api/user`, { headers });
                        if (!response.ok) throw new Error('Failed to fetch user');
                        const data = await response.json();
                        // UserResource returns role as an array of strings
                        this.userRoles = data.data.role || [];
                        // console.log('Fetched User Roles:', this.userRoles);
                    } catch (error) {
                        console.error('Error fetching user:', error);
                    }
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
                            this.showToast(error.response.data.meta.message || 'An unexpected error occurred.', 'error');
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
                        this.workflows = data.data.workflows;
                        this.employeeEntitlements = data.data.employee_entitlements;
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
                    showModal('create_edit_modal');
                },
                openEditModal(req) {
                    // Prevent editing approved requests
                    if (req.current_status === 'Approved' || req.current_status === 'Rejected') {
                        this.showToast("Cannot edit approved leave requests. Approved requests are final.", 'warning');
                        return;
                    }
                    
                    this.resetForm();
                    this.editMode = true;
                    this.formData = {
                        id: req.id,
                        user_id: req.user.id,
                        leave_type_id: req.leave_type.id,
                        workflow_id: req.workflow_id,
                        start_date: this.formatDate(req.start_date),
                        end_date: this.formatDate(req.end_date),
                        leave_period: req.leave_period,
                        reason: req.reason,
                        current_status: req.current_status,
                        supporting_document_path: req.supporting_attachment_path
                    };
                    showModal('create_edit_modal');
                },
                openDetailsModal(req) {
                    this.selectedRequest = req;
                    showModal('details_modal');
                },
                openApprovalModal(req) {
                    this.selectedRequest = req;
                    this.approvalData = { action: 'Approved', comments: '', approver_id: '', signature: '' };
                    showModal('approval_modal');
                    this.setupSignaturePad();
                },
                closeModal(id) {
                    hideModal(id);
                },
                resetForm() {
                    this.formData = { id: null, user_id: '', leave_type_id: '', workflow_id: '', start_date: '', end_date: '', leave_period: 'full_day', reason: '', current_status: 'Draft', supporting_document: null };
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
                },

                // --- SIGNATURE PAD ---
                // --- SIGNATURE PAD ---
                signaturePad: null,
                isFullScreen: false,
                isLandscape: false,
                
                setupSignaturePad() {
                    this.$nextTick(() => {
                        const canvas = document.getElementById('signature-pad');
                        if (!canvas) return;
                        
                        // Destroy previous instance if exists to avoid duplicates
                        if (this.signaturePad) {
                            this.signaturePad.off();
                            this.signaturePad = null;
                        }

                        // Initialize SignaturePad
                        this.signaturePad = new SignaturePad(canvas, {
                            backgroundColor: 'rgba(255, 255, 255, 0)', // Transparent
                            penColor: 'rgb(0, 0, 0)',
                            velocityFilterWeight: 0.7,
                            minWidth: 0.6,
                            maxWidth: 1.8,
                            throttle: 26,
                            minDistance: 3,
                        });

                        // Auto full screen on mobile
                        if (window.innerWidth < 768) {
                            this.toggleFullScreen(true);
                        } else {
                            this.resizeCanvas();
                        }

                        // Update data model on end stroke
                        this.signaturePad.addEventListener("endStroke", () => {
                            if (!this.signaturePad.isEmpty()) {
                                this.approvalData.signature = this.signaturePad.toDataURL();
                            }
                        });
                        
                        // Handle window resize
                        window.addEventListener("resize", () => {
                            this.resizeCanvas();
                        });
                    });
                },
                
                resizeCanvas() {
                    const canvas = document.getElementById('signature-pad');
                    if (!canvas || !this.signaturePad) return;
                    
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    
                    // Store data as vector points to avoid distortion
                    const data = this.signaturePad.toData();
                    
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                    
                    this.signaturePad.clear(); // This is necessary after resizing
                    
                    // Restore data
                    if (data) {
                        this.signaturePad.fromData(data);
                    }
                },
                
                toggleFullScreen(forceState = null) {
                    if (forceState !== null) {
                        this.isFullScreen = forceState;
                    } else {
                        this.isFullScreen = !this.isFullScreen;
                    }

                    // Check for mobile portrait to force landscape
                    if (this.isFullScreen && window.innerWidth < 768 && window.innerHeight > window.innerWidth) {
                        this.isLandscape = true;
                    } else {
                        this.isLandscape = false;
                    }

                    this.$nextTick(() => {
                        this.resizeCanvas();
                    });
                },

                clearSignature() {
                    if (this.signaturePad) {
                        this.signaturePad.clear();
                        this.approvalData.signature = '';
                    }
                }
            }
        }
    </script>

    @endpush