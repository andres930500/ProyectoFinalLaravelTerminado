<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
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
        'address',
        'price_per_hour',
        'image',
        'images',
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

    public function formattedAddress(): ?string
    {
        if (blank($this->address)) {
            return null;
        }

        return trim($this->address);
    }

    public function googleMapsEmbedUrl(): ?string
    {
        $query = $this->mapQuery();

        if ($query === null) {
            return null;
        }

        return 'https://www.google.com/maps?q='.urlencode($query).'&output=embed';
    }

    public function googleMapsUrl(): ?string
    {
        $query = $this->mapQuery();

        if ($query === null) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.urlencode($query);
    }

    public function getImageAttribute(?string $value): ?string
    {
        if (filled($value)) {
            return $this->normalizeImagePath($value);
        }

        $images = $this->decodeStoredImages($this->attributes['images'] ?? null);

        return $this->normalizeImagePath($images[0] ?? null);
    }

    public function getImagesAttribute(mixed $value): array
    {
        $images = $this->decodeStoredImages($value);

        if ($images === [] && filled($this->attributes['image'] ?? null)) {
            $images = [$this->attributes['image']];
        }

        return array_values(array_filter(
            array_map(fn (?string $path) => $this->normalizeImagePath($path), $images)
        ));
    }

    public function setImagesAttribute(mixed $value): void
    {
        $images = collect(is_array($value) ? $value : [])
            ->filter(fn ($item) => filled($item))
            ->take(3)
            ->values()
            ->all();

        $this->attributes['images'] = $images === []
            ? null
            : json_encode($images, JSON_UNESCAPED_SLASHES);
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
        $slotMinutes = $this->slotMinutes();
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

                $cursor = $this->alignToDaySlotBoundary($rangeStart->copy());

                if ($date->isToday() && $cursor->lessThan($now)) {
                    $cursor = $this->alignToDaySlotBoundary($now->copy());
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

    public function getDailyReservationSlots(Carbon $date): array
    {
        $slotMinutes = $this->slotMinutes();
        $slots = [];

        if (! $this->is_active) {
            return [];
        }

        $dayAvailabilities = $this->availabilities()
            ->where('day_of_week', $date->dayOfWeek)
            ->orderBy('start_time')
            ->get();

        if ($dayAvailabilities->isEmpty()) {
            return [];
        }

        $reservations = $this->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('start_time', $date->toDateString())
            ->get();

        $blockedSlots = $this->blockedSlots()
            ->whereDate('start_time', $date->toDateString())
            ->get();

        foreach ($dayAvailabilities as $availability) {
            $rangeStart = Carbon::parse($date->toDateString().' '.$availability->start_time);
            $rangeEnd = Carbon::parse($date->toDateString().' '.$availability->end_time);

            if ($rangeEnd->lessThanOrEqualTo($rangeStart)) {
                continue;
            }

            $cursor = $this->alignToDaySlotBoundary($rangeStart->copy());

            while ($cursor->copy()->addMinutes($slotMinutes)->lessThanOrEqualTo($rangeEnd)) {
                $slotStart = $cursor->copy();
                $slotEnd = $cursor->copy()->addMinutes($slotMinutes);

                $status = 'available';
                $message = 'Disponible para reservar.';

                if ($slotStart->lessThan(now())) {
                    $status = 'past';
                    $message = 'Horario no disponible porque ya paso.';
                } else {
                    $blocked = $blockedSlots->first(
                        fn (BlockedSlot $blockedSlot) => $blockedSlot->start_time < $slotEnd && $blockedSlot->end_time > $slotStart
                    );

                    $reservation = $reservations->first(
                        fn (Reservation $reservation) => $reservation->start_time < $slotEnd && $reservation->end_time > $slotStart
                    );

                    if ($blocked) {
                        $status = 'blocked';
                        $message = $blocked->reason
                            ? 'No disponible: '.$blocked->reason
                            : 'Horario bloqueado por administracion.';
                    } elseif ($reservation) {
                        $status = $reservation->status === 'confirmed' ? 'reserved_confirmed' : 'reserved_pending';
                        $message = $reservation->status === 'confirmed'
                            ? 'No disponible: ya fue reservada y confirmada.'
                            : 'No disponible: hay una solicitud pendiente de aprobacion.';
                    }
                }

                $slots[] = [
                    'start' => $slotStart->toDateTimeString(),
                    'end' => $slotEnd->toDateTimeString(),
                    'time' => $slotStart->format('H:i'),
                    'label' => sprintf('%s - %s', $slotStart->format('H:i'), $slotEnd->format('H:i')),
                    'status' => $status,
                    'is_available' => $status === 'available',
                    'message' => $message,
                ];

                $cursor->addMinutes($slotMinutes);
            }
        }

        return $slots;
    }

    public function isStartAlignedToSlot(Carbon $start): bool
    {
        $minutesFromMidnight = ((int) $start->format('G') * 60) + (int) $start->format('i');

        return $minutesFromMidnight % $this->slotMinutes() === 0 && (int) $start->format('s') === 0;
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

    protected function alignToDaySlotBoundary(Carbon $candidate): Carbon
    {
        $slotMinutes = $this->slotMinutes();
        $minutesFromMidnight = ((int) $candidate->format('G') * 60) + (int) $candidate->format('i');
        $remainder = $minutesFromMidnight % $slotMinutes;

        $aligned = $candidate->copy()->second(0);

        if ($remainder !== 0) {
            $aligned->addMinutes($slotMinutes - $remainder);
        }

        return $aligned;
    }

    protected function slotMinutes(): int
    {
        return max(1, (int) env('RESERVATION_SLOT_MINUTES', 60));
    }

    protected function mapQuery(): ?string
    {
        $address = $this->formattedAddress();

        if ($address === null) {
            return null;
        }

        $query = $address;

        if (! Str::contains(Str::lower($query), 'manizales')) {
            $query .= ', Manizales, Caldas, Colombia';
        }

        return $query;
    }

    protected function decodeStoredImages(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || blank($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function normalizeImagePath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, '/')) {
            return $path;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $this->normalizeLocalAbsoluteUrl($path);
        }

        if (Str::startsWith($path, 'storage/')) {
            return '/'.ltrim($path, '/');
        }

        if (Str::startsWith($path, 'public/')) {
            return '/storage/'.ltrim(Str::after($path, 'public/'), '/');
        }

        return $this->normalizeLocalAbsoluteUrl(Storage::disk('public')->url($path));
    }

    protected function normalizeLocalAbsoluteUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $query = parse_url($url, PHP_URL_QUERY);
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        $shouldUseRelativePath = in_array($host, ['localhost', '127.0.0.1', $appHost], true)
            || ($host === null && $scheme === null);

        if (! $shouldUseRelativePath) {
            return $url;
        }

        return $query ? $path.'?'.$query : $path;
    }
}
