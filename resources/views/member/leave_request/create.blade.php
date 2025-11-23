@extends('template.member')

@section('title', 'New Leave Request')

@section('content')
<div x-data="createLeave('{{ config('app.base_api') }}')" x-init="init()" class="pb-20">
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('member.leaves.index') }}" class="btn btn-ghost btn-circle btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </a>
        <h1 class="text-2xl font-bold">New Request</h1>
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
        
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
                <input type="date" x-model="formData.end_date" class="input input-bordered w-full" :class="{'input-error': errors.end_date}" required @change="calculateDuration" />
                <label class="label" x-show="errors.end_date">
                    <span class="label-text-alt text-error" x-text="errors.end_date"></span>
                </label>
            </div>
        </div>

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
            <textarea x-model="formData.reason" class="textarea textarea-bordered h-24" :class="{'textarea-error': errors.reason}" placeholder="Please describe the reason for your leave..." required></textarea>
            <label class="label" x-show="errors.reason">
                <span class="label-text-alt text-error" x-text="errors.reason"></span>
            </label>
        </div>

        {{-- Attachment --}}
        <div class="form-control w-full">
            <label class="label">
                <span class="label-text font-semibold">Attachment (Optional)</span>
            </label>
            <input type="file" @change="handleFileUpload" class="file-input file-input-bordered w-full" accept=".jpg,.jpeg,.png,.pdf" />
            <label class="label">
                <span class="label-text-alt">Max 2MB (JPG, PNG, PDF)</span>
            </label>
        </div>

        {{-- Submit Button --}}
        <div class="pt-4">
            <button type="submit" class="btn btn-primary w-full" :disabled="submitting">
                <span x-show="submitting" class="loading loading-spinner"></span>
                <span x-text="submitting ? 'Submitting...' : 'Submit Request'"></span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function createLeave(baseApiUrl) {
        return {
            leaveTypes: [],
            formData: {
                leave_type_id: '',
                start_date: '',
                end_date: '',
                leave_period: 'full_day',
                reason: '',
                supporting_document: null
            },
            duration: 0,
            errors: {},
            submitting: false,
            token: localStorage.getItem('authToken'),

            async init() {
                if (!this.token) return;
                await this.fetchMasterData();
                
                // Set default dates to today
                const today = new Date().toISOString().split('T')[0];
                this.formData.start_date = today;
                this.formData.end_date = today;
                this.calculateDuration();
            },

            async fetchMasterData() {
                try {
                    // Fetch Master Data (Leave Types) AND User Balances
                    const [masterResponse, balanceResponse] = await Promise.all([
                        fetch(`${baseApiUrl}/master-data`, {
                            headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                        }),
                        fetch(`${baseApiUrl}/user/leave-balances`, {
                            headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                        })
                    ]);

                    const masterData = await masterResponse.json();
                    const balanceData = await balanceResponse.json();

                    if (masterResponse.ok && balanceResponse.ok) {
                        const allLeaveTypes = masterData.data.leave_types;
                        const userBalances = balanceData.data;

                        // Filter leave types: Only show if user has an entitlement (balance record) for it
                        // OR if the leave type doesn't require entitlement (logic depends on your system, assuming all need entitlement here)
                        // Actually, let's map the balances to leave types.
                        
                        // We only want to show leave types that the user has a balance for.
                        // userBalances is an array of objects like { leave_type_id: 1, leave_type_name: 'Annual', remaining_balance: 10, ... }
                        
                        const availableLeaveTypeIds = userBalances.map(b => b.leave_type_id);
                        
                        this.leaveTypes = allLeaveTypes.filter(type => availableLeaveTypeIds.includes(type.id));
                        
                        // Optional: Append balance info to the name for display?
                        // this.leaveTypes = this.leaveTypes.map(type => {
                        //     const bal = userBalances.find(b => b.leave_type_id === type.id);
                        //     return { ...type, name: `${type.name} (Bal: ${bal.remaining_balance})` };
                        // });
                    }
                } catch (e) {
                    console.error('Error fetching data:', e);
                    Swal.fire('Error', 'Failed to load leave types', 'error');
                }
            },

            calculateDuration() {
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
                    // Half day is always 0.5 days and must be same day
                    if (this.formData.start_date !== this.formData.end_date) {
                        this.errors.end_date = 'Half day leave must be on the same day';
                        this.duration = 0;
                    } else {
                        // Check if the single day is a weekend
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
                    // Calculate working days (exclude weekends)
                    let count = 0;
                    let curDate = new Date(start);
                    while (curDate <= end) {
                        const dayOfWeek = curDate.getDay();
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) { // 0 = Sunday, 6 = Saturday
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
                        event.target.value = ''; // Clear input
                        this.formData.supporting_document = null;
                        return;
                    }
                    this.formData.supporting_document = file;
                }
            },

            async submitForm() {
                this.submitting = true;
                this.errors = {};

                // Basic validation before sending
                if (this.formData.leave_period !== 'full_day' && this.formData.start_date !== this.formData.end_date) {
                    this.errors.end_date = 'Half day leave must be on the same day';
                    this.submitting = false;
                    return;
                }

                const formData = new FormData();
                formData.append('leave_type_id', this.formData.leave_type_id);
                formData.append('start_date', this.formData.start_date);
                formData.append('end_date', this.formData.end_date);
                formData.append('leave_period', this.formData.leave_period);
                formData.append('reason', this.formData.reason);
                if (this.formData.supporting_document) {
                    formData.append('supporting_document', this.formData.supporting_document);
                }

                try {
                    // First, create the request (it will be Draft by default in controller logic usually, but let's check controller)
                    // The controller sets 'current_status' => 'Draft' automatically in store method.
                    // Wait, do we want to submit immediately or draft?
                    // The user usually wants to submit. 
                    // The controller logic:
                    // store() -> creates with 'Draft'.
                    // To submit, we might need to update it to 'Pending' or the store method handles it?
                    // Looking at LeaveRequestController.php:
                    // store() sets 'current_status' => 'Draft'.
                    // update() handles status change to 'Pending'.
                    
                    // So we must:
                    // 1. POST to create (Draft)
                    // 2. PUT/PATCH to update status to 'Pending' (Submit)
                    
                    // Step 1: Create Draft
                    const createResponse = await fetch(`${baseApiUrl}/leave-requests`, {
                        method: 'POST',
                        headers: { 
                            'Authorization': `Bearer ${this.token}`,
                            'Accept': 'application/json'
                            // Content-Type not set for FormData
                        },
                        body: formData
                    });

                    const createData = await createResponse.json();

                    if (!createResponse.ok) {
                        if (createResponse.status === 422) {
                            this.errors = createData.data.errors;
                        } else {
                            Swal.fire('Error', createData.meta.message || 'Failed to create request', 'error');
                        }
                        return;
                    }

                    const leaveRequestId = createData.data.id;

                    // Step 2: Submit (Update status to Pending)
                    // We need to send JSON for this
                    const submitResponse = await fetch(`${baseApiUrl}/leave-requests/${leaveRequestId}`, {
                        method: 'PUT', // or PATCH
                        headers: { 
                            'Authorization': `Bearer ${this.token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            current_status: 'Pending',
                            _method: 'PUT' // Laravel sometimes needs this
                        })
                    });
                    
                    const submitData = await submitResponse.json();

                    if (submitResponse.ok) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Leave request submitted successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route("member.leaves.index") }}';
                        });
                    } else {
                         // If submission fails, warn user but they have a draft
                         Swal.fire('Warning', 'Request saved as Draft but failed to submit. Please check "My Leaves" to submit it.', 'warning');
                         window.location.href = '{{ route("member.leaves.index") }}';
                    }

                } catch (e) {
                    console.error('Error submitting form:', e);
                    Swal.fire('Error', 'An unexpected error occurred', 'error');
                } finally {
                    this.submitting = false;
                }
            }
        }
    }
</script>
@endpush
