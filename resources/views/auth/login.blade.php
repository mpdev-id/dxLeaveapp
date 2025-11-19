@extends('template.auth')

@section('content')
    <div x-data="loginForm('{{ config('app.base_api') }}')" x-init="init()">
        
        <!-- Session Status -->
        @if (session('status'))
            <div role="alert" class="alert alert-success mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Error Message -->
        <div x-show="errorMessage" role="alert" class="alert alert-error mb-4" style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span x-text="errorMessage"></span>
        </div>

        <form @submit.prevent="submitForm">
            @csrf
            <div class="form-control">
                <label class="label" for="identifier">
                    <span class="label-text">Email or Username</span>
                </label>
                <input type="text" id="identifier" x-model="formData.identifier" placeholder="email or username" class="input input-bordered w-full" :class="{'input-error': errors.identifier}" required />
                <div x-show="errors.identifier" class="text-error text-sm mt-1" x-text="errors.identifier ? errors.identifier[0] : ''"></div>
            </div>
            
            <div class="form-control">
                <label class="label" for="password">
                    <span class="label-text">Password</span>
                </label>
                <input type="password" id="password" x-model="formData.password" placeholder="password" class="input input-bordered w-full" :class="{'input-error': errors.password}" required />
                <div x-show="errors.password" class="text-error text-sm mt-1" x-text="errors.password ? errors.password[0] : ''"></div>
            </div>

            <div class="flex items-center justify-between mt-4">
                <div class="form-control">
                    <label class="label cursor-pointer">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary" />
                        <span class="label-text ml-2">Remember me</span>
                    </label>
                </div>
                <label class="label">
                    <a href="{{ route('password.request') }}" class="label-text-alt link link-hover">Forgot password?</a>
                </label>
            </div>
            
            <div class="form-control mt-6">
                <button type="submit" class="btn btn-primary" :disabled="loading">
                    <span x-show="loading" class="loading loading-spinner"></span>
                    <span x-text="loading ? 'Processing...' : 'Login'"></span>
                </button>
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('register') }}" class="link">Don't have an account? Register</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function loginForm(baseApiUrl) {
        return {
            formData: {
                identifier: '',
                password: ''
            },
            loading: false,
            errorMessage: '',
            errors: {},
            init() {
                if (localStorage.getItem('authToken')) {
                    window.location.href = '/admin/users';
                }
            },
            async submitForm() {
                this.loading = true;
                this.errorMessage = '';
                this.errors = {};
                try {
                    const response = await fetch(`${baseApiUrl}/login`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && data.data && data.data.errors) {
                            this.errors = data.data.errors;
                        } else {
                            this.errorMessage = data.message || data.meta.message || 'An unknown error occurred.';
                        }
                        return; // Stop execution
                    }

                    if (data.data && data.data.access_token) {
                        localStorage.setItem('authToken', data.data.access_token);
                        window.location.href = '/admin/users';
                    } else {
                        this.errorMessage = data.meta?.message || data.message || 'Login successful, but no token was provided.';
                    }

                } catch (error) {
                    this.errorMessage = error.message || 'Failed to connect to the server.';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
