<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'applicable_model'];

    /**
     * Relasi One-to-Many: Satu Workflow memiliki banyak Langkah (WorkflowStep).
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('step_number');
    }

    /**
     * Relasi One-to-Many: Satu Workflow dapat digunakan oleh banyak Pengajuan Cuti (LeaveRequest).
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
