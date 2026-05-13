<?php

namespace Database\Factories;

use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Space>
 */
class SpaceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Space>
     */
    protected $model = Space::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $spaceTypes = [
            'cancha_cesped' => [22, 'Cancha de futbol 11 con cesped natural y medidas reglamentarias.'],
            'cancha_sintetica' => [14, 'Cancha de futbol 7 con grama sintetica e iluminacion nocturna.'],
            'cancha_futbol_sala' => [10, 'Cancha techada de futbol sala con piso deportivo y marcacion oficial.'],
            'cancha_futbol_playa' => [12, 'Cancha de futbol playa con arena tratada y zona de descanso.'],
        ];

        $type = fake()->randomElement(array_keys($spaceTypes));
        [$capacity, $description] = $spaceTypes[$type];
        $name = fake()->unique()->company().' Arena';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'type' => $type,
            'capacity' => $capacity,
            'description' => $description,
            'rules' => fake()->randomElement([
                'Respetar el horario reservado. Prohibido fumar dentro de la cancha. Mantener limpio el espacio.',
                'Uso obligatorio de calzado adecuado. No se permite el ingreso con bebidas alcoholicas.',
                'Cada equipo debe presentarse 10 minutos antes. El dano a las instalaciones sera cobrado.',
            ]),
            'price_per_hour' => fake()->randomElement([60000, 70000, 80000, 90000, 120000, 150000]),
            'image' => null,
            'is_active' => true,
        ];
    }
}
