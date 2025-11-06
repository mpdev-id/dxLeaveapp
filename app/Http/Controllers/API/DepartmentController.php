<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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

        $departments = $query->paginate($request->input('per_page', 10));

        return ResponseFormatter::success($departments, 'Departments retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return ResponseFormatter::success(new DepartmentResource($department), 'Department retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();
        return ResponseFormatter::success(null, 'Department deleted successfully');
    }
}