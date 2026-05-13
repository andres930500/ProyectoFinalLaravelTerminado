<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Space;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAvailabilityController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Admin/Availabilities/Create', [
            'spaces' => Space::query()->active()->orderBy('name')->get(['id', 'name', 'slug']),
            'days' => $this->days(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'space_id' => ['required', 'exists:spaces,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        Availability::query()->updateOrCreate(
            [
                'space_id' => $validated['space_id'],
                'day_of_week' => $validated['day_of_week'],
            ],
            [
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ]
        );

        return back()->with('success', 'Disponibilidad guardada correctamente.');
    }

    public function edit(Availability $availability): Response
    {
        $availability->load('space');

        return Inertia::render('Admin/Availabilities/Edit', [
            'availability' => $availability,
            'spaces' => Space::query()->active()->orderBy('name')->get(['id', 'name', 'slug']),
            'days' => $this->days(),
        ]);
    }

    public function update(Request $request, Availability $availability): RedirectResponse
    {
        $validated = $request->validate([
            'space_id' => ['required', 'exists:spaces,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $availability->update($validated);

        return back()->with('success', 'Disponibilidad actualizada correctamente.');
    }

    public function destroy(Availability $availability): RedirectResponse
    {
        $availability->delete();

        return back()->with('success', 'Disponibilidad eliminada correctamente.');
    }

    protected function days(): array
    {
        return [
            ['value' => 0, 'label' => 'Domingo'],
            ['value' => 1, 'label' => 'Lunes'],
            ['value' => 2, 'label' => 'Martes'],
            ['value' => 3, 'label' => 'Miercoles'],
            ['value' => 4, 'label' => 'Jueves'],
            ['value' => 5, 'label' => 'Viernes'],
            ['value' => 6, 'label' => 'Sabado'],
        ];
    }
}
