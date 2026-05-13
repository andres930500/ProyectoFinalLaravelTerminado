<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpaceController extends Controller
{
    public function index(Request $request): Response
    {
        $selectedType = $request->query('type');

        $types = Space::query()
            ->active()
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->values();

        $spaces = Space::query()
            ->active()
            ->when($selectedType, fn ($query) => $query->where('type', $selectedType))
            ->orderBy('name')
            ->get();

        return Inertia::render('Spaces/Index', [
            'spaces' => $spaces,
            'types' => $types,
            'selectedType' => $selectedType,
        ]);
    }

    public function show(Request $request, Space $space): Response
    {
        abort_unless($space->is_active, 404);

        $space->load([
            'availabilities' => fn ($query) => $query->orderBy('day_of_week')->orderBy('start_time'),
        ]);

        $nextAvailableSlots = $space->getNextAvailableSlots(10);
        $defaultSlot = $nextAvailableSlots[0] ?? null;
        $selectedDate = $request->query('date', $defaultSlot ? $defaultSlot['start']->toDateString() : now()->toDateString());
        $selectedTime = $request->query('time', $defaultSlot ? $defaultSlot['start']->format('H:i') : '08:00');

        $nextSlots = collect($nextAvailableSlots)
            ->map(fn (array $slot) => [
                'start' => $slot['start']->toDateTimeString(),
                'end' => $slot['end']->toDateTimeString(),
                'formatted' => sprintf(
                    '%s | %s - %s',
                    $slot['start']->translatedFormat('D j M'),
                    $slot['start']->format('H:i'),
                    $slot['end']->format('H:i')
                ),
            ])
            ->values();

        $availabilityCheck = $this->buildAvailabilityCheck($space, $selectedDate, $selectedTime);

        $availabilities = $space->availabilities
            ->groupBy('day_of_week')
            ->map(fn ($items, $dayOfWeek) => [
                'day_of_week' => (int) $dayOfWeek,
                'day_name' => $items->first()->dayName(),
                'slots' => $items->map(fn ($availability) => [
                    'id' => $availability->id,
                    'start_time' => $availability->start_time,
                    'end_time' => $availability->end_time,
                ])->values(),
            ])
            ->values();

        return Inertia::render('Spaces/Show', [
            'space' => $space,
            'nextSlots' => $nextSlots,
            'availabilities' => $availabilities,
            'availabilityCheck' => $availabilityCheck,
            'selectedDate' => $selectedDate,
            'selectedTime' => $selectedTime,
        ]);
    }

    protected function buildAvailabilityCheck(Space $space, string $date, string $time): array
    {
        try {
            $start = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}");
            $end = $start->copy()->addMinutes(max(1, (int) env('RESERVATION_SLOT_MINUTES', 60)));
            $available = $space->isAvailableForSlot($start, $end);

            return [
                'date' => $date,
                'time' => $time,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
                'available' => $available,
                'message' => $available
                    ? 'El horario seleccionado esta disponible para reserva.'
                    : 'El horario seleccionado no esta disponible.',
            ];
        } catch (\Throwable) {
            return [
                'date' => $date,
                'time' => $time,
                'available' => false,
                'message' => 'La fecha u hora indicada no tiene un formato valido.',
            ];
        }
    }
}
