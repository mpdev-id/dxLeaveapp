<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="corporate">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Login') - {{ config('app.name', 'Cutikuy') }}</title>

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

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            html, body {
                overflow-x: hidden;
                height: 100%;
                margin: 0;
                padding: 0;
            }
            
            /* Animated gradient background */
            .auth-bg {
                background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
                background-size: 400% 400%;
                animation: gradient 15s ease infinite;
            }
            
            @keyframes gradient {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            
            /* Glass morphism effect */
            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            /* Ensure proper sizing on all devices */
            .auth-container {
                min-height: 100vh;
                min-height: -webkit-fill-available;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }
            
            @media (max-width: 640px) {
                .auth-container {
                    padding: 0.5rem;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="auth-bg auth-container">
            <div class="w-full max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-center">
                    
                    <!-- Left Side: Branding -->
                    <div class="hidden lg:flex flex-col items-center justify-center text-white p-8 order-2 lg:order-1">
                        <div class="space-y-6 text-center">
                            <!-- Logo/Mascot -->
                            <div class="flex justify-center">
                                <div class="relative">
                                    <img 
                                        src="{{ asset('images/cute_duck_attendance.png') }}" 
                                        alt="Cutikuy Mascot" 
                                        class="w-48 h-48 lg:w-64 lg:h-64 object-contain drop-shadow-2xl transition-transform duration-500 hover:scale-110 hover:rotate-6"
                                    >
                                </div>
                            </div>
                            
                            <!-- App Name -->
                            <div>
                                <h1 class="text-4xl lg:text-5xl font-bold mb-3 drop-shadow-lg">
                                    {{ config('app.name', 'Cutikuy') }}
                                </h1>
                                <p class="text-lg lg:text-xl text-white/90 max-w-md mx-auto">
                                    Employee Leave Management System
                                </p>
                            </div>
                            
                            <!-- Features -->
                            <div class="grid grid-cols-2 gap-4 max-w-md mx-auto mt-8">
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm font-semibold">Easy to Use</p>
                                </div>
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <p class="text-sm font-semibold">Secure</p>
                                </div>
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <p class="text-sm font-semibold">Notifications</p>
                                </div>
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm font-semibold">Mobile Ready</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side: Auth Form -->
                    <div class="order-1 lg:order-2">
                        <div class="card glass-card shadow-2xl w-full max-w-md mx-auto">
                            <div class="card-body p-6 sm:p-8">
                                <!-- Mobile Logo -->
                                <div class="flex lg:hidden justify-center mb-6">
                                    <img 
                                        src="{{ asset('images/cute_duck_attendance.png') }}" 
                                        alt="Cutikuy" 
                                        class="w-20 h-20 object-contain"
                                    >
                                </div>
                                
                                <!-- Mobile App Name -->
                                <div class="flex lg:hidden flex-col items-center mb-6">
                                    <h1 class="text-2xl font-bold text-base-content">{{ config('app.name', 'Cutikuy') }}</h1>
                                    <p class="text-sm text-base-content/60">Leave Management System</p>
                                </div>
                                
                                @yield('content')
                            </div>
                        </div>
                        
                        <!-- Footer Links -->
                        <div class="text-center mt-6 space-y-2">
                            <div class="flex flex-wrap justify-center gap-4 text-sm">
                                <a href="{{ url('/documentation.html') }}" class="text-white/80 hover:text-white transition-colors">
                                    📚 API Documentation
                                </a>
                                <span class="text-white/40">•</span>
                                <a href="#" class="text-white/80 hover:text-white transition-colors">
                                    ℹ️ Help Center
                                </a>
                            </div>
                            <p class="text-xs text-white/60">
                                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
    @stack('scripts')
    
    <script>
        {{-- PWA Service Worker Registration --}}
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('[PWA] Service Worker registered');
                        
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
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
    
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                window.location.reload();
            });
        }
    
        {{-- PWA Install Prompt --}}
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            const installBanner = document.createElement('div');
            installBanner.className = 'alert alert-info fixed bottom-4 left-4 right-4 z-50 shadow-lg lg:left-auto lg:right-4 lg:w-96';
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
    </script>
</body>
</html>
