<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plant;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use App\Models\User;

class PlantController extends Controller
{
    public function index(Request $request)
    {
        $query = Plant::with(['team.department', 'supervisor']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->has('all')) {
            return ResponseFormatter::success($query->get(), 'All plants retrieved successfully');
        }

        $plants = $query->paginate($request->per_page ?? 10);
        return ResponseFormatter::success($plants, 'Plants retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        $plant = Plant::create($validated);

        // Auto-assign 'SPV' role
        if ($plant->supervisor_id) {
            $supervisor = User::find($plant->supervisor_id);
            if ($supervisor) $supervisor->assignRole('SPV');
        }

        return ResponseFormatter::success($plant->load(['team.department', 'supervisor']), 'Plant created successfully');
    }

    public function show(Plant $plant)
    {
        return ResponseFormatter::success($plant->load(['team.department', 'supervisor']), 'Plant retrieved successfully');
    }

    public function update(Request $request, Plant $plant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        $oldSupervisorId = $plant->supervisor_id;

        $plant->update($validated);

        // Handle Supervisor Role
        if ($oldSupervisorId !== $plant->supervisor_id) {
            // Remove role from old supervisor if they don't supervise any other plants
            if ($oldSupervisorId) {
                $oldSupervisor = User::find($oldSupervisorId);
                if ($oldSupervisor && $oldSupervisor->plantsSupervised()->count() == 0) {
                    $oldSupervisor->removeRole('SPV');
                }
            }
            // Assign to new supervisor
            if ($plant->supervisor_id) {
                $newSupervisor = User::find($plant->supervisor_id);
                if ($newSupervisor) $newSupervisor->assignRole('SPV');
            }
        }

        return ResponseFormatter::success($plant->load(['team.department', 'supervisor']), 'Plant updated successfully');
    }

    public function destroy(Plant $plant)
    {
        $plant->delete();
        return ResponseFormatter::success(null, 'Plant deleted successfully');
    }
}
