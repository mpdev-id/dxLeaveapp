<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'duration_days',
        'reason',
        'supporting_attachment_path',
        'current_status',
        'workflow_id',
        'current_workflow_step_id',
    ];

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
}
