<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        [$from, $to] = $this->resolveRange($request);

        $reservations = Reservation::query()
            ->with('space:id,name,price_per_hour')
            ->whereBetween('start_time', [$from, $to])
            ->get();

        $totalReservaciones = $reservations->count();
        $reservasConfirmadas = $reservations->where('status', 'confirmed')->count();
        $reservasPendientes = $reservations->where('status', 'pending')->count();
        $reservasRechazadas = $reservations->where('status', 'rejected')->count();

        $ingresosTotales = round($reservations
            ->where('status', 'confirmed')
            ->sum(fn (Reservation $reservation) => $this->reservationAmount($reservation)), 2);

        $tasaConversion = $totalReservaciones > 0
            ? round(($reservasConfirmadas / $totalReservaciones) * 100, 2)
            : 0;

        $rankingCanchas = Space::query()
            ->active()
            ->with(['reservations' => fn ($query) => $query
                ->where('status', 'confirmed')
                ->whereBetween('start_time', [$from, $to]),
            ])
            ->get()
            ->map(function (Space $space) {
                $confirmadas = $space->reservations->count();
                $ingresos = round($space->reservations->sum(
                    fn (Reservation $reservation) => $this->reservationAmount($reservation)
                ), 2);

                return [
                    'id' => $space->id,
                    'name' => $space->name,
                    'total' => $confirmadas,
                    'ingresos' => $ingresos,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $reservasPorHora = collect(range(0, 23))
            ->map(function (int $hour) use ($reservations) {
                return [
                    'hour' => $hour,
                    'total' => $reservations->filter(
                        fn (Reservation $reservation) => $reservation->start_time->format('G') === (string) $hour
                    )->count(),
                ];
            })
            ->values();

        $clientesFrecuentes = $reservations
            ->groupBy('user_email')
            ->map(function ($items, $email) {
                $sorted = $items->sortByDesc('start_time')->values();
                $latest = $sorted->first();

                return [
                    'user_email' => $email,
                    'user_name' => $latest?->user_name,
                    'total_reservas' => $items->count(),
                    'ultima_reserva' => optional($items->max('start_time'))->toDateTimeString(),
                ];
            })
            ->sortByDesc('total_reservas')
            ->take(10)
            ->values();

        return Inertia::render('Admin/Reports', [
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'totalReservaciones' => $totalReservaciones,
            'reservasConfirmadas' => $reservasConfirmadas,
            'reservasPendientes' => $reservasPendientes,
            'reservasRechazadas' => $reservasRechazadas,
            'ingresosTotales' => $ingresosTotales,
            'tasaConversion' => $tasaConversion,
            'rankingCanchas' => $rankingCanchas,
            'reservasPorHora' => $reservasPorHora,
            'clientesFrecuentes' => $clientesFrecuentes,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveRange($request);

        $reservations = Reservation::query()
            ->with('space:id,name,price_per_hour')
            ->whereBetween('start_time', [$from, $to])
            ->orderBy('start_time')
            ->get();

        $filename = sprintf(
            'reportes-reservas-%s-a-%s.csv',
            $from->toDateString(),
            $to->toDateString()
        );

        return response()->streamDownload(function () use ($reservations) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Cancha',
                'Cliente',
                'Email',
                'Telefono',
                'Fecha',
                'Hora inicio',
                'Hora fin',
                'Duracion',
                'Precio',
                'Estado',
            ]);

            foreach ($reservations as $reservation) {
                fputcsv($handle, [
                    $reservation->id,
                    $reservation->space?->name,
                    $reservation->user_name,
                    $reservation->user_email,
                    $reservation->user_phone,
                    $reservation->start_time->toDateString(),
                    $reservation->start_time->format('H:i'),
                    $reservation->end_time->format('H:i'),
                    $reservation->start_time->diffInMinutes($reservation->end_time).' min',
                    number_format($this->reservationAmount($reservation), 2, '.', ''),
                    $reservation->status,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function resolveRange(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->string('from'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->string('to'))->endOfDay()
            : now()->endOfMonth()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    protected function reservationAmount(Reservation $reservation): float
    {
        $minutes = $reservation->start_time->diffInMinutes($reservation->end_time);
        $hourlyRate = (float) ($reservation->space?->price_per_hour ?? 0);

        return round(($minutes / 60) * $hourlyRate, 2);
    }
}
