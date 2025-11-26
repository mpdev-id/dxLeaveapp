<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- PWA Meta Tags --}}
    <meta name="description" content="Cutikuy - Employee Leave Management System">
    <meta name="theme-color" content="#0d6efd">
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
    
    {{-- SPA Enhancements --}}
    <link rel="stylesheet" href="/css/spa-enhancements.css">
    
    @stack('styles')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/spa-enhancements.js" defer></script>
</head>
<body class="bg-base-200">
    <div x-data="{ drawerOpen: localStorage.getItem('drawerOpen') === 'true' }" x-init="$watch('drawerOpen', value => localStorage.setItem('drawerOpen', value))">
        <div class="drawer" :class="{'lg:drawer-open': drawerOpen, 'lg:drawer-close': !drawerOpen}">
            <input id="my-drawer" type="checkbox" class="drawer-toggle" />

            {{-- Page Content --}}
            <div class="drawer-content flex flex-col">
                <div class="navbar bg-base-100 shadow-lg rounded-2xl">
                    <div class="flex-none lg:hidden">
                        {{-- This is for the mobile view, but the menu is now on the bottom nav --}}
                    </div>
                    <div class="flex-none hidden lg:block">
                        <label for="my-drawer" @click="drawerOpen = !drawerOpen" aria-label="open sidebar" class="btn btn-square btn-ghost ">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </label>
                    </div>
                    <div class="flex-1 flex-none hidden lg:block">
                        <a class="btn btn-ghost text-xl">@yield('title')</a>
                    </div>
                </div>

                <main class="p-4 md:p-6 pb-24 lg:pb-6">
                    <!-- <div class="bg-base-100 rounded-box shadow p-4 md:p-6"> -->
                    <div class="p-4 md:p-6">
                        @yield('content')
                    </div>
                </main>
            </div> 

            {{-- Drawer Sidebar --}}
            <div class="drawer-side bg-purple-400/25 z-1">
                <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
                <ul class="menu p-4 w-60 min-h-full bg-base-200 text-base-content z-1">
                    <li class="flex items-center justify-center mb-2">
                        <img src="{{ asset('images/cute_duck_attendance.png') }}" alt="Cutikuy" class="w-20 h-20 object-contain rounded-full animated pulse">
                    </li>
                    <li class="text-2xl font-bold p-4 text-center">{{ config('app.name', 'Laravel') }}</li>
                    <li>
                        <a href="{{ route('admin.dashboard.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <details id="menu-master-data">
                            <summary>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" />
                                </svg>
                                Master Data
                            </summary>
                            <ul>
                                <li><a href="{{ route('admin.users.index') }}">Employees</a></li>
                                <li><a href="{{ route('admin.employee-entitlements.index') }}">Employee Entitlements</a></li>
                                <li><a href="{{ route('admin.departments.index') }}">Departments</a></li>
                                <li><a href="{{ route('admin.leave-types.index') }}">Leave Types</a></li>
                                <li><a href="{{ route('admin.workflows.index') }}">Workflows</a></li>
                            </ul>
                        </details>
                    </li>
                    <li>
                        <details id="menu-leave-pra-request">
                            <summary>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                  </svg>                                  
                                Leave Pra-Request
                            </summary>
                            <ul>
                                <li><a href="{{ route('admin.leave-request') }}">Leave Request</a></li>
                                <li><a href="{{ route('admin.leave-log') }}">Leave Log</a></li>
                            </ul>
                        </details>
                    </li>
                    <li>
                        <a href="{{ route('admin.push-notifications.test') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Test Push Notifications
                        </a>
                    </li>

                        
                        <li class="mt-auto">
                        <a href="{{ route('member.profile.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Member Profile
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full text-left flex" onclick="logoutApi(event, '{{ config('app.base_api') }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @include('includes.bottom-nav')

    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Restore open state from localStorage
            const openMenus = JSON.parse(localStorage.getItem('openMenus') || '[]');
            openMenus.forEach(menuId => {
                const menu = document.getElementById(menuId);
                if (menu) {
                    menu.open = true;
                }
            });

            // Add event listeners to save state on change
            const menuDetails = document.querySelectorAll('.drawer-side .menu details');
            menuDetails.forEach(detail => {
                detail.addEventListener('toggle', event => {
                    let openMenus = JSON.parse(localStorage.getItem('openMenus') || '[]');
                    if (event.target.open) {
                        // Add to list if not already there
                        if (!openMenus.includes(event.target.id)) {
                            openMenus.push(event.target.id);
                        }
                    } else {
                        // Remove from list
                        openMenus = openMenus.filter(id => id !== event.target.id);
                    }
                    localStorage.setItem('openMenus', JSON.stringify(openMenus));
                });
            });

            // Set active menu item
            const currentUrl = window.location.href;
            const menuLinks = document.querySelectorAll('.drawer-side .menu a');
            let bestMatch = null;
            let longestMatch = 0;

            menuLinks.forEach(link => {
                if (link.href && currentUrl.startsWith(link.href) && link.href.length > longestMatch) {
                    longestMatch = link.href.length;
                    bestMatch = link;
                }
            });

            if (bestMatch) {
                bestMatch.classList.add('active');
                const detailsParent = bestMatch.closest('details');
                if (detailsParent && !detailsParent.open) {
                    detailsParent.open = true;
                    // The 'toggle' event listener above will handle saving to localStorage
                }
            }

            document.body.addEventListener('click', function (e) {
                if (e.target.matches('.delete-button')) {
                    e.preventDefault();
                    const form = e.target.closest('form');
                    if (form) {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    }
                }
            });
        });

        function logoutApi(event, baseApiUrl) {
            event.preventDefault();
            const form = event.currentTarget.closest('form');
            
            fetch(`${baseApiUrl}/logout`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                }
            })
            .finally(() => {
                localStorage.removeItem('authToken');
                if (form) {
                    form.submit(); // Submit the original form to logout from web session
                } else {
                    window.location.href = '/login'; // Fallback if something is wrong
            }
        });
    }

    {{-- PWA Service Worker Registration --}}
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then((registration) => {
                    // console.log('[PWA] Service Worker registered:', registration.scope);
                    
                    // Check for updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // Show update notification
                                if (confirm('New version available! Reload to update?')) {
                                    newWorker.postMessage({ action: 'skipWaiting' });
                                    window.location.reload();
                                }
                            }
                        });
                    });
                })
                .catch((error) => {
                    console.error('[PWA] Service Worker registration failed:', error);
                });
        });

        // Handle controller change (new service worker activated)
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            window.location.reload();
        });
    }

    {{-- PWA Install Prompt --}}
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        // Show install button or banner
        const installBanner = document.createElement('div');
        installBanner.className = 'alert alert-info fixed bottom-20 left-4 right-4 z-50 shadow-lg lg:left-auto lg:right-4 lg:w-96';
        installBanner.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="font-bold">Install Cutikuy App</h3>
                <div class="text-xs">Add to home screen for better experience</div>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-sm btn-primary" onclick="installPWA()">Install</button>
                <button class="btn btn-sm btn-ghost" onclick="dismissInstall()">Later</button>
            </div>
        `;
        installBanner.id = 'install-banner';
        document.body.appendChild(installBanner);
    });

    function installPWA() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    // console.log('[PWA] User accepted the install prompt');
                }
                deferredPrompt = null;
                dismissInstall();
            });
        }
    }

    function dismissInstall() {
        const banner = document.getElementById('install-banner');
        if (banner) {
            banner.remove();
        }
    }

    {{-- Online/Offline Status --}}
    window.addEventListener('online', () => {
        // console.log('[PWA] Back online');
        // You can show a toast notification here
    });

    window.addEventListener('offline', () => {
        // console.log('[PWA] Gone offline');
        // You can show a toast notification here
    });
</script>
</body>
</html>