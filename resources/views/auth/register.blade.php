@extends('template.auth')

@section('content')
<div x-data="registrationForm('{{ config('app.base_api') }}')" x-init="init()" class="w-full max-w-2xl mx-auto">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title justify-center text-2xl mb-4">Create Your Account</h2>

            <!-- General Error/Success Message -->
            <div x-show="message" role="alert" :class="success ? 'alert alert-success' : 'alert alert-error'" class="mb-4" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span x-text="message"></span>
            </div>

            <form @submit.prevent="submitForm" class="space-y-4">
                @csrf
                <div class="flex flex-col w-full gap-4">
                    {{-- Full Name --}}
                    <div class="form-control">
                        <label class="label" for="name"><span class="label-text flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>Full Name</span></label>
                        <input type="text" id="name" x-model="formData.name" placeholder="Your Full Name" class="input input-bordered w-full" :class="{'input-error': errors.name}" required />
                        <div x-show="errors.name" x-text="errors.name ? errors.name[0] : ''" class="text-error text-sm mt-1"></div>
                    </div>

                    {{-- Employee Code --}}
                    <div class="form-control">
                        <label class="label" for="employee_code"><span class="label-text flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>Employee Code</span></label>
                        <input type="text" id="employee_code" x-model="formData.employee_code" placeholder="e.g., EMP001" class="input input-bordered w-full" :class="{'input-error': errors.employee_code}" required />
                        <div x-show="errors.employee_code" x-text="errors.employee_code ? errors.employee_code[0] : ''" class="text-error text-sm mt-1"></div>
                    </div>

                    {{-- Email --}}
                    <div class="form-control">
                        <label class="label" for="email"><span class="label-text flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>Email</span></label>
                        <input type="email" id="email" x-model="formData.email" placeholder="your.email@example.com" class="input input-bordered w-full" :class="{'input-error': errors.email}" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,63}$" />
                        <div x-show="errors.email" x-text="errors.email ? errors.email[0] : ''" class="text-error text-sm mt-1"></div>
                    </div>

                    {{-- Phone Number --}}
                    <div class="form-control">
                        <label class="label" for="phone_number"><span class="label-text flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>Phone Number</span></label>
                        <input type="tel" id="phone_number" x-model="formData.phone_number" placeholder="e.g., 08123456789" class="input input-bordered w-full" :class="{'input-error': errors.phone_number}" required />
                        <div x-show="errors.phone_number" x-text="errors.phone_number ? errors.phone_number[0] : ''" class="text-error text-sm mt-1"></div>
                      
                    </div>

                    {{-- Password --}}
                    <div class="form-control">
                        <label class="label" for="password"><span class="label-text flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>Password</span></label>
                        <input type="password" id="password" x-model="formData.password" placeholder="Enter Password" class="input input-bordered w-full" :class="{'input-error': errors.password}" required />
                        <div x-show="errors.password" x-text="errors.password ? errors.password[0] : ''" class="text-error text-sm mt-1"></div>
                    </div>

                    {{-- Password Confirmation --}}
                    <div class="form-control">
                        <label class="label" for="password_confirmation"><span class="label-text flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>Confirm Password</span></label>
                        <input type="password" id="password_confirmation" x-model="formData.password_confirmation" placeholder="Confirm Password" class="input input-bordered w-full" required />
                    </div>

                    {{-- Department --}}
                    <div class="form-control">
                        <label class="label" for="department_id"><span class="label-text flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>Department</span></label>
                        <select id="department_id" x-model="formData.department_id" class="select select-bordered w-full" :class="{'select-error': errors.department_id}">
                            <option value="" disabled>Select a department</option>
                            <template x-for="dept in departments" :key="dept.id">
                                <option :value="dept.id" x-text="dept.name"></option>
                            </template>
                        </select>
                        <div x-show="errors.department_id" x-text="errors.department_id ? errors.department_id[0] : ''" class="text-error text-sm mt-1"></div>
                    </div>

                    {{-- Hire Date --}}
                    <div class="form-control">
                        <label class="label" for="hire_date"><span class="label-text flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>Hire Date</span></label>
                        <input type="date" id="hire_date" x-model="formData.hire_date" class="input input-bordered w-full" :class="{'input-error': errors.hire_date}" />
                        <div x-show="errors.hire_date" x-text="errors.hire_date ? errors.hire_date[0] : ''" class="text-error text-sm mt-1"></div>
                    </div>

                    {{-- Status --}}
                    <div class="form-control md:col-span-2 hidden" aria-disabled="true">
                        <label class="label" for="status"><span class="label-text flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Status</span></label>
                        <select id="status" x-model="formData.status" class="select select-bordered w-full" :class="{'select-error': errors.status}">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div x-show="errors.status" x-text="errors.status ? errors.status[0] : ''" class="text-error text-sm mt-1"></div>
                    </div>
                </div>

                <div class="card-actions justify-center mt-6">
                    <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                        <span x-show="loading" class="loading loading-spinner"></span>
                        <span x-text="loading ? 'Processing...' : 'Register'"></span>
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="link">Already have an account? Login</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const phoneInput = document.getElementById('phone_number');
    phoneInput.addEventListener('input', function(e) {
        const val = e.target.value;
        if (val.startsWith('08')) {
            e.target.value = '628' + val.slice(2);
        }
    });

    function registrationForm(baseApiUrl) {
        return {
            formData: {
                name: '',
                employee_code: '',
                email: '',
                phone_number: '',
                password: '',
                password_confirmation: '',
                department_id: '',
                hire_date: '',
                status: 'active',
            },
            departments: [],
            loading: false,
            message: '',
            success: false,
            errors: {},
            init() {
                // Fetch departments
                fetch(`${baseApiUrl}/departments`)
                    .then(res => res.json())
                    .then(data => {
                        if(data.data) {
                            this.departments = data.data.data;
                        }
                    })
                    .catch(err => console.error('Failed to load departments', err));
            },
            async submitForm() {
                this.loading = true;
                this.message = '';
                this.errors = {};
                this.success = false;

                try {
                    const response = await fetch(`${baseApiUrl}/register`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.success = false;
                        if (response.status === 422) {
                            this.errors = data.data?.errors || data.errors || {};
                            this.message = data.meta?.message || data.message || 'Please check the form for errors.';
                        } else {
                            this.message = data.meta?.message || data.message || 'An unknown error occurred.';
                        }
                        return; // Stop execution
                    }
                    
                    this.message = data.meta?.message || data.message || 'Registration successful! Please log in.';
                    this.success = true;
                    this.errors = {};
                    this.formData = { name: '', employee_code: '', email: '', phone_number: '', password: '', password_confirmation: '', department_id: '', hire_date: '', status: 'active' };

                    setTimeout(() => {
                        window.location.href = '{{ route("login") }}';
                    }, 2000);

                } catch (error) {
                    this.success = false;
                    this.message = 'Failed to connect to the server.';
                    console.error(error);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
