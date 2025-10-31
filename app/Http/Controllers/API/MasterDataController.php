<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\LeaveTypeResource;
use App\Http\Resources\PublicHolidayResource;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\PublicHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterDataController extends Controller
{
    //====================== DEPARTMENTS ======================

    public function getDepartments()
    {
        $departments = Department::all();
        return ResponseFormatter::success(DepartmentResource::collection($departments), 'Departments retrieved successfully');
    }

    public function createDepartment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
        }

        $department = Department::create($validator->validated());
        return ResponseFormatter::success(new DepartmentResource($department), 'Department created successfully');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
        }

        $department->update($validator->validated());
        return ResponseFormatter::success(new DepartmentResource($department), 'Department updated successfully');
    }

    public function deleteDepartment(Department $department)
    {
        $department->delete();
        return ResponseFormatter::success(null, 'Department deleted successfully');
    }

    //====================== LEAVE TYPES ======================

    public function getLeaveTypes()
    {
        $leaveTypes = LeaveType::all();
        return ResponseFormatter::success(LeaveTypeResource::collection($leaveTypes), 'Leave types retrieved successfully');
    }

    public function createLeaveType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'default_entitlement_days' => 'required|integer|min:0',
            'accrual_frequency' => 'sometimes|string',
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

    public function updateLeaveType(Request $request, LeaveType $leaveType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'default_entitlement_days' => 'sometimes|required|integer|min:0',
            'accrual_frequency' => 'sometimes|string',
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

    public function deleteLeaveType(LeaveType $leaveType)
    {
        $leaveType->delete();
        return ResponseFormatter::success(null, 'Leave type deleted successfully');
    }

    //====================== PUBLIC HOLIDAYS ======================

    public function getPublicHolidays()
    {
        $publicHolidays = PublicHoliday::all();
        return ResponseFormatter::success(PublicHolidayResource::collection($publicHolidays), 'Public holidays retrieved successfully');
    }

    public function createPublicHoliday(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date_format:Y-m-d',
            // 'region_id' => 'sometimes|integer|nullable',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
        }

        $publicHoliday = PublicHoliday::create($validator->validated());
        return ResponseFormatter::success(new PublicHolidayResource($publicHoliday), 'Public holiday created successfully');
    }

    public function updatePublicHoliday(Request $request, PublicHoliday $publicHoliday)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date_format:Y-m-d',
            // 'region_id' => 'sometimes|integer|nullable',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
        }

        $publicHoliday->update($validator->validated());
        return ResponseFormatter::success(new PublicHolidayResource($publicHoliday), 'Public holiday updated successfully');
    }

    public function deletePublicHoliday(PublicHoliday $publicHoliday)
    {
        $publicHoliday->delete();
        return ResponseFormatter::success(null, 'Public holiday deleted successfully');
    }
}