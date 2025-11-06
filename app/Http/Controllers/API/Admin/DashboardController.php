<?php

namespace App\Http\Controllers\API\Admin;

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
        $stats = [
            'total_users' => User::count(),
            'total_departments' => Department::count(),
            'pending_requests' => LeaveRequest::where('current_status', 'Pending')->count(),
            'approved_this_month' => LeaveRequest::where('current_status', 'Approved')
                                            ->whereMonth('start_date', Carbon::now()->month)
                                            ->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    public function getRecentActivity()
    {
        $recentActivity = LeaveRequest::with(['user', 'leaveType'])
                                    ->latest()
                                    ->take(5)
                                    ->get();

        return response()->json(['data' => $recentActivity]);
    }

    public function getUpcomingLeaves()
    {
        $upcomingLeaves = LeaveRequest::with('user')
                                    ->where('current_status', 'Approved')
                                    ->where('start_date', '>=', Carbon::now())
                                    ->where('start_date', '<=', Carbon::now()->addDays(7))
                                    ->orderBy('start_date', 'asc')
                                    ->take(5)
                                    ->get();

        return response()->json(['data' => $upcomingLeaves]);
    }
}
