<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'employee_code',
        'email',
        'phone_number',
        'password',
        'department_id',
        'manager_id',
        'status',
        'status',
        'hire_date',
        'signature_path',
        'plant_id',
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

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function teamsLed(): HasMany
    {
        return $this->hasMany(Team::class, 'leader_id');
    }

    public function additionalTeamsLed(): HasMany
    {
        return $this->hasMany(Team::class, 'additional_leader_id');
    }

    public function plantsSupervised(): HasMany
    {
        return $this->hasMany(Plant::class, 'supervisor_id');
    }

    public function departmentsHeaded(): HasMany
    {
        return $this->hasMany(Department::class, 'head_id');
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

    /**
     * Route notifications for the WhatsApp channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string|null
     */
    public function routeNotificationForWhatsApp($notification)
    {
        return $this->phone_number;
    }

    /**
     * Accessor for signature_path to get full URL.
     */
    public function getSignatureUrlAttribute()
    {
        if ($this->signature_path) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->signature_path);
        }
        return null;
    }
}
