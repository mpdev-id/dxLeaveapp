<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_entitlement_days',
        'accrual_frequency',
        'is_paid',
        'max_carry_over_days',
        'requires_attachment',
    ];

    /**
     * Relasi One-to-Many: Satu Jenis Cuti memiliki banyak Pengajuan Cuti (LeaveRequest).
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Relasi One-to-Many: Satu Jenis Cuti memiliki banyak Jatah Karyawan (EmployeeEntitlement).
     */
    public function entitlements(): HasMany
    {
        return $this->hasMany(EmployeeEntitlement::class);
    }
}
