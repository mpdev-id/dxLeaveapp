@extends('template.auth')

@section('content')
<div x-data="registrationForm('{{ config('app.base_api') }}')" x-init="init()">
    <h2 class="text-2xl font-bold text-center mb-4">Create Your Account</h2>

    <!-- General Error/Success Message -->
    <div x-show="message" role="alert" :class="success ? 'alert alert-success' : 'alert alert-error'" class="mb-4" style="display: none;">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span x-text="message"></span>
    </div>

    <form @submit.prevent="submitForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Name --}}
            <div class="form-control">
                <label class="label" for="name"><span class="label-text">Full Name</span></label>
                <input type="text" id="name" x-model="formData.name" placeholder="Your Full Name" class="input input-bordered w-full" required />
                <div x-show="errors.name" x-text="errors.name ? errors.name[0] : ''" class="text-red-500 text-xs mt-1"></div>
            </div>

            {{-- Employee Code --}}
            <div class="form-control">
                <label class="label" for="employee_code"><span class="label-text">Employee Code</span></label>
                <input type="text" id="employee_code" x-model="formData.employee_code" placeholder="e.g., EMP001" class="input input-bordered w-full" required />
                <div x-show="errors.employee_code" x-text="errors.employee_code ? errors.employee_code[0] : ''" class="text-red-500 text-xs mt-1"></div>
            </div>

            {{-- Email --}}
            <div class="form-control">
                <label class="label" for="email"><span class="label-text">Email</span></label>
                <input type="email" id="email" x-model="formData.email" placeholder="your.email@example.com" class="input input-bordered w-full" required />
                <div x-show="errors.email" x-text="errors.email ? errors.email[0] : ''" class="text-red-500 text-xs mt-1"></div>
            </div>

            {{-- Phone Number --}}
            <div class="form-control">
                <label class="label" for="phone_number"><span class="label-text">Phone Number</span></label>
                <input type="tel" id="phone_number" x-model="formData.phone_number" placeholder="e.g., 08123456789" class="input input-bordered w-full" required />
                <div x-show="errors.phone_number" x-text="errors.phone_number ? errors.phone_number[0] : ''" class="text-red-500 text-xs mt-1"></div>
            </div>

            {{-- Password --}}
            <div class="form-control">
                <label class="label" for="password"><span class="label-text">Password</span></label>
                <input type="password" id="password" x-model="formData.password" placeholder="Enter Password" class="input input-bordered w-full" required />
                <div x-show="errors.password" x-text="errors.password ? errors.password[0] : ''" class="text-red-500 text-xs mt-1"></div>
            </div>

            {{-- Password Confirmation --}}
            <div class="form-control">
                <label class="label" for="password_confirmation"><span class="label-text">Confirm Password</span></label>
                <input type="password" id="password_confirmation" x-model="formData.password_confirmation" placeholder="Confirm Password" class="input input-bordered w-full" required />
            </div>

            {{-- Department --}}
            <div class="form-control">
                <label class="label" for="department_id"><span class="label-text">Department</span></label>
                <select id="department_id" x-model="formData.department_id" class="select select-bordered w-full">
                    <option value="" disabled>Select a department</option>
                    <template x-for="dept in departments" :key="dept.id">
                        <option :value="dept.id" x-text="dept.name"></option>
                    </template>
                </select>
                <div x-show="errors.department_id" x-text="errors.department_id ? errors.department_id[0] : ''" class="text-red-500 text-xs mt-1"></div>
            </div>

            {{-- Hire Date --}}
            <div class="form-control">
                <label class="label" for="hire_date"><span class="label-text">Hire Date</span></label>
                <input type="date" id="hire_date" x-model="formData.hire_date" class="input input-bordered w-full" />
                <div x-show="errors.hire_date" x-text="errors.hire_date ? errors.hire_date[0] : ''" class="text-red-500 text-xs mt-1"></div>
            </div>

            {{-- Status --}}
            <div class="form-control md:col-span-2">
                <label class="label" for="status"><span class="label-text">Status</span></label>
                <select id="status" x-model="formData.status" class="select select-bordered w-full">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <div x-show="errors.status" x-text="errors.status ? errors.status[0] : ''" class="text-red-500 text-xs mt-1"></div>
            </div>
        </div>

        <div class="form-control mt-6">
            <button type="submit" class="btn btn-primary" :disabled="loading">
                <span x-show="loading" class="loading loading-spinner"></span>
                <span x-text="loading ? 'Processing...' : 'Register'"></span>
            </button>
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="link">Already have an account? Login</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
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
                        if (response.status === 422) {
                            this.errors = data.errors;
                            this.message = 'Please check the form for errors.';
                        } else {
                            this.message = data.message || 'An unknown error occurred.';
                        }
                        throw new Error('Registration failed');
                    }
                    
                    this.message = 'Registration successful! Please log in.';
                    this.success = true;
                    // Optionally redirect after a delay
                    setTimeout(() => {
                        window.location.href = '{{ route("login") }}';
                    }, 2000);

                } catch (error) {
                    console.error(error.message);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
