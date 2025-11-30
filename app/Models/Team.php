<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'department_id', 'leader_id', 'additional_leader_id', 'sl_id', 'asmen_id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function additionalLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'additional_leader_id');
    }

    public function sl(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sl_id');
    }

    public function asmen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asmen_id');
    }

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class);
    }
}
