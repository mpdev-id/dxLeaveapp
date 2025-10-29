<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'step_number',
        'required_approver_type',
        'approver_role_id',
        'approver_user_id',
        'required_approvals',
        'is_final_step',
    ];

    /**
     * Relasi Belongs To: Langkah milik satu Workflow.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Relasi Belongs To: Jika approver adalah Role (menggunakan Spatie Role Model).
     */
    public function approverRole(): BelongsTo
    {
        // Spatie menggunakan Model Role di namespace-nya sendiri
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'approver_role_id');
    }

    /**
     * Relasi Belongs To: Jika approver adalah User spesifik.
     */
    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
