@extends('template.admin')

@section('title', 'Employee Leave Log')

@section('content')
    <div x-data="employeeLeaveLogList('{{ config('app.base_api') }}')" x-init="init()" class="container mx-auto p-4">
        
        <h1 class="text-2xl font-bold mb-6">Employee Leave Log</h1>

        <!-- Search and Filter Controls -->
        <div class="flex flex-wrap items-center justify-between mb-4 gap-4">
            <div class="relative flex-grow max-w-md">
                <input 
                    type="text" 
                    placeholder="Search by name, code, or department..." 
                    x-model="search"
                    @input.debounce.300ms="filterEmployees()"
                    class="input input-bordered w-full pl-10"
                >
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </span>
            </div>
            <div class="text-sm text-gray-600">
                <span x-text="`Showing ${filteredEmployees.length} of ${employees.length} employees`"></span>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="text-center p-8">
            <span class="loading loading-lg loading-dots text-primary"></span>
            <p>Loading employees...</p>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && filteredEmployees.length === 0" class="text-center p-8 bg-base-100 rounded-lg shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="font-bold">No employees found</p>
            <p class="text-gray-500" x-show="search">Try adjusting your search</p>
        </div>

        <!-- Desktop Table View -->
        <div x-show="!loading && filteredEmployees.length > 0" class="hidden md:block bg-base-100 rounded-lg shadow">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th class="cursor-pointer" @click="changeSort('name')">
                            Employee Name
                            <span x-show="sortBy === 'name'" x-text="sortDir === 'asc' ? '▲' : '▼'"></span>
                        </th>
                        <th class="cursor-pointer" @click="changeSort('employee_code')">
                            Employee Code
                            <span x-show="sortBy === 'employee_code'" x-text="sortDir === 'asc' ? '▲' : '▼'"></span>
                        </th>
                        <!-- <th class="cursor-pointer" @click="changeSort('department')">
                            Department
                            <span x-show="sortBy === 'department'" x-text="sortDir === 'asc' ? '▲' : '▼'"></span>
                        </th> -->
                        <th>Total Leave Taken</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="employee in paginatedEmployees" :key="employee.id">
                        <tr>
                            <td>
                                <div class="font-bold" x-text="employee.name"></div>
                                <div class="text-sm opacity-50" x-text="employee.email"></div>
                            </td>
                            <td x-text="employee.employee_code"></td>
                            <!-- <td x-text="employee.department ? employee.department.name : 'N/A'"></td> -->
                            <td>
                                <div class="badge badge-info" x-text="(employee.total_leave_taken || 0) + ' days'"></div>
                            </td>
                            <td>
                                <a :href="`{{ route('admin.leave-log.show', ['user' => 'TEMP_USER_ID']) }}`.replace('TEMP_USER_ID', employee.id)" 
                                   class="btn btn-sm btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                    View Log
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div x-show="!loading && filteredEmployees.length > 0" class="block md:hidden space-y-4">
            <template x-for="employee in paginatedEmployees" :key="employee.id">
                <div class="bg-base-100 rounded-lg shadow p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="font-bold" x-text="employee.name"></div>
                            <div class="text-sm opacity-50" x-text="employee.employee_code"></div>
                        </div>
                        <div class="badge badge-info" x-text="(employee.total_leave_taken || 0) + ' days'"></div>
                    </div>
                    <div class="divider my-2"></div>
                    <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                        <div>
                            <div class="font-semibold">Department</div>
                            <div x-text="employee.department ? employee.department.name : 'N/A'"></div>
                        </div>
                        <div>
                            <div class="font-semibold">Email</div>
                            <div x-text="employee.email"></div>
                        </div>
                    </div>
                    <a :href="`{{ route('admin.leave-log.show', ['user' => 'TEMP_USER_ID']) }}`.replace('TEMP_USER_ID', employee.id)" 
                       class="btn btn-sm btn-primary btn-block">
                        View Leave Log
                    </a>
                </div>
            </template>
        </div>

        <!-- Pagination -->
        <div x-show="!loading && filteredEmployees.length > perPage" class="flex items-center justify-between mt-4">
            <span class="text-sm text-gray-700" x-text="`Showing ${((currentPage - 1) * perPage) + 1} to ${Math.min(currentPage * perPage, filteredEmployees.length)} of ${filteredEmployees.length} results`"></span>
            <div class="btn-group">
                <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="btn btn-outline">«</button>
                <button class="btn btn-outline" x-text="`Page ${currentPage} of ${totalPages}`"></button>
                <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="btn btn-outline">»</button>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    function employeeLeaveLogList(baseApiUrl) {
        return {
            employees: [],
            filteredEmployees: [],
            loading: true,
            search: '',
            sortBy: 'name',
            sortDir: 'asc',
            currentPage: 1,
            perPage: 10,
            
            get totalPages() {
                return Math.ceil(this.filteredEmployees.length / this.perPage);
            },
            
            get paginatedEmployees() {
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return this.filteredEmployees.slice(start, end);
            },
            
            init() {
                this.fetchEmployees();
            },
            
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
                        if (response.status === 401) {
                            window.location.href = '/login';
                            return;
                        }
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    if (data.data) {
                        this.employees = data.data.data || data.data;
                        // Fetch leave taken for each employee
                        await this.fetchLeaveTaken();
                        this.filterEmployees();
                    }
                } catch (error) {
                    console.error('Error fetching employees:', error);
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Could not fetch employee list.',
                        });
                    }
                } finally {
                    this.loading = false;
                }
            },
            
            async fetchLeaveTaken() {
                const token = localStorage.getItem('authToken');
                const currentYear = new Date().getFullYear();
                
                // Fetch leave taken for each employee
                const promises = this.employees.map(async (employee) => {
                    try {
                        const response = await fetch(`${baseApiUrl}/admin/master/users/${employee.id}/leave-requests`, {
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            const leaveRequests = data.data || [];
                            
                            // Calculate total days taken for approved leaves in current year
                            const totalDays = leaveRequests
                                .filter(req => {
                                    const reqYear = new Date(req.start_date).getFullYear();
                                    return req.current_status === 'Approved' && reqYear === currentYear;
                                })
                                .reduce((sum, req) => sum + (req.duration_days || 0), 0);
                            
                            employee.total_leave_taken = totalDays;
                        }
                    } catch (error) {
                        console.error(`Error fetching leave for employee ${employee.id}:`, error);
                        employee.total_leave_taken = 0;
                    }
                });
                
                await Promise.all(promises);
            },
            
            filterEmployees() {
                const searchLower = this.search.toLowerCase();
                
                this.filteredEmployees = this.employees.filter(emp => {
                    const nameMatch = emp.name?.toLowerCase().includes(searchLower);
                    const codeMatch = emp.employee_code?.toLowerCase().includes(searchLower);
                    const deptMatch = emp.department?.name?.toLowerCase().includes(searchLower);
                    const emailMatch = emp.email?.toLowerCase().includes(searchLower);
                    
                    return nameMatch || codeMatch || deptMatch || emailMatch;
                });
                
                this.sortEmployees();
                this.currentPage = 1; // Reset to first page on filter
            },
            
            sortEmployees() {
                this.filteredEmployees.sort((a, b) => {
                    let aVal, bVal;
                    
                    if (this.sortBy === 'department') {
                        aVal = a.department?.name || '';
                        bVal = b.department?.name || '';
                    } else {
                        aVal = a[this.sortBy] || '';
                        bVal = b[this.sortBy] || '';
                    }
                    
                    if (typeof aVal === 'string') {
                        aVal = aVal.toLowerCase();
                        bVal = bVal.toLowerCase();
                    }
                    
                    if (this.sortDir === 'asc') {
                        return aVal > bVal ? 1 : -1;
                    } else {
                        return aVal < bVal ? 1 : -1;
                    }
                });
            },
            
            changeSort(column) {
                if (this.sortBy === column) {
                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = column;
                    this.sortDir = 'asc';
                }
                this.sortEmployees();
            },
            
            changePage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                }
            }
        }
    }
</script>
@endpush
