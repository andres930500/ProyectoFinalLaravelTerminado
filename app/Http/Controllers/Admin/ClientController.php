<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        $reservations = Reservation::query()
            ->with('space:id,name,price_per_hour')
            ->orderByDesc('start_time')
            ->get();

        $clients = $this->buildClientSummaries($reservations);

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function show(string $email): Response
    {
        $decodedEmail = urldecode($email);

        $reservations = Reservation::query()
            ->with('space:id,name,price_per_hour')
            ->where('user_email', $decodedEmail)
            ->orderByDesc('start_time')
            ->get();

        abort_if($reservations->isEmpty(), 404);

        $client = $this->buildClientSummary($reservations);

        return Inertia::render('Admin/Clients/Show', [
            'client' => $client,
            'reservations' => $reservations->map(function (Reservation $reservation) {
                return [
                    'id' => $reservation->id,
                    'slug' => $reservation->slug,
                    'status' => $reservation->status,
                    'start_time' => $reservation->start_time?->toIso8601String(),
                    'end_time' => $reservation->end_time?->toIso8601String(),
                    'duration_minutes' => $reservation->getDurationInMinutes(),
                    'total_price' => $reservation->getTotalPrice(),
                    'space' => [
                        'name' => $reservation->space?->name,
                    ],
                ];
            })->values(),
        ]);
    }

    protected function buildClientSummaries(Collection $reservations): Collection
    {
        return $reservations
            ->groupBy('user_email')
            ->map(fn (Collection $items) => $this->buildClientSummary($items))
            ->sortByDesc('total_reservas')
            ->values();
    }

    protected function buildClientSummary(Collection $reservations): array
    {
        $sorted = $reservations->sortBy('start_time')->values();
        $firstReservation = $sorted->first();
        $lastReservation = $sorted->last();
        $latestWithPhone = $sorted->reverse()->first(fn (Reservation $reservation) => filled($reservation->user_phone));
        $favoriteSpace = $reservations
            ->groupBy(fn (Reservation $reservation) => $reservation->space?->name ?? 'Sin cancha')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first();

        $reservasConfirmadas = $reservations->where('status', 'confirmed')->count();
        $totalGastado = round(
            $reservations
                ->where('status', 'confirmed')
                ->sum(fn (Reservation $reservation) => $reservation->getTotalPrice()),
            2
        );

        return [
            'nombre' => $lastReservation?->user_name ?? 'Cliente sin nombre',
            'email' => $lastReservation?->user_email,
            'telefono' => $latestWithPhone?->user_phone,
            'total_reservas' => $reservations->count(),
            'reservas_confirmadas' => $reservasConfirmadas,
            'total_gastado' => $totalGastado,
            'primera_reserva' => $firstReservation?->start_time?->toIso8601String(),
            'ultima_reserva' => $lastReservation?->start_time?->toIso8601String(),
            'cancha_favorita' => $favoriteSpace,
        ];
    }
}
