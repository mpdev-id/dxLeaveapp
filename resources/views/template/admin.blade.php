<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <div x-data="{ drawerOpen: localStorage.getItem('drawerOpen') === 'true' }" x-init="$watch('drawerOpen', value => localStorage.setItem('drawerOpen', value))">
        <div class="drawer lg:drawer-open rounded-3xl" :class="{'lg:drawer-open': drawerOpen, 'lg:drawer-close': !drawerOpen}">
            <input id="my-drawer" type="checkbox" class="drawer-toggle" :checked="drawerOpen" />
            
            {{-- Page Content --}}
            <div class="drawer-content flex flex-col">
                <div class="navbar bg-base-100">
                    <div class="flex-none">
                        <label for="my-drawer" @click="drawerOpen = !drawerOpen" aria-label="open sidebar" class="btn btn-square btn-ghost">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </label>
                    </div>
                    <div class="flex-1">
                        <a class="btn btn-ghost text-xl">@yield('title')</a>
                    </div>
                </div>
                
                <main class="p-6">
                    @yield('content')
                </main>
            </div> 
            
            {{-- Drawer Sidebar --}}
            <div class="drawer-side rounded-r-4xl">
                <label for="my-drawer" @click="drawerOpen = false" aria-label="close sidebar" class="drawer-overlay"></label>
                <ul class="menu p-4 w-60 min-h-full bg-base-200 text-base-content">
                    <li class="text-2xl font-bold p-4">{{ config('app.name', 'Laravel') }}</li>
                    <li>
                        <a href="{{ url('/') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <details>
                            <summary>
                                <svg class="w-5 h-5" id='Squared_Menu_24' width='24' height='24' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'>
                                    <rect width='24' height='24' stroke='none' fill='#000000' opacity='0' />


                                    <g transform="matrix(0.59 0 0 0.59 12 12)">
                                        <path style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-dashoffset: 0; stroke-linejoin: miter; stroke-miterlimit: 4; fill: rgb(253 253 253); fill-rule: nonzero; opacity: 1;" transform=" translate(-24, -24)" d="M 15 15 L 7 15 L 7 7 L 15 7 L 15 15 z M 28 15 L 20 15 L 20 7 L 28 7 L 28 15 z M 41 15 L 33 15 L 33 7 L 41 7 L 41 15 z M 15 28 L 7 28 L 7 20 L 15 20 L 15 28 z M 28 28 L 20 28 L 20 20 L 28 20 L 28 28 z M 41 28 L 33 28 L 33 20 L 41 20 L 41 28 z M 15 41 L 7 41 L 7 33 L 15 33 L 15 41 z M 28 41 L 20 41 L 20 33 L 28 33 L 28 41 z M 41 41 L 33 41 L 33 33 L 41 33 L 41 41 z" stroke-linecap="round" />
                                    </g>
                                </svg>
                                Master Data
                            </summary>

                            <ul>
                                <li><a href="{{ route('admin.users.index') }}">Employees</a></li>
                                <li><a href="{{ route('admin.employee-entitlements.index') }}">Employee Entitlements</a></li>
                                <li><a href="{{ route('admin.departments.index') }}">Departments</a></li>
                                <li><a href="{{ route('admin.leave-types.index') }}">Leave Types</a></li>
                                {{-- <li><a href="{{ route('admin.public-holidays.index') }}">Public Holidays</a></li> --}}
                            </ul>
                        </details>
                    </li>
                    <li>
                        <details>
                            <summary>
                                <svg class="w-5 h-5" id='Squared_Menu_24' width='24' height='24' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'>
                                    <rect width='24' height='24' stroke='none' fill='#000000' opacity='0' />


                                    <g transform="matrix(0.59 0 0 0.59 12 12)">
                                        <path style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-dashoffset: 0; stroke-linejoin: miter; stroke-miterlimit: 4; fill: rgb(253 253 253); fill-rule: nonzero; opacity: 1;" transform=" translate(-24, -24)" d="M 15 15 L 7 15 L 7 7 L 15 7 L 15 15 z M 28 15 L 20 15 L 20 7 L 28 7 L 28 15 z M 41 15 L 33 15 L 33 7 L 41 7 L 41 15 z M 15 28 L 7 28 L 7 20 L 15 20 L 15 28 z M 28 28 L 20 28 L 20 20 L 28 20 L 28 28 z M 41 28 L 33 28 L 33 20 L 41 20 L 41 28 z M 15 41 L 7 41 L 7 33 L 15 33 L 15 41 z M 28 41 L 20 41 L 20 33 L 28 33 L 28 41 z M 41 41 L 33 41 L 33 33 L 41 33 L 41 41 z" stroke-linecap="round" />
                                    </g>
                                </svg>
                                Leave Pra-Request
                            </summary>

                            <ul>
                                <li><a href="{{ route('admin.users.index') }}">Leave Request</a></li>
                                <li><a href="{{ route('admin.departments.index') }}">Leave Log</a></li>
                                <li><a href="{{ route('admin.leave-types.index') }}">Leave Print</a></li>
                            </ul>
                        </details>
                    </li>
                    <li class="mt-auto">
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
    </script>
    @push('scripts')
    <script>
        function logoutApi(event, baseApiUrl) {
            event.preventDefault();
            fetch(`${baseApiUrl}/logout`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                }
            })
            .then(response => response.json())
            .then(() => {
                localStorage.removeItem('authToken');
                window.location.href = '/login';
            })
            .catch(error => {
                console.error('Error logging out:', error);
                localStorage.removeItem('authToken');
                window.location.href = '/login';
            });
        }
    </script>
    @endpush
</body>
</html>
