<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function summary(): JsonResponse
    {
        $pendingReservations = Reservation::query()
            ->where('status', 'pending')
            ->count();

        $todayReservations = Reservation::query()
            ->whereDate('start_time', now()->toDateString())
            ->count();

        $recentActivity = Reservation::query()
            ->with('space:id,name')
            ->where('created_at', '>=', now()->subDay())
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Reservation $reservation) => [
                'user_name' => $reservation->user_name,
                'space_name' => $reservation->space?->name,
                'status' => $reservation->status,
                'created_at' => $reservation->created_at?->toDateTimeString(),
            ])
            ->values();

        return response()->json([
            'pendingReservations' => $pendingReservations,
            'todayReservations' => $todayReservations,
            'recentActivity' => $recentActivity,
        ]);
    }
}
