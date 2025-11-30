<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Department::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('name', 'like', '%' . $search . '%');
            }

            // Sorting functionality
            if ($request->filled('sort_by')) {
                $sortBy = $request->input('sort_by');
                $sortDir = $request->input('sort_dir', 'asc');

                if ($sortBy === 'name') {
                    $query->orderBy($sortBy, $sortDir);
                }
            }

              if ($request->input('all') === 'true') {
                $departments =  $query->get();
            } else {
                $departments = $query->paginate($request->input('per_page', 10));
            }
            
            return ResponseFormatter::success($departments, 'Departments retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve departments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:departments,name',
                'head_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
            }

            $department = Department::create($validator->validated());

            // Auto-assign 'Manager' role
            if ($department->head_id) {
                $head = User::find($department->head_id);
                if ($head) $head->assignRole('Manager');
            }

            return ResponseFormatter::success(new DepartmentResource($department), 'Department created successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to create department: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        try {
            return ResponseFormatter::success(new DepartmentResource($department), 'Department retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve department: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
                'head_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
            }

            $oldHeadId = $department->head_id;

            $department->update($validator->validated());

            // Handle Manager Role
            if ($oldHeadId !== $department->head_id) {
                // Remove role from old head if they don't head any other departments
                if ($oldHeadId) {
                    $oldHead = User::find($oldHeadId);
                    if ($oldHead && $oldHead->departmentsHeaded()->count() == 0) {
                        $oldHead->removeRole('Manager');
                    }
                }
                // Assign to new head
                if ($department->head_id) {
                    $newHead = User::find($department->head_id);
                    if ($newHead) $newHead->assignRole('Manager');
                }
            }

            return ResponseFormatter::success(new DepartmentResource($department), 'Department updated successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to update department: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        try {
            $department->delete();
            return ResponseFormatter::success(null, 'Department deleted successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to delete department: ' . $e->getMessage(), 500);
        }
    }
}