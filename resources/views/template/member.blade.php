<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="corporate">
<head>
    <title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- PWA Meta Tags --}}
    <meta name="description" content="Cutikuy - Employee Leave Management System">
    <meta name="theme-color" content="#ffbffaff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Cutikuy">
    
    {{-- Manifest --}}
    <link rel="manifest" href="/manifest.json">
    
    {{-- Icons --}}
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/images/icons/icon-192x192.png">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="/css/spa-enhancements.css">
    
    @stack('styles')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/spa-enhancements.js" defer></script>
    <script>
        // Auth Check
        if (!localStorage.getItem('authToken')) {
            window.location.href = '/login';
        }
    </script>
    </script>
    <style>
        body {
            background-image: url({{asset('/images/grid-pattern.svg')}});
            background-size: 650px;
            background-repeat: repeat-x;
            background-position: top;
            background-attachment: fixed; /* Ensures the background stays fixed even if content scrolls */
        }
    </style>
</head>
<body class="bg-base-200 min-h-screen pb-16 md:pb-0">

    {{-- Top Navbar (Desktop/Tablet) --}}
    <div class="navbar bg-base-100 shadow-lg sticky top-0 z-50" x-data="navbarUser('{{ config('app.base_api') }}')" x-init="init()">
        <div class="flex-1 flex items-center gap-2">
            <img src="{{ asset('images/cute_duck_attendance.png') }}" alt="Cutikuy" class="w-10 h-10 object-contain rounded-full">
            <a class="btn btn-ghost text-xl">{{ config('app.name', 'Cutikuy') }}</a>
        </div>
        <div class="flex-none gap-2">
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 rounded-full">
                        <img 
                            alt="User Avatar" 
                            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=random&color=fff&size=128&bold=true`" 
                            class="w-full h-full object-cover"
                        />
                    </div>
                </div>
                <ul tabindex="0" class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52">
                    <li><a href="{{ route('dashboard-member') }}">Dashboard</a></li>
                    <li><a href="{{ route('member.leaves.index') }}">My Leaves</a></li>
                    <!-- @can('approve leave request')
                        <li><a href="{{ route('member.approver-log.index') }}">Approver Log</a></li>
                    @endcan -->
                        <li><a href="{{ route('member.approver-log.index') }}">Approver Log</a></li>
                    <li><a href="{{ route('member.profile.index') }}">Profile</a></li>
                    <li><a href="#" onclick="logoutApi(event)">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <main class="p-4 md:p-6 max-w-7xl mx-auto">
        @yield('content')
    </main>

    {{-- Bottom Navigation (Mobile Only) --}}
    <div class="btm-nav md:hidden bg-base-100 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-50 fixed bottom-0 left-0 right-0 h-16 border-t border-base-200 flex flex-row">
        <a href="{{ route('dashboard-member') }}" class="{{ request()->routeIs('dashboard-member') ? 'active text-primary bg-primary/10' : 'text-base-content/60 hover:text-primary' }} flex-1 flex flex-col items-center justify-center gap-1 h-full transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="{{ route('member.leaves.index') }}" class="{{ request()->routeIs('member.leaves.*') ? 'active text-primary bg-primary/10' : 'text-base-content/60 hover:text-primary' }} flex-1 flex flex-col items-center justify-center gap-1 h-full transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
            <span class="text-[10px] font-medium">Leaves</span>
        </a>
        <!-- @can('approve leave request')
            <a href="{{ route('member.approver-log.index') }}" class="{{ request()->routeIs('member.approver-log.*') ? 'active text-primary bg-primary/10' : 'text-base-content/60 hover:text-primary' }} flex-1 flex flex-col items-center justify-center gap-1 h-full transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-[10px] font-medium">Approvals</span>
            </a>
            @endcan -->
            <a href="{{ route('member.approver-log.index') }}" class="{{ request()->routeIs('member.approver-log.*') ? 'active text-primary bg-primary/10' : 'text-base-content/60 hover:text-primary' }} flex-1 flex flex-col items-center justify-center gap-1 h-full transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="text-[10px] font-medium">Approvals</span>
            </a>
        <a href="{{ route('member.profile.index') }}" class="{{ request()->routeIs('member.profile.*') ? 'active text-primary bg-primary/10' : 'text-base-content/60 hover:text-primary' }} flex-1 flex flex-col items-center justify-center gap-1 h-full transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            <span class="text-[10px] font-medium">Profile</span>
        </a>
    </div>

    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Alpine.js component for navbar user info
        function navbarUser(baseApiUrl) {
            return {
                userName: 'User',
                
                async init() {
                    await this.fetchUser();
                },
                
                async fetchUser() {
                    try {
                        const token = localStorage.getItem('authToken');
                        if (!token) return;
                        
                        const response = await fetch(`${baseApiUrl}/user`, {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.userName = data.data.name || 'User';
                        }
                    } catch (error) {
                        console.error('Error fetching user:', error);
                    }
                }
            }
        }
        
        function logoutApi(event) {
            event.preventDefault();
            const baseApiUrl = '{{ config('app.base_api') }}'; // Ensure this config is available
            
            fetch(`${baseApiUrl}/logout`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                }
            })
            .finally(() => {
                localStorage.removeItem('authToken');
                window.location.href = '/login';
            });
        }
    </script>
</body>
</html>
