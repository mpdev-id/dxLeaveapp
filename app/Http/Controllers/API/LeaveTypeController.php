<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveTypeResource;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leaveTypes = LeaveType::all();
        return ResponseFormatter::success(LeaveTypeResource::collection($leaveTypes), 'Leave types retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'default_entitlement_days' => 'required|integer|min:0',
            'accrual_frequency' => 'sometimes|string|nullable',
            'is_paid' => 'required|boolean',
            'max_carry_over_days' => 'sometimes|integer|min:0|nullable',
            'requires_attachment' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
        }

        $leaveType = LeaveType::create($validator->validated());
        return ResponseFormatter::success(new LeaveTypeResource($leaveType), 'Leave type created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType)
    {
        return ResponseFormatter::success(new LeaveTypeResource($leaveType), 'Leave type retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'default_entitlement_days' => 'sometimes|required|integer|min:0',
            'accrual_frequency' => 'sometimes|string|nullable',
            'is_paid' => 'sometimes|required|boolean',
            'max_carry_over_days' => 'sometimes|integer|min:0|nullable',
            'requires_attachment' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
        }

        $leaveType->update($validator->validated());
        return ResponseFormatter::success(new LeaveTypeResource($leaveType), 'Leave type updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();
        return ResponseFormatter::success(null, 'Leave type deleted successfully');
    }
}