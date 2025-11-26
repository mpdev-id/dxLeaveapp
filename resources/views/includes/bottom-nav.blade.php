<script>
    document.addEventListener('DOMContentLoaded', function() {
        const topNav = document.getElementById('top-nav-shadow');
        if (!topNav) return; // Exit if the element is not found

        const addShadow = () => {
            if (window.scrollY > 0) {
                topNav.classList.add('shadow-2xl');
            } else {
                topNav.classList.remove('shadow-2xl');
            }
        };

        // Add shadow on initial load if already scrolled
        addShadow();

        // Add shadow on scroll
        window.addEventListener('scroll', addShadow);
    });
</script>
<div id="top-nav-shadow" class="fixed top-0 left-0 z-1 w-full h-16 bg-base-100 border-t border-base-300 lg:hidden transition-all ease-in-out duration-800">
    <div class="grid h-full max-w-lg grid-cols-5 mx-auto font-medium">
    {{-- Dashboard --}}
    <a href="{{ route('admin.dashboard.index') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-base-200 group {{ request()->routeIs('admin.dashboard.index') ? 'text-primary' : 'text-base-content' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 mb-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
        <span class="text-xs">Home</span>
    </a>

    {{-- Leave Request --}}
    <a href="{{ route('admin.leave-request') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-base-200 group {{ request()->routeIs('admin.leave-request') ? 'text-primary' : 'text-base-content' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3m-3 0v5m-3-5h6M9 6h3M9 6v5m-3-5h6m4.5 19.5h-15a2.25 2.25 0 01-2.25-2.25V6.75A2.25 2.25 0 014.5 4.5h15a2.25 2.25 0 012.25 2.25v15A2.25 2.25 0 0119.5 22.5z" />
        </svg>
        <span class="text-xs">Request</span>
    </a>

    {{-- Leave Log --}}
    <a href="{{ route('admin.leave-log') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-base-200 group {{ request()->routeIs('admin.leave-log') ? 'text-primary' : 'text-base-content' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-xs">History</span>
    </a>

    {{-- Employees (Master Data) --}}
    <a href="{{ route('admin.users.index') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-base-200 group {{ request()->routeIs('admin.users.index') ? 'text-primary' : 'text-base-content' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.622.92.986 2.043.986 3.222v.213a5.25 5.25 0 01-3.359 4.954c.466-.09.922-.192 1.359-.311A5.25 5.25 0 0015 19.128z" />
        </svg>
        <span class="text-xs">Karyawan</span>
    </a>

    {{-- More (opens drawer) --}}
    <label for="my-drawer" class="inline-flex flex-col items-center justify-center px-5 hover:bg-base-200 group text-base-content">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
        </svg>
        <span class="text-xs">More</span>
    </label>
</div>
</div>
