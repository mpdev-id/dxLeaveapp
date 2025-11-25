<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="corporate">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIALAN') }}</title>

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

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            html, body {
                overflow-x: hidden;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div id="animated-bg" class="fixed top-0 left-0 w-full h-full -z-10"></div>
        <div class="hero min-h-screen bg-base-200/75 backdrop-blur-sm">
            <div class="hero-content flex-col lg:flex-row-reverse">
                <div class="text-center lg:text-left px-8">
                    <h1 class="text-5xl font-bold">{{ config('app.name', 'SIALAN') }}</h1>
                    <p class="py-6">Sistem Informasi Admin Leave App Nich! Please log in to access your account and manage your leave requests.</p>
                    <a href="{{ url('/documentation.html') }}" class="btn btn-warning shadow-xl btn-sm btn-outline m-t">API Doc's</a>
                </div>
                <div class="card shrink-0 w-full w-sm auth-card">
                    <div class="card-body">
                        @yield('content')
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
    </script>
</body>
</html>
