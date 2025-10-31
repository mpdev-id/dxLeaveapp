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
    public function index()
    {
        $holidays = PublicHoliday::all();
        return ResponseFormatter::success(PublicHolidayResource::collection($holidays), 'Public holidays retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicHoliday $publicHoliday)
    {
        return ResponseFormatter::success(new PublicHolidayResource($publicHoliday), 'Public holiday retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicHoliday $publicHoliday)
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicHoliday $publicHoliday)
    {
        $publicHoliday->delete();
        return ResponseFormatter::success(null, 'Public holiday deleted successfully');
    }
}