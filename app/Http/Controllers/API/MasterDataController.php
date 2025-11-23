<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\LeaveTypeResource;
use App\Http\Resources\PublicHolidayResource;
use App\Models\Department;
use App\Http\Resources\EmployeeEntitlementResource;
use App\Models\EmployeeEntitlement;
use App\Models\LeaveType;
use App\Models\PublicHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

use App\Http\Resources\UserResource;
use App\Models\User;

use App\Models\Workflow;

class MasterDataController extends Controller
{

    public function getAllMasterData()
    {
        try {
            $users = User::with('roles')->get();
            $leaveTypes = LeaveType::all();
            $workflows = Workflow::all();
            $employeeEntitlements = EmployeeEntitlement::all();

            return ResponseFormatter::success([
                'users' => UserResource::collection($users),
                'leave_types' => LeaveTypeResource::collection($leaveTypes),
                'workflows' => $workflows,
                'employee_entitlements' => EmployeeEntitlementResource::collection($employeeEntitlements),
            ], 'Master data retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve master data: ' . $e->getMessage(), 500);
        }
    }


    //====================== DEPARTMENTS ======================

    public function getDepartments()
    {
        try {
            $departments = Department::all();
            return ResponseFormatter::success(DepartmentResource::collection($departments), 'Departments retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve departments: ' . $e->getMessage(), 500);
        }
    }

    public function createDepartment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:departments,name',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
            }

            $department = Department::create($validator->validated());
            return ResponseFormatter::success(new DepartmentResource($department), 'Department created successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to create department: ' . $e->getMessage(), 500);
        }
    }

    public function updateDepartment(Request $request, Department $department)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
            }

            $department->update($validator->validated());
            return ResponseFormatter::success(new DepartmentResource($department), 'Department updated successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to update department: ' . $e->getMessage(), 500);
        }
    }

    public function deleteDepartment(Department $department)
    {
        try {
            $department->delete();
            return ResponseFormatter::success(null, 'Department deleted successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to delete department: ' . $e->getMessage(), 500);
        }
    }

    //====================== LEAVE TYPES ======================

    public function getLeaveTypes()
    {
        try {
            $leaveTypes = LeaveType::all();
            return ResponseFormatter::success(LeaveTypeResource::collection($leaveTypes), 'Leave types retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve leave types: ' . $e->getMessage(), 500);
        }
    }

    public function createLeaveType(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to create leave type: ' . $e->getMessage(), 500);
        }
    }

    public function updateLeaveType(Request $request, LeaveType $leaveType)
    {
        try {
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
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to update leave type: ' . $e->getMessage(), 500);
        }
    }

    public function deleteLeaveType(LeaveType $leaveType)
    {
        try {
            $leaveType->delete();
            return ResponseFormatter::success(null, 'Leave type deleted successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to delete leave type: ' . $e->getMessage(), 500);
        }
    }

    //====================== PUBLIC HOLIDAYS ======================

    public function getPublicHolidays()
    {
        try {
            $publicHolidays = PublicHoliday::all();
            return ResponseFormatter::success(PublicHolidayResource::collection($publicHolidays), 'Public holidays retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve public holidays: ' . $e->getMessage(), 500);
        }
    }

    public function createPublicHoliday(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to create public holiday: ' . $e->getMessage(), 500);
        }
    }

    public function updatePublicHoliday(Request $request, PublicHoliday $publicHoliday)
    {
        try {
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
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to update public holiday: ' . $e->getMessage(), 500);
        }
    }

    public function deletePublicHoliday(PublicHoliday $publicHoliday)
    {
        try {
            $publicHoliday->delete();
            return ResponseFormatter::success(null, 'Public holiday deleted successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to delete public holiday: ' . $e->getMessage(), 500);
        }
    }

    public function roles()
    {
        try {
            $roles = Role::all();
            return ResponseFormatter::success($roles, 'Roles retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve roles: ' . $e->getMessage(), 500);
        }
    }
}