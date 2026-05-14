<?php

namespace App\Http\Controllers;

use App\Models\Space;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminSpaceController extends Controller
{
    public function index(): Response
    {
        $spaces = Space::query()
            ->with(['availabilities' => fn ($query) => $query->orderBy('day_of_week')->orderBy('start_time')])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Spaces/Index', [
            'spaces' => $spaces,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Spaces/Create', [
            'types' => $this->spaceTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price_per_hour' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'rules' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
            'availabilities' => ['nullable', 'array'],
            'availabilities.*.enabled' => ['nullable', 'boolean'],
            'availabilities.*.day_of_week' => ['required_with:availabilities', 'integer', 'between:0,6'],
            'availabilities.*.start_time' => ['nullable', 'date_format:H:i'],
            'availabilities.*.end_time' => ['nullable', 'date_format:H:i'],
        ]);

        $spaceData = collect($validated)->except(['availabilities', 'images'])->all();

        if ($request->hasFile('images')) {
            $storedImages = $this->storeUploadedImages($request->file('images'));
            $spaceData['images'] = $storedImages;
            $spaceData['image'] = $storedImages[0] ?? null;
        }

        $space = Space::query()->create([
            ...$spaceData,
            'slug' => $this->uniqueSlug($validated['name']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $this->syncAvailabilities($space, $validated['availabilities'] ?? []);

        return redirect()->route('admin.spaces.index')->with('success', 'Cancha creada correctamente.');
    }

    public function show(Space $space): Response
    {
        $space->load(['availabilities' => fn ($query) => $query->orderBy('day_of_week')->orderBy('start_time')]);

        return Inertia::render('Admin/Spaces/Show', [
            'space' => $space,
        ]);
    }

    public function edit(Space $space): Response
    {
        $space->load(['availabilities' => fn ($query) => $query->orderBy('day_of_week')->orderBy('start_time')]);

        return Inertia::render('Admin/Spaces/Edit', [
            'space' => $space,
            'types' => $this->spaceTypes(),
        ]);
    }

    public function update(Request $request, Space $space): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price_per_hour' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'rules' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
            'availabilities' => ['nullable', 'array'],
            'availabilities.*.enabled' => ['nullable', 'boolean'],
            'availabilities.*.day_of_week' => ['required_with:availabilities', 'integer', 'between:0,6'],
            'availabilities.*.start_time' => ['nullable', 'date_format:H:i'],
            'availabilities.*.end_time' => ['nullable', 'date_format:H:i'],
        ]);

        $spaceData = collect($validated)->except(['availabilities', 'images'])->all();

        if ($space->name !== $validated['name']) {
            $spaceData['slug'] = $this->uniqueSlug($validated['name'], $space->id);
        }

        if ($request->hasFile('images')) {
            $this->deleteStoredImages($space);

            $storedImages = $this->storeUploadedImages($request->file('images'));
            $spaceData['images'] = $storedImages;
            $spaceData['image'] = $storedImages[0] ?? null;
        }

        $space->update($spaceData);
        $this->syncAvailabilities($space, $validated['availabilities'] ?? []);

        return redirect()->route('admin.spaces.edit', $space)->with('success', 'Cancha actualizada correctamente.');
    }

    public function destroy(Space $space): RedirectResponse
    {
        if ($space->reservations()->exists()) {
            $space->update(['is_active' => false]);
        } else {
            $this->deleteStoredImages($space);
            $space->delete();
        }

        return redirect()->route('admin.spaces.index')->with('success', 'Cancha eliminada correctamente.');
    }

    protected function spaceTypes(): array
    {
        return [
            'cancha_cesped',
            'cancha_sintetica',
            'cancha_futbol_sala',
            'cancha_futbol_playa',
        ];
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::random(8));
        $slug = $baseSlug;
        $suffix = 1;

        while (Space::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function syncAvailabilities(Space $space, array $availabilities): void
    {
        foreach ($availabilities as $availability) {
            $enabled = filter_var($availability['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $dayOfWeek = $availability['day_of_week'];

            if (! $enabled) {
                $space->availabilities()->where('day_of_week', $dayOfWeek)->delete();
                continue;
            }

            if (blank($availability['start_time'] ?? null) || blank($availability['end_time'] ?? null)) {
                continue;
            }

            $space->availabilities()->updateOrCreate(
                ['day_of_week' => $dayOfWeek],
                [
                    'start_time' => $availability['start_time'],
                    'end_time' => $availability['end_time'],
                ]
            );
        }
    }

    protected function storeUploadedImages(array $files): array
    {
        return collect($files)
            ->take(3)
            ->map(fn ($file) => $file->store('spaces', 'public'))
            ->values()
            ->all();
    }

    protected function deleteStoredImages(Space $space): void
    {
        $storedImages = json_decode($space->getRawOriginal('images') ?? '[]', true);
        $storedImages = is_array($storedImages) ? $storedImages : [];

        if ($storedImages === [] && filled($space->getRawOriginal('image'))) {
            $storedImages = [$space->getRawOriginal('image')];
        }

        foreach ($storedImages as $path) {
            if (filled($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
