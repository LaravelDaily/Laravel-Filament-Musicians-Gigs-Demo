<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GigAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'gig_id',
        'user_id',
        'instrument_id',
        'status',
        'pay_amount',
        'notes',
        'responded_at',
        'subout_reason',
        'decline_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssignmentStatus::class,
            'pay_amount' => 'decimal:2',
            'responded_at' => 'datetime',
        ];
    }

    public function gig(): BelongsTo
    {
        return $this->belongsTo(Gig::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(AssignmentStatusLog::class);
    }
}
