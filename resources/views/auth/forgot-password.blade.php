@extends('template.auth')

@section('content')
    <div class="mb-4 text-sm text-base-content/80">
        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div role="alert" class="alert alert-success mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text">Email</span>
            </label>
            <input type="email" id="email" name="email" placeholder="your.email@example.com" class="input input-bordered" required autofocus />
        </div>

        <div class="form-control mt-6">
            <button type="submit" class="btn btn-primary">
                Email Password Reset Link
            </button>
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="link">Back to Login</a>
        </div>
    </form>
@endsection
