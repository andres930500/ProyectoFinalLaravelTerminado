<?php

namespace App\Http\Controllers;

use App\Models\BlockedSlot;
use App\Models\Space;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminBlockedSlotController extends Controller
{
    public function index(): Response
    {
        $blockedSlots = BlockedSlot::query()
            ->with('space')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/BlockedSlots/Index', [
            'blockedSlots' => $blockedSlots,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/BlockedSlots/Create', [
            'spaces' => Space::query()->active()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'space_id' => ['required', 'exists:spaces,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'reason' => ['required', 'string'],
        ]);

        BlockedSlot::query()->create($validated);

        return redirect()->route('admin.blocked-slots.index')->with('success', 'Bloqueo creado correctamente.');
    }

    public function edit(BlockedSlot $blockedSlot): Response
    {
        $blockedSlot->load('space');

        return Inertia::render('Admin/BlockedSlots/Edit', [
            'blockedSlot' => $blockedSlot,
            'spaces' => Space::query()->active()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function update(Request $request, BlockedSlot $blockedSlot): RedirectResponse
    {
        $validated = $request->validate([
            'space_id' => ['required', 'exists:spaces,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'reason' => ['required', 'string'],
        ]);

        $blockedSlot->update($validated);

        return redirect()->route('admin.blocked-slots.index')->with('success', 'Bloqueo actualizado correctamente.');
    }

    public function destroy(BlockedSlot $blockedSlot): RedirectResponse
    {
        $blockedSlot->delete();

        return redirect()->route('admin.blocked-slots.index')->with('success', 'Bloqueo eliminado correctamente.');
    }
}
