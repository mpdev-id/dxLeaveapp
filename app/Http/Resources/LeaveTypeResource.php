<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
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
            'name' => $this->name,
            'default_entitlement_days' => $this->default_entitlement_days,
            'accrual_frequency' => $this->accrual_frequency,
            'is_paid' => $this->is_paid,
            'max_carry_over_days' => $this->max_carry_over_days,
            'requires_attachment' => $this->requires_attachment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
