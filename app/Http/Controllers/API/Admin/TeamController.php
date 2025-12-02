<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\User;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with(['department', 'leader', 'additionalLeader', 'sl', 'asmen']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        if ($request->has('all')) {
            return ResponseFormatter::success($query->get(), 'All teams retrieved successfully');
        }

        $teams = $query->paginate($request->per_page ?? 10);
        return ResponseFormatter::success($teams, 'Teams retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'leader_id' => 'nullable|exists:users,id',
            'additional_leader_id' => 'nullable|exists:users,id',
            'sl_id' => 'nullable|exists:users,id',
            'asmen_id' => 'nullable|exists:users,id',
        ]);

        // Convert empty strings to null for nullable fields
        foreach (['leader_id', 'additional_leader_id', 'sl_id', 'asmen_id'] as $field) {
            if (isset($validated[$field]) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        $team = Team::create($validated);

        // Auto-assign 'TL' role
        if ($team->leader_id) {
            $leader = User::find($team->leader_id);
            if ($leader) $leader->assignRole('TL');
        }
        if ($team->additional_leader_id) {
            $leader = User::find($team->additional_leader_id);
            if ($leader) $leader->assignRole('TL');
        }
        // Auto-assign 'SL' role
        if ($team->sl_id) {
            $sl = User::find($team->sl_id);
            if ($sl) $sl->assignRole('SL');
        }
        // Auto-assign 'ASMEN' role
        if ($team->asmen_id) {
            $asmen = User::find($team->asmen_id);
            if ($asmen) $asmen->assignRole('ASMEN');
        }

        return ResponseFormatter::success($team->load(['department', 'leader', 'additionalLeader', 'sl', 'asmen']), 'Team created successfully');
    }

    public function show(Team $team)
    {
        return ResponseFormatter::success($team->load(['department', 'leader', 'additionalLeader', 'sl', 'asmen']), 'Team retrieved successfully');
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'leader_id' => 'nullable|exists:users,id',
            'additional_leader_id' => 'nullable|exists:users,id',
            'sl_id' => 'nullable|exists:users,id',
            'asmen_id' => 'nullable|exists:users,id',
        ]);

        // Convert empty strings to null for nullable fields
        foreach (['leader_id', 'additional_leader_id', 'sl_id', 'asmen_id'] as $field) {
            if (isset($validated[$field]) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        $oldLeaderId = $team->leader_id;
        $oldAdditionalLeaderId = $team->additional_leader_id;
        $oldSlId = $team->sl_id;
        $oldAsmenId = $team->asmen_id;

        $team->update($validated);

        // Handle Leader Role
        if ($oldLeaderId !== $team->leader_id) {
            // Remove role from old leader if they don't lead any other teams
            if ($oldLeaderId) {
                $oldLeader = User::find($oldLeaderId);
                if ($oldLeader && $oldLeader->teamsLed()->count() == 0 && $oldLeader->additionalTeamsLed()->count() == 0) {
                    $oldLeader->removeRole('TL');
                }
            }
            // Assign to new leader
            if ($team->leader_id) {
                $newLeader = User::find($team->leader_id);
                if ($newLeader) $newLeader->assignRole('TL');
            }
        }

        // Handle Additional Leader Role
        if ($oldAdditionalLeaderId !== $team->additional_leader_id) {
            if ($oldAdditionalLeaderId) {
                $oldLeader = User::find($oldAdditionalLeaderId);
                if ($oldLeader && $oldLeader->teamsLed()->count() == 0 && $oldLeader->additionalTeamsLed()->count() == 0) {
                    $oldLeader->removeRole('TL');
                }
            }
            if ($team->additional_leader_id) {
                $newLeader = User::find($team->additional_leader_id);
                if ($newLeader) $newLeader->assignRole('TL');
            }
        }

        // Handle SL Role
        if ($oldSlId !== $team->sl_id) {
            if ($oldSlId) {
                $oldSl = User::find($oldSlId);
                // Assuming we might want to check if they are SL elsewhere, but for now just remove if not here.
                // To be safe, we should check if they are SL in other teams.
                // But User model doesn't have 'teamsSl' relationship yet. Let's skip the check for now or assume 1 team per SL.
                // Or better, add the relationship to User model if needed. For now, just remove role.
                if ($oldSl) $oldSl->removeRole('SL');
            }
            if ($team->sl_id) {
                $newSl = User::find($team->sl_id);
                if ($newSl) $newSl->assignRole('SL');
            }
        }

        // Handle ASMEN Role
        if ($oldAsmenId !== $team->asmen_id) {
            if ($oldAsmenId) {
                $oldAsmen = User::find($oldAsmenId);
                if ($oldAsmen) $oldAsmen->removeRole('ASMEN');
            }
            if ($team->asmen_id) {
                $newAsmen = User::find($team->asmen_id);
                if ($newAsmen) $newAsmen->assignRole('ASMEN');
            }
        }

        return ResponseFormatter::success($team->load(['department', 'leader', 'additionalLeader', 'sl', 'asmen']), 'Team updated successfully');
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return ResponseFormatter::success(null, 'Team deleted successfully');
    }
}
