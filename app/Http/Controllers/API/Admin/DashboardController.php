<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function getStats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_departments' => Department::count(),
                'pending_requests' => LeaveRequest::where('current_status', 'Pending')->count(),
                'approved_this_month' => LeaveRequest::where('current_status', 'Approved')
                    ->whereMonth('start_date', Carbon::now()->month)
                    ->count(),
            ];

            return ResponseFormatter::success(['data' => $stats]);
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
}
