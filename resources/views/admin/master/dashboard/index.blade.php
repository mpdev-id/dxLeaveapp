@extends('template.admin')

@section('title', 'Homepage')
@section('content')

{{-- <div x-data="{ open: false }">
    <button @click="open = !open" class="btn btn-primary">Click Me</button>
    <div x-show="open" class="text-center text-7xl mt-5 p-5">
        Hello world, I am
        <span class="text-rotate">
            <span>
              <span class="bg-teal-400 text-white px-2 rounded-3xl">Web Developer</span>
              <span class="bg-red-400 text-white px-2 rounded-3xl">Laravel Specialist</span>
              <span class="bg-blue-400 text-white px-2 rounded-3xl">Spiderman</span>
            </span>
          </span>
    </div>
</div> --}}
<div x-data="statsComponent()" x-init="ambilData()" class="stats shadow">

    <template x-if="!loading">
        <ul>
          <template x-for="user in users" :key="user.id">
            <li x-text="user.name"></li>
          </template>
        </ul>
      </template>

    <div class="stat">
        <div class="stat-figure text-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
        </div>
        <div class="stat-title">Total User's</div>
        <div class="stat-value text-primary">350</div>
        <div class="stat-desc">Employees</div>
    </div>

    <div class="stat">
        <div class="stat-figure text-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
        <div class="stat-title">Approved Request</div>
        <div class="stat-value text-secondary">50</div>
        <div class="stat-desc">Requests</div>
    </div>

    <div class="stat">
        <div class="stat-figure text-secondary">
            <div class="avatar avatar-online">
                <div class="w-16 rounded-full">
                    <img src="https://img.daisyui.com/images/profile/demo/anakeen@192.webp" />
                </div>
            </div>
        </div>
        <div class="stat-value">20%</div>
        <div class="stat-title">Employees Leave this month</div>
        <div class="stat-desc text-secondary">31 Requests waiting</div>
    </div>

</div>

{{-- users --}}
<div x-data="fetchPengguna()" x-init="ambilData()">
  <template x-if="loading">
    <p>Loading data...</p>
  </template>

  <template x-if="!loading">
    <ul>
      <template x-for="user in users" :key="user.id">
        <li x-text="user.name"></li>
      </template>
    </ul>
  </template>
</div>


@endsection

@push('scripts')
  
<script>
    function statsComponent() {
      return {
        users: [],
        loading: true,
        async ambilData() {
          try {
            const res = await fetch('https://leaveapp.redirect.my.id/api/admin/dashboard/');
            const data = await res.json();
            this.users = data;
          } catch (e) {
            console.error('Gagal mengambil data:', e);
          } finally {
            this.loading = false;
          }
        }
      }
    }
  </script>
<script>
  function fetchPengguna() {
    return {
      users: [],
      loading: true,
      async ambilData() {
        try {
          const res = await fetch('https://jsonplaceholder.typicode.com/users');
          const data = await res.json();
          this.users = data;
        } catch (e) {
          console.error('Gagal mengambil data:', e);
        } finally {
          this.loading = false;
        }
      }
    }
  }
</script>
@endpush