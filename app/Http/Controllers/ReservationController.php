<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use App\Mail\ReservationCreatedMail;
use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $spaceSlug = $request->query('space');
        $startInput = $request->query('start');

        if (! $spaceSlug || ! $startInput) {
            return redirect()->route('home')->withErrors([
                'reservation' => 'Debes seleccionar una cancha y un horario valido.',
            ]);
        }

        $space = Space::query()
            ->active()
            ->where('slug', $spaceSlug)
            ->first();

        if (! $space) {
            return redirect()->route('home')->withErrors([
                'space' => 'La cancha seleccionada no existe o no esta disponible.',
            ]);
        }

        try {
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $startInput);
        } catch (\Throwable) {
            return redirect()->back()->withErrors([
                'start' => 'La fecha de inicio no tiene un formato valido.',
            ]);
        }

        $slotMinutes = $this->slotMinutes();
        $endTime = $startTime->copy()->addMinutes($slotMinutes);

        if (! $space->isStartAlignedToSlot($startTime)) {
            return redirect()->back()->withErrors([
                'start' => 'Debes seleccionar un bloque horario completo y alineado.',
            ]);
        }

        if (! $space->isAvailableForSlot($startTime, $endTime)) {
            return redirect()->back()->withErrors([
                'availability' => 'El horario seleccionado ya no esta disponible.',
            ]);
        }

        $totalPrice = round(($slotMinutes / 60) * (float) ($space->price_per_hour ?? 0), 2);

        return Inertia::render('Reservations/Create', [
            'space' => $space,
            'startTime' => $startTime->toDateTimeString(),
            'endTime' => $endTime->toDateTimeString(),
            'totalPrice' => $totalPrice,
        ]);
    }

    public function store(ReservationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $space = Space::query()->findOrFail($validated['space_id']);
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);
        $slotMinutes = $this->slotMinutes();
        $durationInMinutes = (int) round($startTime->diffInMinutes($endTime));

        if (! $space->isStartAlignedToSlot($startTime)) {
            return back()->withInput()->withErrors([
                'slot_alignment' => 'La reserva debe iniciar en un bloque exacto del sistema.',
            ]);
        }

        if ($durationInMinutes !== $slotMinutes) {
            return back()->withInput()->withErrors([
                'duration' => "La reserva debe durar exactamente {$slotMinutes} minutos.",
            ]);
        }

        if (! $space->is_active) {
            return back()->withInput()->withErrors([
                'space_id' => 'La cancha seleccionada no esta activa.',
            ]);
        }

        if (! $space->isAvailableForSlot($startTime, $endTime)) {
            return back()->withInput()->withErrors([
                'availability' => 'El horario seleccionado no esta disponible.',
            ]);
        }

        $hasReservationConflict = $space->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($hasReservationConflict) {
            return back()->withInput()->withErrors([
                'collision' => 'Ya existe una reserva activa para ese horario.',
            ]);
        }

        $hasBlockedSlot = $space->blockedSlots()
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($hasBlockedSlot) {
            return back()->withInput()->withErrors([
                'blocked_slot' => 'La cancha tiene un bloqueo en el horario solicitado.',
            ]);
        }

        $reservation = Reservation::query()->create([
            'space_id' => $space->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
            'user_name' => $validated['user_name'],
            'user_email' => $validated['user_email'],
            'user_phone' => $validated['user_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        Mail::to($reservation->user_email)->send(new ReservationCreatedMail($reservation));

        return redirect()->route('public.reservations.confirmation', $reservation->slug);
    }

    public function confirmation(Reservation $reservation): Response
    {
        $reservation->load('space');

        return Inertia::render('Reservations/Confirmation', [
            'reservation' => $reservation,
        ]);
    }

    protected function slotMinutes(): int
    {
        return max(1, (int) env('RESERVATION_SLOT_MINUTES', 60));
    }
}
