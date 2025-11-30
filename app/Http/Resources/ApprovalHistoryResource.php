<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalHistoryResource extends JsonResource
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
            'approver' => new UserResource($this->whenLoaded('approver')),
            'status' => $this->action, // 'action' field in DB holds the status like 'Approved'
            'step' => $this->whenLoaded('step', function () {
                return [
                    'id' => $this->step->id,
                    'name' => $this->step->name,
                    'step_number' => $this->step->step_number,
                ];
            }),
            'workflow_step_id' => $this->workflow_step_id,
            'comments' => $this->comments,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
