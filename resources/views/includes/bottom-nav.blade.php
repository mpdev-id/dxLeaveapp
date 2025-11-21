<div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-base-100 border-t border-base-300 lg:hidden">
    <div class="grid h-full max-w-lg grid-cols-5 mx-auto font-medium">
        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard.index') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-base-200 group {{ request()->routeIs('admin.dashboard.index') ? 'text-primary' : 'text-base-content' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-xs">Home</span>
        </a>

        {{-- Leave Request --}}
        <a href="{{ route('admin.leave-request') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-base-200 group {{ request()->routeIs('admin.leave-request') ? 'text-primary' : 'text-base-content' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xs">Request</span>
        </a>

        {{-- Leave Log --}}
        <a href="{{ route('admin.leave-log') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-base-200 group {{ request()->routeIs('admin.leave-log') ? 'text-primary' : 'text-base-content' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mb-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5 0-2.268-2.268M9 3.75l3 3m0 0l3-3m-3 3v12" />
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
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <span class="text-xs">More</span>
        </label>
    </div>
</div>
