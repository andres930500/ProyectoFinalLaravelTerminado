<?php

namespace App\Http\Controllers;

use App\Mail\ReservationCancelledMail;
use App\Mail\ReservationConfirmedMail;
use App\Mail\ReservationRejectedMail;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class AdminReservationController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'space_id', 'date']);

        $reservations = Reservation::query()
            ->with('space')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->byStatus($status))
            ->when($filters['space_id'] ?? null, fn ($query, $spaceId) => $query->bySpace($spaceId))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->byDate($date))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'confirmed' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'pending' => Reservation::query()->where('status', 'pending')->count(),
            'today' => Reservation::query()->whereDate('created_at', now()->toDateString())->count(),
            'confirmed' => Reservation::query()->where('status', 'confirmed')->count(),
        ];

        return Inertia::render('Admin/Reservations/Index', [
            'reservations' => $reservations,
            'filters' => $filters,
            'summary' => $summary,
            'spaces' => Space::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'statuses' => ['pending', 'confirmed', 'rejected', 'cancelled', 'finished'],
        ]);
    }

    public function show(Reservation $reservation): Response
    {
        $reservation->load('space');

        return Inertia::render('Admin/Reservations/Show', [
            'reservation' => $reservation,
        ]);
    }

    public function accept(Reservation $reservation): RedirectResponse
    {
        if (! $reservation->canBeConfirmed()) {
            return back()->withErrors([
                'status' => 'La reserva no puede ser confirmada.',
            ]);
        }

        $reservation->update(['status' => 'confirmed']);
        Mail::to($reservation->user_email)->send(new ReservationConfirmedMail($reservation->fresh('space')));

        return back()->with('success', 'Reserva confirmada correctamente.');
    }

    public function reject(Request $request, Reservation $reservation): RedirectResponse
    {
        if (! $reservation->canBeRejected()) {
            return back()->withErrors([
                'status' => 'La reserva no puede ser rechazada.',
            ]);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $notes = trim(implode("\n\n", array_filter([
            $reservation->notes,
            'Motivo de rechazo: '.$validated['rejection_reason'],
        ])));

        $reservation->update([
            'status' => 'rejected',
            'notes' => $notes,
        ]);

        Mail::to($reservation->user_email)->send(new ReservationRejectedMail($reservation->fresh('space')));

        return back()->with('success', 'Reserva rechazada correctamente.');
    }

    public function cancel(Reservation $reservation): RedirectResponse
    {
        if (! $reservation->canBeCancelled()) {
            return back()->withErrors([
                'status' => 'La reserva no puede ser cancelada.',
            ]);
        }

        $reservation->update(['status' => 'cancelled']);
        Mail::to($reservation->user_email)->send(new ReservationCancelledMail($reservation->fresh('space')));

        return back()->with('success', 'Reserva cancelada correctamente.');
    }

    public function finish(Reservation $reservation): RedirectResponse
    {
        if (! $reservation->canBeFinished()) {
            return back()->withErrors([
                'status' => 'La reserva no puede marcarse como finalizada.',
            ]);
        }

        $reservation->update(['status' => 'finished']);

        return back()->with('success', 'Reserva finalizada correctamente.');
    }
}
