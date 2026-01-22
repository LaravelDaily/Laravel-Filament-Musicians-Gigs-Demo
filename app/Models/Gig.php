<?php

namespace App\Models;

use App\Enums\GigStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Gig extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'date',
        'call_time',
        'performance_time',
        'end_time',
        'venue_name',
        'venue_address',
        'client_contact_name',
        'client_contact_phone',
        'client_contact_email',
        'dress_code',
        'notes',
        'pay_info',
        'region_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'call_time' => 'datetime:H:i',
            'performance_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'status' => GigStatus::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['application/pdf']);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(GigAssignment::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', GigStatus::Active);
    }
}
