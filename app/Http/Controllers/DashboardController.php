<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = now();
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $pendingTodayCount = Reservation::query()
            ->byStatus('pending')
            ->whereDate('start_time', $today->toDateString())
            ->count();

        $confirmedWeekCount = Reservation::query()
            ->byStatus('confirmed')
            ->whereBetween('start_time', [$weekStart, $weekEnd])
            ->count();

        $upcomingReservations = Reservation::query()
            ->with('space')
            ->byStatus('confirmed')
            ->where('start_time', '>=', $today)
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        $spaces = Space::query()
            ->active()
            ->withCount([
                'reservations as reservations_this_month' => fn ($query) => $query
                    ->whereBetween('start_time', [$monthStart, $monthEnd]),
            ])
            ->orderBy('name')
            ->get();

        $reservationsByDay = Reservation::query()
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $reservationsByStatus = Reservation::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        $spaceOccupancy = Space::query()
            ->active()
            ->withCount([
                'reservations as confirmadas' => fn ($query) => $query
                    ->where('status', 'confirmed')
                    ->whereMonth('start_time', now()->month)
                    ->whereYear('start_time', now()->year),
            ])
            ->orderBy('name')
            ->get();

        $weeklyIncome = Reservation::query()
            ->with('space:id,price_per_hour')
            ->where('status', 'confirmed')
            ->where('start_time', '>=', now()->subDays(6)->startOfDay())
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn (Reservation $reservation) => $reservation->start_time->toDateString())
            ->map(function ($items, $date) {
                $total = $items->sum(function (Reservation $reservation) {
                    $minutes = $reservation->start_time->diffInMinutes($reservation->end_time);
                    $hourlyRate = (float) ($reservation->space->price_per_hour ?? 0);

                    return round(($minutes / 60) * $hourlyRate, 2);
                });

                return [
                    'fecha' => $date,
                    'total' => round($total, 2),
                ];
            })
            ->values();

        $currentDayOfWeek = $today->dayOfWeek;

        $estadoActual = Space::query()
            ->active()
            ->with([
                'availabilities' => fn ($query) => $query
                    ->where('day_of_week', $currentDayOfWeek)
                    ->orderBy('start_time'),
                'reservations' => fn ($query) => $query
                    ->whereDate('start_time', $today->toDateString())
                    ->orderBy('start_time'),
                'blockedSlots' => fn ($query) => $query
                    ->whereDate('start_time', '<=', $today->toDateString())
                    ->whereDate('end_time', '>=', $today->toDateString())
                    ->orderBy('start_time'),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Space $space) => $this->resolveCurrentSpaceStatus($space, $today))
            ->values();

        return Inertia::render('Dashboard', [
            'pendingToday' => $pendingTodayCount,
            'confirmedThisWeek' => $confirmedWeekCount,
            'upcomingReservations' => $upcomingReservations,
            'spaces' => $spaces,
            'reservationsByDay' => $reservationsByDay,
            'reservationsByStatus' => $reservationsByStatus,
            'spaceOccupancy' => $spaceOccupancy,
            'weeklyIncome' => $weeklyIncome,
            'estadoActual' => $estadoActual,
        ]);
    }

    protected function resolveCurrentSpaceStatus(Space $space, Carbon $now): array
    {
        $activeReservation = $space->reservations
            ->first(fn (Reservation $reservation) => in_array($reservation->status, ['pending', 'confirmed'], true)
                && $reservation->start_time->lessThanOrEqualTo($now)
                && $reservation->end_time->greaterThan($now));

        $activeBlock = $space->blockedSlots
            ->first(fn ($blockedSlot) => $blockedSlot->start_time->lessThanOrEqualTo($now)
                && $blockedSlot->end_time->greaterThan($now));

        $todaySchedule = $this->formatTodaySchedule($space->availabilities);
        $withinAvailability = $space->availabilities->contains(function ($availability) use ($now) {
            $time = $now->format('H:i:s');

            return $availability->start_time <= $time && $availability->end_time > $time;
        });

        $nextReservation = $space->reservations
            ->first(fn (Reservation $reservation) => $reservation->start_time->greaterThan($now));

        $state = 'available';

        if ($activeBlock) {
            $state = 'blocked';
        } elseif ($activeReservation?->status === 'pending') {
            $state = 'pending';
        } elseif ($activeReservation?->status === 'confirmed') {
            $state = 'confirmed';
        } elseif (! $withinAvailability) {
            $state = 'closed';
        }

        return [
            'id' => $space->id,
            'name' => $space->name,
            'type' => $space->type,
            'estado' => $state,
            'reserva_actual' => $activeReservation ? [
                'cliente' => $activeReservation->user_name,
                'hasta' => $activeReservation->end_time->format('H:i'),
            ] : null,
            'bloqueo_actual' => $activeBlock ? [
                'motivo' => $activeBlock->reason,
                'hasta' => $activeBlock->end_time->format('H:i'),
            ] : null,
            'proxima_reserva' => $nextReservation ? [
                'cliente' => $nextReservation->user_name,
                'a_las' => $nextReservation->start_time->format('H:i'),
            ] : null,
            'horario_hoy' => $todaySchedule,
        ];
    }

    protected function formatTodaySchedule(Collection $availabilities): ?string
    {
        if ($availabilities->isEmpty()) {
            return null;
        }

        return $availabilities
            ->map(fn ($availability) => substr($availability->start_time, 0, 5).' - '.substr($availability->end_time, 0, 5))
            ->implode(' | ');
    }
}
