<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index()
    {
        return view('member.leave_request.index');
    }

    public function create()
    {
        return view('member.leave_request.create');
    }

    public function print($id)
    {
        $leaveRequest = \App\Models\LeaveRequest::with([
            'user.department', 
            'leaveType', 
            'approvals.approver.roles'
        ])->findOrFail($id);

        return view('member.leave_request.print', compact('leaveRequest'));
    }
}
