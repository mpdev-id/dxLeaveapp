<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Services\EntitlementService; // Add this line
use Carbon\Carbon; // Add this line

class LeaveLogController extends Controller
{
    protected $entitlementService;

    public function __construct(EntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    /**
     * Display the leave requests for a specific employee.
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        // Data fetching will be handled by frontend via API call
        return view('admin.leave-log.show', compact('user'));
    }
}
