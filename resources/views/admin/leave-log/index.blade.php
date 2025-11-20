@extends('template.admin')

@section('title', 'Employee Leave Log')

@section('content')
    <div class="card bg-base-100 shadow-xl" x-data="employeeLeaveLogList('{{ config('app.base_api') }}')" x-init="fetchEmployees()">
        <div class="card-body">
            <h2 class="card-title">Employee List for Leave Log</h2>
            <div x-show="loading" class="text-center">Loading employees...</div>
            <div x-show="!loading && employees.length === 0" class="text-center">No employees found.</div>
            <div x-show="!loading && employees.length > 0" class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Employee Code</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="employee in employees" :key="employee.id">
                            <tr>
                                <td x-text="employee.name"></td>
                                <td x-text="employee.employee_code"></td>
                                <td x-text="employee.department ? employee.department.name : 'N/A'"></td>
                                <td>
                                    <a :href="`{{ route('admin.leave-log.show', ['user' => 'TEMP_USER_ID']) }}`.replace('TEMP_USER_ID', employee.id)" class="btn btn-sm btn-primary">View Log</a>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function employeeLeaveLogList(baseApiUrl) {
        return {
            employees: [],
            loading: true,
            
            async fetchEmployees() {
                this.loading = true;
                const token = localStorage.getItem('authToken');
                if (!token) {
                    window.location.href = '/login';
                    return;
                }

                try {
                    const response = await fetch(`${baseApiUrl}/admin/master/users`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        if (response.redirected) {
                             window.location.href = '/login';
                             return;
                        }
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    if (data.data) {
                        this.employees = data.data.data;
                    }
                } catch (error) {
                    console.error('Error fetching employees:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Could not fetch employee list.',
                    });
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
