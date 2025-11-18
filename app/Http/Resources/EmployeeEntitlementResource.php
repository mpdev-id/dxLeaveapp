<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\LeaveTypeResource;

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
            'name'=>$this->user->name,
            'year' => $this->year,
            'initial_balance' => $this->initial_balance,
            'days_taken' => $this->days_taken,
            'carry_over_days' => $this->carry_over_days,
            'user_id' => $this->user_id,
            'leave_type_id' => $this->leave_type_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}