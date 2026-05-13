<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
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

        return Inertia::render('Dashboard', [
            'pendingToday' => $pendingTodayCount,
            'confirmedThisWeek' => $confirmedWeekCount,
            'upcomingReservations' => $upcomingReservations,
            'spaces' => $spaces,
            'reservationsByDay' => $reservationsByDay,
            'reservationsByStatus' => $reservationsByStatus,
            'spaceOccupancy' => $spaceOccupancy,
            'weeklyIncome' => $weeklyIncome,
        ]);
    }
}
