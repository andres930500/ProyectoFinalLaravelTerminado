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

        return Inertia::render('Dashboard', [
            'pendingToday' => $pendingTodayCount,
            'confirmedThisWeek' => $confirmedWeekCount,
            'upcomingReservations' => $upcomingReservations,
            'spaces' => $spaces,
        ]);
    }
}
