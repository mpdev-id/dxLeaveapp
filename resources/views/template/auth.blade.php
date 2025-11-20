<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIAL') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="hero min-h-screen bg-base-200">
        <div class="hero-content flex-col lg:flex-row-reverse">
            <div class="text-center lg:text-left px-8">
                <h1 class="text-5xl font-bold">{{ config('app.name', 'SIAL') }}</h1>
                <p class="py-6">Sistem Informasi Admin LeaveApp! Please log in to access your account and manage your leave requests.</p>
            </div>
            <div class="card shrink-0 w-full w-sm">
                <div class="card-body">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
