<?php

namespace App\Http\Controllers;

use App\Models\BlockedSlot;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $spaces = Space::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $space = $spaces->firstWhere('slug', $request->query('space')) ?? $spaces->first();

        $weekStart = $this->resolveWeekStart($request->query('week'));
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY)->setTime(23, 59, 59);
        $slotMinutes = $this->slotMinutes();

        $reservations = collect();
        $blockedSlots = collect();
        $availabilities = collect();
        $calendar = [];

        if ($space) {
            $space->load(['availabilities' => fn ($query) => $query->orderBy('day_of_week')->orderBy('start_time')]);
            $availabilities = $space->availabilities;

            $reservations = Reservation::query()
                ->where('space_id', $space->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('start_time', '<=', $weekEnd)
                ->where('end_time', '>=', $weekStart)
                ->get();

            $blockedSlots = BlockedSlot::query()
                ->where('space_id', $space->id)
                ->where('start_time', '<=', $weekEnd)
                ->where('end_time', '>=', $weekStart)
                ->get();

            $calendar = $this->buildCalendar(
                $space,
                $availabilities,
                $reservations,
                $blockedSlots,
                $weekStart,
                $slotMinutes
            );
        }

        return Inertia::render('Admin/Calendar', [
            'space' => $space,
            'spaces' => $spaces,
            'weekStart' => $weekStart->toDateString(),
            'weekEnd' => $weekEnd->toDateString(),
            'calendar' => $calendar,
            'prevWeek' => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek' => $weekStart->copy()->addWeek()->toDateString(),
        ]);
    }

    protected function buildCalendar(
        Space $space,
        $availabilities,
        $reservations,
        $blockedSlots,
        Carbon $weekStart,
        int $slotMinutes
    ): array {
        $calendar = [];
        $availabilityByDay = $availabilities->groupBy('day_of_week');

        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $date = $weekStart->copy()->addDays($dayOffset);
            $dayAvailabilities = $availabilityByDay->get($date->dayOfWeek, collect());
            $slots = [];

            for ($minutes = 0; $minutes < 24 * 60; $minutes += $slotMinutes) {
                $slotStart = $date->copy()->startOfDay()->addMinutes($minutes);
                $slotEnd = $slotStart->copy()->addMinutes($slotMinutes);
                $status = 'closed';

                $isWithinAvailability = $dayAvailabilities->contains(function ($availability) use ($slotStart, $slotEnd) {
                    $startTime = $slotStart->format('H:i:s');
                    $endTime = $slotEnd->format('H:i:s');

                    return $availability->start_time <= $startTime && $availability->end_time >= $endTime;
                });

                if ($isWithinAvailability) {
                    $status = 'available';
                    $meta = null;

                    $blocked = $blockedSlots->first(fn ($blockedSlot) => $blockedSlot->start_time < $slotEnd && $blockedSlot->end_time > $slotStart);
                    $reservation = $reservations->first(fn ($reservation) => $reservation->start_time < $slotEnd && $reservation->end_time > $slotStart);

                    if ($blocked) {
                        $status = 'blocked';
                        $meta = $blocked->reason;
                    } elseif ($reservation) {
                        $status = $reservation->status === 'confirmed'
                            ? 'reserved_confirmed'
                            : 'reserved_pending';
                        $meta = $reservation->user_name;
                    }
                } else {
                    $meta = null;
                }

                $slots[] = [
                    'start' => $slotStart->toDateTimeString(),
                    'end' => $slotEnd->toDateTimeString(),
                    'label' => $slotStart->format('H:i'),
                    'status' => $status,
                    'meta' => $meta,
                ];
            }

            $calendar[] = [
                'date' => $date->toDateString(),
                'day_name' => $date->translatedFormat('l'),
                'slots' => $slots,
            ];
        }

        return $calendar;
    }

    protected function resolveWeekStart(?string $week): Carbon
    {
        if ($week) {
            try {
                return Carbon::parse($week)->startOfWeek(Carbon::MONDAY)->startOfDay();
            } catch (\Throwable) {
                // Fall through to current week.
            }
        }

        return now()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    protected function slotMinutes(): int
    {
        return max(1, (int) env('RESERVATION_SLOT_MINUTES', 60));
    }
}
