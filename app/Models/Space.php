<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class Space extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'capacity',
        'description',
        'rules',
        'price_per_hour',
        'image',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_per_hour' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Space $space): void {
            if (blank($space->slug) && filled($space->name)) {
                $space->slug = static::generateUniqueSlug($space->name);
            }
        });

        static::updating(function (Space $space): void {
            if ($space->isDirty('name') && blank($space->slug)) {
                $space->slug = static::generateUniqueSlug($space->name, $space->getKey());
            }
        });
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function blockedSlots(): HasMany
    {
        return $this->hasMany(BlockedSlot::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        $price = (float) ($this->price_per_hour ?? 0);

        if ($price <= 0) {
            return 'Gratis';
        }

        return Number::currency($price, 'COP', locale: 'es_CO');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isAvailableForSlot(Carbon $start, Carbon $end): bool
    {
        if (! $this->is_active || $start->greaterThanOrEqualTo($end) || ! $start->isSameDay($end)) {
            return false;
        }

        $dayOfWeek = $start->dayOfWeek;
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');

        $hasAvailability = $this->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>=', $endTime)
            ->exists();

        if (! $hasAvailability) {
            return false;
        }

        $hasBlockedSlot = $this->blockedSlots()
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        if ($hasBlockedSlot) {
            return false;
        }

        $hasReservationConflict = $this->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        return ! $hasReservationConflict;
    }

    public function getNextAvailableSlots(int $count = 5): array
    {
        $count = max(1, $count);
        $slotMinutes = max(1, (int) env('RESERVATION_SLOT_MINUTES', 60));
        $now = now();
        $slots = [];

        $availabilities = $this->availabilities()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        if ($availabilities->isEmpty() || ! $this->is_active) {
            return [];
        }

        for ($offset = 0; $offset < 60 && count($slots) < $count; $offset++) {
            $date = $now->copy()->startOfDay()->addDays($offset);
            $dayAvailabilities = $availabilities->get($date->dayOfWeek, collect());

            foreach ($dayAvailabilities as $availability) {
                $rangeStart = Carbon::parse($date->toDateString().' '.$availability->start_time);
                $rangeEnd = Carbon::parse($date->toDateString().' '.$availability->end_time);

                if ($rangeEnd->lessThanOrEqualTo($rangeStart)) {
                    continue;
                }

                $cursor = $rangeStart->copy();

                if ($date->isToday() && $cursor->lessThan($now)) {
                    $cursor = $this->alignToSlotBoundary($now->copy(), $rangeStart, $slotMinutes);
                }

                while ($cursor->copy()->addMinutes($slotMinutes)->lessThanOrEqualTo($rangeEnd) && count($slots) < $count) {
                    $slotStart = $cursor->copy();
                    $slotEnd = $cursor->copy()->addMinutes($slotMinutes);

                    if ($this->isAvailableForSlot($slotStart, $slotEnd)) {
                        $slots[] = [
                            'start' => $slotStart,
                            'end' => $slotEnd,
                        ];
                    }

                    $cursor->addMinutes($slotMinutes);
                }
            }
        }

        return $slots;
    }

    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::random(8));
        $slug = $baseSlug;
        $suffix = 1;

        while (static::query()
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function alignToSlotBoundary(Carbon $candidate, Carbon $reference, int $slotMinutes): Carbon
    {
        if ($candidate->lessThanOrEqualTo($reference)) {
            return $reference->copy();
        }

        $elapsedMinutes = $reference->diffInMinutes($candidate);
        $remainder = $elapsedMinutes % $slotMinutes;

        if ($remainder === 0) {
            return $candidate->copy()->second(0);
        }

        return $candidate->copy()->addMinutes($slotMinutes - $remainder)->second(0);
    }
}
