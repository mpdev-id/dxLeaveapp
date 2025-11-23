<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\PublicHoliday;
use App\Services\EntitlementService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $entitlementService;

    public function __construct(EntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }
    public function getStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_departments' => Department::count(),
                'pending_requests'  => LeaveRequest::where('current_status', ['Pending','In Progress'])->count(),
                'approved_this_month' => LeaveRequest::where('current_status', 'Approved')
                    ->whereMonth('start_date', Carbon::now()->month)
                    ->count(),
            ];

            return ResponseFormatter::success($stats);
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve stats: ' . $e->getMessage(), 500);
        }
    }

    public function getRecentActivity()
    {
        try {
            $recentActivity = LeaveRequest::with(['user', 'leaveType'])
                ->latest()
                ->take(5)
                ->get();

            return ResponseFormatter::success(['data' => $recentActivity]);
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve recent activity: ' . $e->getMessage(), 500);
        }
    }

    public function getUpcomingLeaves()
    {
        try {
            $upcomingLeaves = LeaveRequest::with('user')
                ->where('current_status', 'Approved')
                ->where('start_date', '>=', Carbon::now())
                ->where('start_date', '<=', Carbon::now()->addDays(7))
                ->orderBy('start_date', 'asc')
                ->take(5)
                ->get();

            return ResponseFormatter::success(['data' => $upcomingLeaves]);
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve upcoming leaves: ' . $e->getMessage(), 500);
        }
    }

    public function getLeaveRequestsByDate(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date_format:Y-m-d',
            ]);

            $date = Carbon::parse($request->date);

            $leaveRequests = LeaveRequest::with(['user.department', 'leaveType'])
                ->whereNotIn('current_status', ['Draft'])
                ->where(function ($query) use ($date) {
                    $query->whereDate('start_date', '<=', $date)
                          ->whereDate('end_date', '>=', $date);
                })
                ->get();

            return ResponseFormatter::success($leaveRequests, 'Leave requests for selected date retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve leave requests for date: ' . $e->getMessage(), 500);
        }
    }

    public function getLeaveCalendar(Request $request)
    {
        try {
            $events = collect();

            // Fetch Approved Leave Requests
            $leaveQuery = LeaveRequest::with(['user.department', 'leaveType'])
                ->whereNotIn('current_status', ['Draft']);

            if ($request->has('start') && $request->has('end')) {
                $leaveQuery->where(function ($q) use ($request) {
                    $q->whereBetween('start_date', [$request->start, $request->end])
                      ->orWhereBetween('end_date', [$request->start, $request->end]);
                });
            }

            $leaveRequests = $leaveQuery->get();

            $leaveEvents = $leaveRequests->map(function ($leave) {
                $endDate = Carbon::parse($leave->end_date)->addDay()->toDateString();
                $currentYear = Carbon::now()->year; // Or $leave->start_date->year if entitlement year matches leave year

                // Get remaining balance for this specific leave type of the user
                $remainingBalance = $this->entitlementService->getRemainingBalanceForLeaveType(
                    $leave->user,
                    $leave->leave_type_id,
                    $currentYear
                );
                
                $leave->load('user', 'leaveType', 'approvals.approver', 'workflow.steps.approverRole');

                $workflowService = app(\App\Services\WorkflowService::class);
                if ($leave->workflow) {
                    foreach ($leave->workflow->steps as &$step) {
                        $approver = $workflowService->findApproverForStep($leave->user, $step);
                        $step->approver_user = $approver ? $approver->only(['id', 'name', 'email']) : null;
                    }
                    unset($step);
                }

                $details = $leave->toArray();
                $details['remaining_leave_balance'] = $remainingBalance;

                return [
                    'id' => 'leave-' . $leave->id,
                    'title' => $leave->user->name . ' - ' . $leave->leaveType->name,
                    'start' => $leave->start_date,
                    'end' => $endDate,
                    'allDay' => true,
                    'extendedProps' => [
                        'type' => 'leave',
                        'details' => $details,
                    ]
                ];
            });

            $events = $events->merge($leaveEvents);


            // Fetch Public Holidays
            $holidayQuery = PublicHoliday::query();

            if ($request->has('start') && $request->has('end')) {
                $holidayQuery->whereBetween('date', [$request->start, $request->end]);
            }

            $publicHolidays = $holidayQuery->get();

            $holidayEvents = $publicHolidays->map(function ($holiday) {
                return [
                    'id' => 'holiday-' . $holiday->id,
                    'title' => $holiday->name,
                    'start' => $holiday->date,
                    'allDay' => true,
                    'className' => 'bg-success text-white',
                    'extendedProps' => [
                        'type' => 'holiday',
                        'details' => $holiday,
                    ]
                ];
            });

            $events = $events->merge($holidayEvents);


            return ResponseFormatter::success($events, 'Calendar data retrieved successfully');

        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve calendar data: ' . $e->getMessage(), 500);
        }
    }

    public function getLeaveChartData(Request $request)
    {
        try {
            $request->validate([
                'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
                'month' => 'required|integer|min:1|max:12',
            ]);

            $year = $request->year;
            $month = $request->month;

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $daysInMonth = $startDate->daysInMonth;

            $statuses = ['Approved', 'In Progress', 'Rejected'];

            $leaveCounts = LeaveRequest::whereBetween('start_date', [$startDate, $endDate])
                ->whereIn('current_status', $statuses)
                ->select(
                    DB::raw('DAY(start_date) as day'),
                    'current_status',
                    DB::raw('count(*) as total')
                )
                ->groupBy('day', 'current_status')
                ->get();

            $labels = range(1, $daysInMonth);
            $data = [];
            foreach ($statuses as $status) {
                $statusKey = lcfirst(str_replace(' ', '', $status)); // e.g., 'inProgress'
                $data[$statusKey] = array_fill(0, $daysInMonth, 0);
            }

            foreach ($leaveCounts as $count) {
                $statusKey = lcfirst(str_replace(' ', '', $count->current_status));
                // array is 0-indexed, days are 1-indexed
                if (isset($data[$statusKey])) {
                    $data[$statusKey][$count->day - 1] = $count->total;
                }
            }

            return ResponseFormatter::success([
                'labels' => $labels,
                'data' => $data,
            ], 'Leave chart data retrieved successfully');

        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve chart data: ' . $e->getMessage(), 500);
        }
    }
}
