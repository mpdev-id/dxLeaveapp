<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicHolidayResource;
use App\Models\PublicHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicHolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = PublicHoliday::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('name', 'like', '%' . $search . '%');
            }

            // Sorting functionality
            if ($request->filled('sort_by')) {
                $sortBy = $request->input('sort_by');
                $sortDir = $request->input('sort_dir', 'asc');

                $allowedSorts = ['name', 'date'];
                if (in_array($sortBy, $allowedSorts)) {
                    $query->orderBy($sortBy, $sortDir);
                }
            }

            if ($request->input('all') === 'true') {
                $holidays = $query->get();
            } else {
                $holidays = $query->paginate($request->input('per_page', 10));
            }

            return ResponseFormatter::success($holidays, 'Public holidays retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve public holidays: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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

            $holiday = PublicHoliday::create($validator->validated());
            return ResponseFormatter::success(new PublicHolidayResource($holiday), 'Public holiday created successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to create public holiday: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicHoliday $publicHoliday)
    {
        try {
            return ResponseFormatter::success(new PublicHolidayResource($publicHoliday), 'Public holiday retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve public holiday: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicHoliday $publicHoliday)
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicHoliday $publicHoliday)
    {
        try {
            $publicHoliday->delete();
            return ResponseFormatter::success(null, 'Public holiday deleted successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to delete public holiday: ' . $e->getMessage(), 500);
        }
    }
}