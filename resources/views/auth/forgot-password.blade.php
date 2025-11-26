@extends('template.auth')

@section('content')
<div x-data="forgotPasswordForm('{{ config('app.base_api') }}')" class="w-full max-w-md mx-auto">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="avatar placeholder mb-4">
                    <div class="bg-primary text-primary-content rounded-full w-16 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-2xl font-bold">Forgot Password?</h2>
                <p class="text-sm text-base-content/70 mt-2">
                    <span x-show="!otpSent">Enter your email or employee code to receive OTP via WhatsApp</span>
                    <span x-show="otpSent" x-cloak>Enter the OTP sent to your WhatsApp and set new password</span>
                </p>
            </div>

            <!-- Error Alert -->
            <div x-show="errorMessage" x-cloak role="alert" class="alert alert-error mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-text="errorMessage"></span>
            </div>

            <!-- Success Alert -->
            <div x-show="successMessage" x-cloak role="alert" class="alert alert-success mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-text="successMessage"></span>
            </div>

            <!-- Step 1: Request OTP -->
            <form x-show="!otpSent" @submit.prevent="requestOTP" class="space-y-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Email or Employee Code</span>
                    </label>
                    <label class="input input-bordered flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM12.735 14c.618 0 1.093-.561.872-1.139a6.002 6.002 0 0 0-11.215 0c-.22.578.254 1.139.872 1.139h9.47Z" />
                        </svg>
                        <input type="text" x-model="identifier" class="grow" placeholder="email@example.com or EMP001" required />
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                    <span x-show="loading" class="loading loading-spinner"></span>
                    <span x-text="loading ? 'Sending...' : 'Send OTP'"></span>
                </button>
            </form>

            <!-- Step 2: Verify OTP & Reset Password -->
            <form x-show="otpSent" x-cloak @submit.prevent="resetPassword" class="space-y-4">
                <!-- OTP Input -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">OTP Code (6 digits)</span>
                        <span class="label-text-alt text-info cursor-pointer" @click="resendOTP">Resend OTP</span>
                    </label>
                    <label class="input input-bordered flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 opacity-70">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        </svg>
                        <input type="text" x-model="otp" class="grow" placeholder="123456" maxlength="6" pattern="[0-9]{6}" required />
                    </label>
                </div>

                <!-- New Password -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">New Password</span>
                    </label>
                    <label class="input input-bordered flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70">
                            <path fill-rule="evenodd" d="M14 6a4 4 0 0 1-4.899 3.899l-1.955 1.955a.5.5 0 0 1-.353.146H5v1.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-2.5a.5.5 0 0 1 .5-.5h1.5v-1a.5.5 0 0 1 .5-.5H8a.5.5 0 0 1 .5.5v1.5h1.5a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 1-.146.354l-1.955 1.955A4 4 0 1 1 14 6Zm-4-2a2 2 0 1 0-4 0 2 2 0 0 0 4 0Z" clip-rule="evenodd" />
                        </svg>
                        <input type="password" x-model="password" class="grow" placeholder="Min 8 characters" minlength="8" required />
                    </label>
                </div>

                <!-- Confirm Password -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Confirm Password</span>
                    </label>
                    <label class="input input-bordered flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70">
                            <path fill-rule="evenodd" d="M14 6a4 4 0 0 1-4.899 3.899l-1.955 1.955a.5.5 0 0 1-.353.146H5v1.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-2.5a.5.5 0 0 1 .5-.5h1.5v-1a.5.5 0 0 1 .5-.5H8a.5.5 0 0 1 .5.5v1.5h1.5a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 1-.146.354l-1.955 1.955A4 4 0 1 1 14 6Zm-4-2a2 2 0 1 0-4 0 2 2 0 0 0 4 0Z" clip-rule="evenodd" />
                        </svg>
                        <input type="password" x-model="password_confirmation" class="grow" placeholder="Confirm password" minlength="8" required />
                    </label>
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="backToIdentifier" class="btn btn-ghost flex-1">
                        Back
                    </button>
                    <button type="submit" class="btn btn-primary flex-1" :disabled="loading">
                        <span x-show="loading" class="loading loading-spinner"></span>
                        <span x-text="loading ? 'Resetting...' : 'Reset Password'"></span>
                    </button>
                </div>
            </form>

            <!-- Back to Login -->
            <div class="divider">OR</div>
            <div class="text-center">
                <a href="{{ route('login') }}" class="link link-hover flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function forgotPasswordForm(baseApiUrl) {
        return {
            identifier: '',
            otp: '',
            password: '',
            password_confirmation: '',
            email: '',
            phoneNumber: '',
            otpSent: false,
            loading: false,
            errorMessage: '',
            successMessage: '',

            async requestOTP() {
                this.loading = true;
                this.errorMessage = '';
                this.successMessage = '';

                try {
                    const response = await fetch(`${baseApiUrl}/forgot-password`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ identifier: this.identifier })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.email = data.data.email;
                        this.phoneNumber = data.data.phone_number;
                        this.otpSent = true;
                        
                        this.successMessage = 'OTP has been sent to your WhatsApp number. Please check your WhatsApp.';
                    } else {
                        this.errorMessage = data.meta?.message || 'Failed to send OTP';
                    }
                } catch (error) {
                    this.errorMessage = 'Failed to connect to server';
                } finally {
                    this.loading = false;
                }
            },

            async resetPassword() {
                if (this.password !== this.password_confirmation) {
                    this.errorMessage = 'Passwords do not match';
                    return;
                }

                this.loading = true;
                this.errorMessage = '';
                this.successMessage = '';

                try {
                    const response = await fetch(`${baseApiUrl}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            email: this.email,
                            otp: this.otp,
                            password: this.password,
                            password_confirmation: this.password_confirmation
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.successMessage = 'Password reset successful! Redirecting to login...';
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 2000);
                    } else {
                        this.errorMessage = data.meta?.message || 'Failed to reset password';
                    }
                } catch (error) {
                    this.errorMessage = 'Failed to connect to server';
                } finally {
                    this.loading = false;
                }
            },

            resendOTP() {
                this.otpSent = false;
                this.otp = '';
                this.password = '';
                this.password_confirmation = '';
                this.requestOTP();
            },

            backToIdentifier() {
                this.otpSent = false;
                this.otp = '';
                this.password = '';
                this.password_confirmation = '';
                this.errorMessage = '';
                this.successMessage = '';
            }
        }
    }
</script>
@endpush
