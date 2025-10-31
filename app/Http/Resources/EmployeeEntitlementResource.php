<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeEntitlementResource extends JsonResource
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
            'year' => $this->year,
            'initial_balance' => $this->initial_balance,
            'days_taken' => $this->days_taken,
            'carry_over_days' => $this->carry_over_days,
            'user' => new UserResource($this->whenLoaded('user')),
            'leave_type' => $this->whenLoaded('leaveType'),
        ];
    }
}