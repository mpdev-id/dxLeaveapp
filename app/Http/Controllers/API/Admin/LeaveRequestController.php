<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['user', 'leaveType'])
                         ->select('leave_requests.*_’);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('leaveType', function ($leaveTypeQuery) use ($search) {
                    $leaveTypeQuery->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        // Sorting functionality
        if ($request->filled('sort_by')) {
            $sortBy = $request->input('sort_by');
            $sortDir = $request->input('sort_dir', 'asc');

            $allowedSorts = ['start_date', 'end_date', 'current_status', 'user_name', 'leave_type_name'];

            if (in_array($sortBy, $allowedSorts)) {
                if ($sortBy === 'user_name') {
                    $query->join('users', 'leave_requests.user_id', '=', 'users.id')
                          ->orderBy('users.name', $sortDir);
                } elseif ($sortBy === 'leave_type_name') {
                    $query->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
                          ->orderBy('leave_types.name', $sortDir);
                } else {
                    $query->orderBy($sortBy, $sortDir);
                }
            }
        }

        $leaveRequests = $query->paginate($request->input('per_page', 10));

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
        return ResponseFormatter::success($leaveRequest->load(['user', 'leaveType', 'approvals']), 'Leave request retrieved successfully');
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
