<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'workflow_step_id',
        'approver_user_id',
        'action',
        'comments',
    ];

    /**
     * Relasi Polymorphic To: Mengembalikan model yang disetujui/ditolak (misalnya, LeaveRequest).
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relasi Belongs To: Merujuk ke langkah workflow yang memicu persetujuan ini.
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    /**
     * Relasi Belongs To: Merujuk ke Karyawan (User) yang memberikan persetujuan.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
