@extends('template.admin')

@section('title', 'Login')

@section('content')
<div class="hero min-h-screen bg-base-200">
    <div class="hero-content flex-col lg:flex-row-reverse">
        <div class="text-center lg:text-left">
            <h1 class="text-5xl font-bold">Login now!</h1>
            <p class="py-6">Provident cupiditate voluptatem et in. Quaerat fugiat ut assumenda excepturi exercitationem quasi. In deleniti eaque aut repudiandae et a id nisi.</p>
        </div>
        <div class="card shrink-0 w-full max-w-sm shadow-2xl bg-base-100">
            <div x-data="loginForm()" x-init="init()">
                <div x-show="errorMessage" x-text="errorMessage" class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                </div>
                <form class="card-body" @submit.prevent="submitForm">
                    <div class="form-control">
                        <label class="label" for="identifier">
                            <span class="label-text">Email or Username</span>
                        </label>
                        <input type="text" id="identifier" x-model="formData.identifier" placeholder="email or username" class="input input-bordered" required />
                    </div>
                    <div class="form-control">
                        <label class="label" for="password">
                            <span class="label-text">Password</span>
                        </label>
                        <input type="password" id="password" x-model="formData.password" placeholder="password" class="input input-bordered" required />
                        <label class="label">
                            <a href="#" class="label-text-alt link link-hover">Forgot password?</a>
                        </label>
                    </div>
                    <div class="form-control mt-6">
                        <button type="submit" :disabled="loading" class="btn btn-primary">
                            <span x-show="loading" class="loading loading-spinner"></span>
                            <span x-show="!loading">Login</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function loginForm() {
        return {
            formData: {
                identifier: '',
                password: ''
            },
            loading: false,
            errorMessage: '',
            init() {
                if (localStorage.getItem('authToken')) {
                    window.location.href = '/admin/users';
                }
            },
            async submitForm() {
                this.loading = true;
                this.errorMessage = '';
                try {
                    // IMPORTANT: The API URL is a placeholder. Please replace with your actual API endpoint.
                    const response = await fetch('http://leaveapp.redirect.my.id/api/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Login failed.');
                    }

                    // Assuming the API returns a token, store it in local storage
                    if (data.data && data.data.access_token) {
                        localStorage.setItem('authToken', data.data.access_token);
                        // Redirect to admin users page on successful login
                        window.location.href = '/admin/users';
                    } else {
                        throw new Error(data.meta.message || 'Token not found in response.');
                    }

                } catch (error) {
                    this.errorMessage = error.message;
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
