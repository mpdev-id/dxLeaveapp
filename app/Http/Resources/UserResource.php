<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'employee_code' => $this->employee_code,
            'role'       => $this->roles->pluck('name'), // Assuming Spatie Roles are used
            'status'     => $this->status,
            'manager_name' => $this->manager ? $this->manager->name : null,
            'members' => $this->subordinates->map(function ($subordinate) {
                return [
                    'id'   => $subordinate->id,
                    'name' => $subordinate->name,
                    'email'=> $subordinate->email,
                    'employee_code' => $subordinate->employee_code,
                    'sisa_cuti' => $subordinate->entitlements->mapWithKeys(function ($entitlement) {
                        return [
                            'tahun' =>  $entitlement->year,
                            $entitlement->leaveType->name => ($entitlement->initial_balance - $entitlement->days_taken + $entitlement->carry_over_days),
                            'Annual Leave Terpakai' =>  $entitlement->days_taken,
                        ];
                    }),
                ];
            })->values()->all(),
            'department' => $this->department->name,
            'hire_date'  => $this->hire_date,
            'sisa_cuti' => $this->entitlements->mapWithKeys(function ($entitlement) {
                return [
                    'tahun' =>  $entitlement->year,
                    $entitlement->leaveType->name => ($entitlement->initial_balance - $entitlement->days_taken + $entitlement->carry_over_days),
                    'Annual Leave Terpakai' =>  $entitlement->days_taken,
                ];
            }),
            'leaveRequests' => $this->leaveRequests->map(function ($leaveRequest) {
                return [
                    'id' => $leaveRequest->id,
                    'leave_type' => $leaveRequest->leaveType->name,
                    'start_date' => $leaveRequest->start_date,
                    'end_date' => $leaveRequest->end_date,
                    'status' => $leaveRequest->status,
                ];
            })->values()->all(),
          
        ];
    }
}
