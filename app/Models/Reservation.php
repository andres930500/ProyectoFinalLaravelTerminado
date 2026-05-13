<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'space_id',
        'start_time',
        'end_time',
        'status',
        'user_name',
        'user_email',
        'user_phone',
        'notes',
        'slug',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'status' => 'string',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Reservation $reservation): void {
            if (blank($reservation->slug)) {
                $reservation->slug = static::generateUniqueSlug();
            }
        });
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeBySpace(Builder $query, int|string $spaceId): Builder
    {
        return $query->where('space_id', $spaceId);
    }

    public function scopeByDate(Builder $query, mixed $date): Builder
    {
        return $query->whereDate('start_time', $date);
    }

    public function getDurationInMinutes(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    public function getTotalPrice(): float
    {
        $pricePerHour = (float) ($this->space?->price_per_hour ?? 0);

        if ($pricePerHour <= 0) {
            return 0.0;
        }

        return round(($this->getDurationInMinutes() / 60) * $pricePerHour, 2);
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeRejected(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeCancelled(): bool
    {
        return $this->status === 'confirmed';
    }

    public function canBeFinished(): bool
    {
        return $this->status === 'confirmed';
    }

    protected static function generateUniqueSlug(): string
    {
        do {
            $slug = Str::lower(Str::random(10));
        } while (static::where('slug', $slug)->exists());

        return $slug;
    }
}
