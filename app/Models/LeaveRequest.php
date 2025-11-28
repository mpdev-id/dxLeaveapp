<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'leave_period',
        'duration_days',
        'reason',
        'leave_address',
        'supporting_attachment_path',
        'signature_path',
        'current_status',
        'workflow_id',
        'current_workflow_step_id',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relasi Belongs To: Pengajuan cuti dibuat oleh satu Karyawan (User).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi Belongs To: Pengajuan cuti menggunakan satu Jenis Cuti (LeaveType).
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Relasi Belongs To: Pengajuan cuti menggunakan satu Workflow.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Relasi Belongs To: Pengajuan cuti berada pada satu Langkah Workflow saat ini.
     */
    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'current_workflow_step_id');
    }

    /**
     * Relasi Polymorphic Many: Riwayat Persetujuan (ApprovalHistory) terkait.
     */
    public function approvals(): MorphMany
    {
        return $this->morphMany(ApprovalHistory::class, 'approvable');
    }

    /**
     * Accessor for supporting_attachment_path.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getSupportingAttachmentPathAttribute($value)
    {
        if ($value) {
            // Check if the value is already a full URL
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            }
            return Storage::disk('public')->url($value);
        }
        return null;
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
