<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Reservation>
     */
    protected $model = Reservation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::instance(fake()->dateTimeBetween('-7 days', '+7 days'))
            ->setTime(fake()->numberBetween(7, 20), fake()->randomElement([0, 30]), 0);
        $durationHours = fake()->randomElement([1, 2]);

        return [
            'space_id' => Space::query()->inRandomOrder()->value('id') ?? Space::factory(),
            'start_time' => $start,
            'end_time' => $start->copy()->addHours($durationHours),
            'status' => fake()->randomElement(['pending', 'confirmed', 'rejected', 'cancelled', 'finished']),
            'user_name' => fake()->name(),
            'user_email' => fake()->unique()->safeEmail(),
            'user_phone' => fake()->optional()->numerify('3#########'),
            'notes' => fake()->optional()->sentence(),
            'slug' => Str::lower(Str::random(10)),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => 'confirmed']);
    }

    public function finished(): static
    {
        return $this->state(fn () => ['status' => 'finished']);
    }
}
