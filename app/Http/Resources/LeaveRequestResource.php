<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\LeaveTypeResource;
use App\Http\Resources\ApprovalHistoryResource;

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
            'id' => $this->uuid,
            'user' => new UserResource($this->whenLoaded('user')),
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'leave_type_id' => $this->leave_type_id,
            'workflow' => $this->whenLoaded('workflow'),
            'workflow_id' => $this->workflow_id,
            'approvals' => ApprovalHistoryResource::collection($this->whenLoaded('approvals')),
            
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'leave_period' => $this->leave_period,
            'duration_days' => $this->duration_days,
            
            'reason' => $this->reason,
            'supporting_attachment_path' => $this->supporting_attachment_path,
            
            'current_status' => $this->current_status,
            'current_step' => $this->whenLoaded('currentStep'),
            'remaining_leave_balance' => $this->remaining_leave_balance,

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
