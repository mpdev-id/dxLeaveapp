<div class="drawer-side is-drawer-close:overflow-visible">
    <label for="my-drawer-4" aria-label="close sidebar" class="drawer-overlay"></label>
    <div class="flex min-h-full flex-col items-start bg-base-200 is-drawer-close:w-14 is-drawer-open:w-64">
      <!-- Sidebar content here -->
      <ul class="menu w-full grow">
        <li>
          <a href="{{ url('/') }}" class="is-drawer-close:tooltip is-drawer-close:tooltip-right" data-tip="Dashboard">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            <span class="is-drawer-close:hidden">Dashboard</span>
          </a>
        </li>
        <li>
          <details>
            <summary>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <span class="is-drawer-close:hidden">Master Data</span>
            </summary>
            <ul>
              <li><a href="{{ route('admin.users.index') }}">Users</a></li>
              <li><a href="{{ route('admin.departments.index') }}">Departments</a></li>
              <li><a href="{{ route('admin.leave-types.index') }}">Leave Types</a></li>
              <li><a href="{{ route('admin.public-holidays.index') }}">Public Holidays</a></li>
              <li><a href="{{ route('admin.employee-entitlements.index') }}">Employee Entitlements</a></li>
            </ul>
          </details>
        </li>
      </ul>
    </div>
  </div>