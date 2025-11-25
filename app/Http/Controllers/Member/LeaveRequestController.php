<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\LeaveRequest;

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

    public function edit(LeaveRequest $leaveRequest)
    {
        // Pass the UUID to the view as 'id' so the JS can use it
        $id = $leaveRequest->uuid;
        return view('member.leave_request.edit', compact('id'));
    }

    public function print(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load([
            'user.department', 
            'leaveType', 
            'approvals.approver.roles'
        ]);

        return view('member.leave_request.print', compact('leaveRequest'));
    }
}
