<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_name' => $this->whenLoaded('user', $this->user->name),
            'user_department' => $this->whenLoaded('user', function() {
                return $this->user->department->name ?? null;
            }),
            'leave_type' => $this->whenLoaded('leaveType', $this->leaveType->name),
            'start_date' => $this->start_date->format('d-m-Y'),
            'end_date' => $this->end_date->format('d-m-Y'),
            'duration_days' => $this->duration_days,
            'reason' => $this->reason,
            'status' => $this->current_status,
            'created_at' => $this->created_at->format('d-m-Y H:i'),
            'approval_chain' => $this->whenLoaded('workflow', function() {
                return $this->workflow->steps->sortBy('step_number')->map(function($step) {
                    $approval = $this->approvals->firstWhere('workflow_step_id', $step->id);
                    // dd($approval);
                    return [
                        'step' => $step->step_number,
                        'approver_role' => $step->approverRole?->name ?? 'N/A',
                        'status' => $approval?->action ?? 'Pending',
                        'approver_name' => $approval?->approver?->name,
                        'comments' => $approval?->comments,
                        'action_date' => $approval?->created_at?->format('d-m-Y H:i'),
                    ];
                })->values();
            }),
        ];
    }
}
