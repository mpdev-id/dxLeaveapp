<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $leaveRequests = LeaveRequest::with(['user', 'leaveType'])->get();
        return ResponseFormatter::success($leaveRequests, 'Leave requests retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'current_status' => 'required|string|in:Pending,Approved,Rejected,Canceled',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
        }

        $leaveRequest = LeaveRequest::create($request->all());

        return ResponseFormatter::success($leaveRequest, 'Leave request created successfully');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        return ResponseFormatter::success($leaveRequest->load(['user', 'leaveType']), 'Leave request retrieved successfully');
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'exists:users,id',
            'leave_type_id' => 'exists:leave_types,id',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'reason' => 'string',
            'current_status' => 'string|in:Pending,Approved,Rejected,Canceled',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
        }

        $leaveRequest->update($request->all());

        return ResponseFormatter::success($leaveRequest, 'Leave request updated successfully');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();
        return ResponseFormatter::success(null, 'Leave request deleted successfully');
    }
}
