@extends('template.member')

@section('title', 'Edit Leave Request')

@section('content')
<div x-data="editLeave('{{ config('app.base_api') }}', '{{ $id }}')" x-init="init()" class="pb-20">
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('member.leaves.index') }}" class="btn btn-ghost btn-circle btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </a>
        <h1 class="text-2xl font-bold">Edit Request</h1>
    </div>
    
    <div class="grid grid-cols-2 gap-3 mb-6">
        <template x-for="balance in balances" :key="balance.leave_type_id">
            <div class="stat bg-base-100 shadow-sm rounded-box p-3 border border-base-200">
                <div class="stat-title text-[10px] font-bold uppercase tracking-wider truncate" x-text="balance.leave_type_name"></div>
                <div class="stat-value text-primary text-xl" x-text="balance.remaining_days"></div>
                <div class="stat-desc text-[10px]">Days Remaining</div>
            </div>
        </template>
    </div>

    <template x-if="loading">
        <div class="flex justify-center py-12">
            <span class="loading loading-spinner loading-lg"></span>
        </div>
    </template>

    <form x-show="!loading" @submit.prevent="submitForm" class="space-y-6">
        {{-- Leave Period --}}
        <div class="form-control w-full">
            <label class="label">
                <span class="label-text font-semibold">Period</span>
            </label>
            <select x-model="formData.leave_period" class="select select-bordered w-full" :class="{'select-error': errors.leave_period}" @change="calculateDuration">
                <option value="full_day">Full Day</option>
                <option value="half_day_morning">Half Day (Morning)</option>
                <option value="half_day_afternoon">Half Day (Afternoon)</option>
            </select>
            <label class="label" x-show="errors.leave_period">
                <span class="label-text-alt text-error" x-text="errors.leave_period"></span>
            </label>
        </div>
        
        {{-- Leave Type --}}
        <div class="form-control w-full">
            <label class="label">
                <span class="label-text font-semibold">Leave Type</span>
            </label>
            <select x-model="formData.leave_type_id" class="select select-bordered w-full" :class="{'select-error': errors.leave_type_id}" required>
                <option value="" disabled selected>Select leave type</option>
                <template x-for="type in leaveTypes" :key="type.id">
                    <option :value="type.id" x-text="type.name"></option>
                </template>
            </select>
            <label class="label" x-show="errors.leave_type_id">
                <span class="label-text-alt text-error" x-text="errors.leave_type_id"></span>
            </label>
        </div>

        {{-- Workflow --}}
        <div class="form-control w-full">
            <label class="label">
                <span class="label-text font-semibold">Leave Group</span>
            </label>
            <select x-model="formData.workflow_id" class="select select-bordered w-full" :class="{'select-error': errors.workflow_id}" required>
                <option value="" disabled selected>Select leave group</option>
                <template x-for="wf in workflows" :key="wf.id">
                    <option :value="wf.id" x-text="wf.name"></option>
                </template>
            </select>
            <label class="label" x-show="errors.workflow_id">
                <span class="label-text-alt text-error" x-text="errors.workflow_id"></span>
            </label>
        </div>

        {{-- Dates --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-semibold">Start Date</span>
                </label>
                <input type="date" x-model="formData.start_date" class="input input-bordered w-full" :class="{'input-error': errors.start_date}" required @change="calculateDuration" />
                <label class="label" x-show="errors.start_date">
                    <span class="label-text-alt text-error" x-text="errors.start_date"></span>
                </label>
            </div>

            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-semibold">End Date</span>
                </label>
                <input type="date" x-model="formData.end_date" class="input input-bordered w-full" :class="{'input-error': errors.end_date}" required @change="calculateDuration" :disabled="formData.leave_period !== 'full_day'" />
                <label class="label" x-show="errors.end_date">
                    <span class="label-text-alt text-error" x-text="errors.end_date"></span>
                </label>
            </div>
        </div>


        {{-- Duration Display --}}
        <div class="alert alert-info shadow-sm" x-show="duration > 0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Total Duration: <span class="font-bold" x-text="duration + ' Days'"></span></span>
        </div>

        {{-- Reason --}}
        <div class="form-control w-full">
            <label class="label">
                <span class="label-text font-semibold">Reason</span>
            </label>
            <textarea x-model="formData.reason" class="textarea textarea-bordered h-24 w-full p-4" :class="{'textarea-error': errors.reason}" placeholder="Please describe the reason for your leave..." required></textarea>
            <label class="label" x-show="errors.reason">
                <span class="label-text-alt text-error" x-text="errors.reason"></span>
            </label>
        </div>

        {{-- Attachment --}}
        <div class="form-control w-full" x-show="showAttachment" x-transition>
            <label class="label">
                <span class="label-text font-semibold">
                    Attachment 
                    <span x-text="isSickLeave ? '(Required)' : '(Optional)'" :class="isSickLeave ? 'text-error' : ''"></span>
                </span>
            </label>
            <input type="file" @change="handleFileUpload" class="file-input file-input-bordered w-full" :class="{'file-input-error': errors.supporting_document}" accept=".jpg,.jpeg,.png,.pdf" />
            <label class="label">
                <span class="label-text-alt">Max 2MB (JPG, PNG, PDF)</span>
                <span class="label-text-alt text-error" x-show="errors.supporting_document" x-text="errors.supporting_document"></span>
            </label>
            <div x-show="existingAttachment" class="text-xs mt-1">
                Current file: <a :href="existingAttachment" target="_blank" class="link link-primary">View Attachment</a>
            </div>
        </div>

        {{-- Signature --}}
        <div class="form-control w-full">
            <label class="label">
                <span class="label-text font-semibold">Signature</span>
            </label>
            
            <div class="flex flex-col gap-4">
                <!-- Option to use saved signature -->
                <div x-show="userSignature" class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" x-model="useSavedSignature" class="checkbox checkbox-primary" />
                        <span class="label-text">Use my saved signature</span>
                    </label>
                    <div x-show="useSavedSignature" class="mt-2 p-4 border rounded-lg bg-base-100 w-fit">
                        <img :src="userSignature" alt="Saved Signature" class="h-20 object-contain">
                    </div>
                </div>

                <!-- Signature Pad -->
                <div x-show="!useSavedSignature" class="w-full flex flex-col items-center">
                    <div class="w-full max-w-sm aspect-square border-2 border-dashed border-gray-300 rounded-lg bg-white relative">
                        <canvas id="signature-pad" class="absolute inset-0 w-full h-full touch-none"></canvas>
                    </div>
                    <div class="w-full max-w-sm flex justify-between mt-2">
                        <span class="text-xs text-gray-500">Sign above</span>
                        <div class="flex gap-2">
                            <button type="button" @click="undoSignature" class="btn btn-xs btn-ghost" :disabled="historyStep < 0" title="Undo">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                            </button>
                            <button type="button" @click="redoSignature" class="btn btn-xs btn-ghost" :disabled="historyStep >= history.length - 1" title="Redo">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" /></svg>
                            </button>
                            <button type="button" @click="clearSignature" class="btn btn-xs btn-ghost text-error" title="Clear">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <label class="label" x-show="errors.signature">
                <span class="label-text-alt text-error" x-text="errors.signature"></span>
            </label>
        </div>

        {{-- Submit Buttons --}}
        <div class="pt-4 grid grid-cols-2 gap-4">
            <button type="button" @click="submitForm('draft')" class="btn btn-outline btn-secondary w-full" :disabled="submitting">
                <span x-show="submitting && action === 'draft'" class="loading loading-spinner"></span>
                <span x-text="submitting && action === 'draft' ? 'Saving...' : 'Update Draft'"></span>
            </button>
            <button type="submit" class="btn btn-primary w-full" :disabled="submitting">
                <span x-show="submitting && action === 'submit'" class="loading loading-spinner"></span>
                <span x-text="submitting && action === 'submit' ? 'Submitting...' : 'Submit Request'"></span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    function editLeave(baseApiUrl, requestId) {
        return {
            leaveTypes: [],
            workflows: [],
            publicHolidays: [],
            balances: [],
            userSignature: null,
            useSavedSignature: false,
            userSignature: null,
            useSavedSignature: false,
            signaturePad: null,
            history: [],
            historyStep: -1,
            formData: {
                leave_type_id: '',
                workflow_id: '',
                start_date: '',
                end_date: '',
                leave_period: 'full_day',
                reason: '',
                supporting_document: null
            },
            existingAttachment: null,
            duration: 0,
            errors: {},
            submitting: false,
            loading: true,
            action: 'submit', // 'submit' or 'draft'
            token: localStorage.getItem('authToken'),

            async init() {
                if (!this.token) return;
                await this.fetchMasterData();
                await this.fetchRequestData();
                this.loading = false;

                // Initialize Signature Pad
                this.$nextTick(() => {
                    const canvas = document.getElementById('signature-pad');
                    if (canvas) {
                        this.signaturePad = new SignaturePad(canvas, {
                            backgroundColor: 'rgba(255, 255, 255, 0)'
                        });

                        this.signaturePad.addEventListener("endStroke", () => {
                            this.saveHistory();
                        });
                        
                        // Initial resize
                        this.resizeCanvas();
                        
                        // Handle window resize
                        window.addEventListener("resize", () => this.resizeCanvas());
                    }
                });

                // Watch for visibility change
                this.$watch('useSavedSignature', (value) => {
                    if (!value) {
                        setTimeout(() => this.resizeCanvas(), 50);
                    }
                });
            },

            get isSickLeave() {
                if (!this.formData.leave_type_id) return false;
                const type = this.leaveTypes.find(t => t.id == this.formData.leave_type_id);
                if (!type) return false;
                const name = type.name.toLowerCase();
                return name.includes('sick') || name.includes('sakit');
            },

            get showAttachment() {
                if (!this.formData.leave_type_id) return false;
                const type = this.leaveTypes.find(t => t.id == this.formData.leave_type_id);
                if (!type) return false;
                const name = type.name.toLowerCase();
                return name.includes('sick') || name.includes('sakit') || name.includes('special') || name.includes('khusus');
            },
                try {
                    const [masterResponse, balanceResponse, userResponse] = await Promise.all([
                        fetch(`${baseApiUrl}/master-data`, {
                            headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                        }),
                        fetch(`${baseApiUrl}/user/leave-balances`, {
                            headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                        }),
                        fetch(`${baseApiUrl}/user`, {
                            headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                        })
                    ]);

                    const masterData = await masterResponse.json();
                    const balanceData = await balanceResponse.json();
                    const userData = await userResponse.json();

                    if (masterResponse.ok && balanceData.data) {
                        const allLeaveTypes = masterData.data.leave_types;
                        this.workflows = masterData.data.workflows || [];
                        this.publicHolidays = masterData.data.public_holidays || [];
                        const userBalances = balanceData.data;
                        this.balances = userBalances;

                        const availableLeaveTypeIds = userBalances.map(b => b.leave_type_id);
                        this.leaveTypes = allLeaveTypes.filter(type => availableLeaveTypeIds.includes(type.id));

                        // Check for saved signature
                        if (userData.data && userData.data.signature_url) {
                            this.userSignature = userData.data.signature_url;
                            // Default to using saved signature if available? Or let user choose?
                            // Let's default to false to let them see options, or true for convenience.
                            // If they already signed this request, we might want to show that?
                            // But edit usually means re-signing or keeping existing.
                            // For simplicity, let's start with false unless they select it.
                        }
                    }
                } catch (e) {
                    console.error('Error fetching master data:', e);
                }
            },

            async fetchRequestData() {
                try {
                    const response = await fetch(`${baseApiUrl}/leave-requests/${requestId}`, {
                        headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                    });
                    const data = await response.json();

                    if (response.ok) {
                        const req = data.data;
                        this.formData.leave_type_id = req.leave_type_id;
                        this.formData.workflow_id = req.workflow_id; // Ensure backend sends this
                        this.formData.start_date = req.start_date;
                        this.formData.end_date = req.end_date;
                        this.formData.leave_period = req.leave_period;
                        this.formData.reason = req.reason;
                        // Handle attachment URL if available
                        // this.existingAttachment = req.attachment_url; 
                        
                        this.calculateDuration();
                    } else {
                        Swal.fire('Error', 'Failed to load request data', 'error');
                        window.location.href = '{{ route("member.leaves.index") }}';
                    }
                } catch (e) {
                    console.error('Error fetching request:', e);
                    Swal.fire('Error', 'Network error', 'error');
                }
            },

            calculateDuration() {
                // Same logic as create
                if (this.formData.leave_period !== 'full_day' && this.formData.start_date) {
                    this.formData.end_date = this.formData.start_date;
                }

                if (!this.formData.start_date || !this.formData.end_date) {
                    this.duration = 0;
                    return;
                }

                const start = new Date(this.formData.start_date);
                const end = new Date(this.formData.end_date);

                if (end < start) {
                    this.errors.end_date = 'End date cannot be before start date';
                    this.duration = 0;
                    return;
                } else {
                    this.errors.end_date = '';
                }

                if (this.formData.leave_period !== 'full_day') {
                    if (this.formData.start_date !== this.formData.end_date) {
                        this.errors.end_date = 'Half day leave must be on the same day';
                        this.duration = 0;
                    } else {
                        const day = start.getDay();
                        if (day === 0 || day === 6) {
                            this.errors.end_date = 'Cannot apply for leave on weekends';
                            this.duration = 0;
                        } else {
                            this.duration = 0.5;
                            this.errors.end_date = '';
                        }
                    }
                } else {
                    // Calculate working days (exclude weekends and public holidays)
                    let count = 0;
                    let curDate = new Date(start);
                    
                    // Format holidays to YYYY-MM-DD for easy comparison
                    const holidayDates = this.publicHolidays.map(h => h.date);

                    while (curDate <= end) {
                        const dayOfWeek = curDate.getDay();
                        const dateString = curDate.toISOString().split('T')[0];

                        // Check if weekend (0=Sun, 6=Sat) OR public holiday
                        if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidayDates.includes(dateString)) {
                            count++;
                        }
                        curDate.setDate(curDate.getDate() + 1);
                    }
                    this.duration = count;
                }
            },

            handleFileUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    if (file.size > 2048 * 1024) {
                        Swal.fire('Error', 'File size exceeds 2MB limit', 'error');
                        event.target.value = '';
                        this.formData.supporting_document = null;
                        return;
                    }
                    this.formData.supporting_document = file;
                }
            },

            saveHistory() {
                this.history = this.history.slice(0, this.historyStep + 1);
                // Deep copy to avoid reference issues
                this.history.push(JSON.parse(JSON.stringify(this.signaturePad.toData())));
                this.historyStep++;
            },

            undoSignature() {
                if (this.historyStep >= 0) {
                    this.historyStep--;
                    if (this.historyStep >= 0) {
                        this.signaturePad.fromData(this.history[this.historyStep]);
                    } else {
                        this.signaturePad.clear();
                    }
                }
            },

            redoSignature() {
                if (this.historyStep < this.history.length - 1) {
                    this.historyStep++;
                    this.signaturePad.fromData(this.history[this.historyStep]);
                }
            },

            clearSignature() {
                if (this.signaturePad) {
                    this.signaturePad.clear();
                    this.saveHistory();
                }
            },

            async submitForm(actionType = 'submit') {
                this.submitting = true;
                this.action = actionType;
                this.errors = {};

                if (this.formData.leave_period !== 'full_day' && this.formData.start_date !== this.formData.end_date) {
                    this.errors.end_date = 'Half day leave must be on the same day';
                    this.submitting = false;
                    return;
                }

                const formData = new FormData();
                formData.append('_method', 'PUT'); // Important for Laravel to treat this as PUT
                formData.append('leave_type_id', this.formData.leave_type_id);
                formData.append('workflow_id', this.formData.workflow_id);
                formData.append('start_date', this.formData.start_date);
                formData.append('end_date', this.formData.end_date);
                formData.append('leave_period', this.formData.leave_period);
                formData.append('reason', this.formData.reason);
                
                // Set status based on action
                if (actionType === 'draft') {
                    formData.append('current_status', 'Draft');
                } else {
                    formData.append('current_status', 'Pending');
                }

                if (this.formData.supporting_document) {
                    formData.append('supporting_document', this.formData.supporting_document);
                } else if (this.isSickLeave && actionType === 'submit' && !this.existingAttachment) {
                    // Only require if no existing attachment
                    this.errors.supporting_document = 'Attachment is required for Sick Leave';
                    this.submitting = false;
                    return;
                }

                // Handle Signature
                if (this.useSavedSignature) {
                    formData.append('use_saved_signature', '1');
                } else {
                    if (this.signaturePad && !this.signaturePad.isEmpty()) {
                        formData.append('signature', this.signaturePad.toDataURL());
                    } else {
                        // If submitting (not draft), signature might be required? 
                        if (actionType === 'submit') {
                            // If user already has a signature on file for this request, maybe they don't need to re-sign?
                            // But the UI implies a new signature or saved one.
                            // Let's require it if they don't use saved.
                            this.errors.signature = 'Signature is required';
                            this.submitting = false;
                            return;
                        }
                    }
                }

                try {
                    // We use POST with _method=PUT to support file uploads
                    const response = await fetch(`${baseApiUrl}/leave-requests/${requestId}`, {
                        method: 'POST', 
                        headers: { 
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.data.errors;
                        } else {
                            Swal.fire('Error', data.meta?.message || 'Failed to update request', 'error');
                        }
                        return;
                    }

                    Swal.fire({
                        title: 'Success!',
                        text: actionType === 'draft' ? 'Request updated as Draft.' : 'Request submitted successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '{{ route("member.leaves.index") }}';
                    });

                } catch (e) {
                    console.error('Error updating form:', e);
                    Swal.fire('Error', 'An unexpected error occurred', 'error');
                } finally {
                    this.submitting = false;
                }
            }
        }
    }
</script>
@endpush
