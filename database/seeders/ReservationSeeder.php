<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $spaces = Space::query()->with('availabilities')->get();

        if ($spaces->isEmpty()) {
            return;
        }

        $statuses = [
            'pending',
            'confirmed',
            'rejected',
            'cancelled',
            'finished',
        ];

        for ($i = 0; $i < 15; $i++) {
            $space = $spaces[$i % $spaces->count()];
            $status = $statuses[$i % count($statuses)];
            $baseDate = $i < 8
                ? now()->subWeek()->addDays($i)
                : now()->addDays($i - 7);

            [$start, $end] = $this->buildSlotForSpace($space, $baseDate->copy(), $i);

            Reservation::factory()
                ->state([
                    'space_id' => $space->id,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => $status,
                    'user_name' => fake()->name(),
                    'user_email' => fake()->unique()->safeEmail(),
                    'user_phone' => fake()->optional()->numerify('3#########'),
                    'notes' => fake()->optional()->sentence(),
                ])
                ->create();
        }
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    protected function buildSlotForSpace(Space $space, Carbon $date, int $index): array
    {
        $availability = $space->availabilities
            ->firstWhere('day_of_week', $date->dayOfWeek);

        if (! $availability) {
            $date = $this->moveToNextAvailableDate($space, $date);
            $availability = $space->availabilities->firstWhere('day_of_week', $date->dayOfWeek);
        }

        $open = Carbon::parse($date->toDateString().' '.$availability->start_time);
        $close = Carbon::parse($date->toDateString().' '.$availability->end_time);
        $durationHours = $index % 3 === 0 ? 2 : 1;
        $candidateStart = $open->copy()->addHours(($index % 5) + 1);
        $candidateEnd = $candidateStart->copy()->addHours($durationHours);

        if ($candidateEnd->greaterThan($close)) {
            $candidateEnd = $close->copy();
            $candidateStart = $candidateEnd->copy()->subHours($durationHours);
        }

        return [$candidateStart, $candidateEnd];
    }

    protected function moveToNextAvailableDate(Space $space, Carbon $date): Carbon
    {
        $candidate = $date->copy();
        $availableDays = $space->availabilities->pluck('day_of_week')->unique()->all();

        while (! in_array($candidate->dayOfWeek, $availableDays, true)) {
            $candidate->addDay();
        }

        return $candidate;
    }
}
