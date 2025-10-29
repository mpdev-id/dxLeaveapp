<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'employee_code',
        'email',
        'password',
        'department_id',
        'manager_id',
        'status',
        'hire_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    /**
     * Relasi One-to-One (Inverse): Karyawan dimiliki oleh satu Manager (jika ada).
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Relasi One-to-Many: Karyawan adalah Manager dari banyak Bawahannya (Subordinates).
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    // --- Relasi Core ---

    /**
     * Relasi Belongs To: Karyawan milik satu Departemen.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // --- Relasi LMS ---

    /**
     * Relasi One-to-Many: Karyawan memiliki banyak Jatah Cuti (Entitlements).
     */
    public function entitlements(): HasMany
    {
        return $this->hasMany(EmployeeEntitlement::class);
    }

    /**
     * Relasi One-to-Many: Karyawan membuat banyak Pengajuan Cuti (LeaveRequests).
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Relasi One-to-Many: Karyawan adalah Approver dari banyak Riwayat Persetujuan (ApprovalsHistory).
     */
    public function approvalsGiven(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class, 'approver_user_id');
    }
}
