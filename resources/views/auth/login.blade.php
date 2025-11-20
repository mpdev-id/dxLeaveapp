@extends('template.auth')

@section('content')
    <div x-data="loginForm('{{ config('app.base_api') }}')" x-init="init()" class="w-full max-w-md mx-auto">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title justify-center text-2xl mb-4">Login</h2>
                
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

                <form @submit.prevent="submitForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="identifier">
                            <span class="label-text">Email or Username</span>
                        </label>
                        <label class="input input-bordered flex items-center gap-2" :class="{'input-error': errors.identifier}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70"><path d="M2.5 3A1.5 1.5 0 0 0 1 4.5v.793c.026.009.051.02.076.032L7.674 8.51c.206.1.446.1.652 0l6.598-3.185A.755.755 0 0 1 15 5.293V4.5A1.5 1.5 0 0 0 13.5 3h-11Z" /><path d="M15 6.954 8.978 9.86a2.25 2.25 0 0 1-1.956 0L1 6.954V11.5A1.5 1.5 0 0 0 2.5 13h11a1.5 1.5 0 0 0 1.5-1.5V6.954Z" /></svg>
                            <input type="text" id="identifier" x-model="formData.identifier" class="grow" placeholder="Email or Username" required />
                        </label>
                        <div x-show="errors.identifier" class="text-error text-sm mt-1" x-text="errors.identifier ? errors.identifier[0] : ''"></div>
                    </div>
                    
                    <div>
                        <label class="label" for="password">
                            <span class="label-text">Password</span>
                        </label>
                        <label class="input input-bordered flex items-center gap-2" :class="{'input-error': errors.password}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70"><path fill-rule="evenodd" d="M14 6a4 4 0 0 1-4.899 3.899l-1.955 1.955a.5.5 0 0 1-.353.146H5v1.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-2.5a.5.5 0 0 1 .5-.5h1.5v-1a.5.5 0 0 1 .5-.5H8a.5.5 0 0 1 .5.5v1.5h1.5a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 1-.146.354l-1.955 1.955A4 4 0 1 1 14 6Zm-4-2a2 2 0 1 0-4 0 2 2 0 0 0 4 0Z" clip-rule="evenodd" /></svg>
                            <input type="password" id="password" x-model="formData.password" class="grow" placeholder="Password" required />
                        </label>
                        <div x-show="errors.password" class="text-error text-sm mt-1" x-text="errors.password ? errors.password[0] : ''"></div>

                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div class="form-control">
                            <label class="label cursor-pointer">
                                <input type="checkbox" name="remember" class="checkbox checkbox-primary" />
                                <span class="label-text ml-2">Remember me</span>
                            </label>
                        </div>
                        <a href="{{ route('password.request') }}" class="text-sm link link-hover">Forgot password?</a>
                    </div>
                    
                    <div class="card-actions justify-center">
                        <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                            <span x-show="loading" class="loading loading-spinner"></span>
                            <span x-text="loading ? 'Logging in...' : 'Login'"></span>
                        </button>
                    </div>
                </form>
                
                <div class="divider">OR</div>
                
                <div class="text-center">
                    <a href="{{ route('register') }}" class="link">Don't have an account? Register</a>
                </div>
            </div>
        </div>
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
