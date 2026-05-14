<?php

namespace Database\Seeders;

use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SpaceSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $spaces = [
            [
                'name' => 'Cancha El Campin',
                'slug' => 'cancha-el-campin',
                'type' => 'cancha_sintetica',
                'capacity' => 14,
                'description' => 'Cancha de futbol 7 con grama sintetica de ultima generacion, iluminacion LED para partidos nocturnos y vestuarios completos.',
                'rules' => 'No se permite el uso de guayos de taco largo. Respetar el horario reservado. El equipo local debe portar pecheras. Se prohibe el consumo de bebidas alcoholicas.',
                'address' => 'Avenida Kevin Angel # 58-120, Manizales',
                'price_per_hour' => 80000,
                'image' => null,
                'is_active' => true,
                'availabilities' => [
                    [1, '06:00:00', '22:00:00'],
                    [2, '06:00:00', '22:00:00'],
                    [3, '06:00:00', '22:00:00'],
                    [4, '06:00:00', '22:00:00'],
                    [5, '06:00:00', '22:00:00'],
                    [6, '07:00:00', '21:00:00'],
                    [0, '07:00:00', '21:00:00'],
                ],
                'finished_slot' => ['day' => Carbon::WEDNESDAY, 'start' => '19:00:00', 'hours' => 1],
                'pending_slot' => ['start' => '18:00:00', 'hours' => 1],
            ],
            [
                'name' => 'Estadio La Macarena Mini',
                'slug' => 'estadio-la-macarena-mini',
                'type' => 'cancha_cesped',
                'capacity' => 22,
                'description' => 'Cancha de futbol 11 con cesped natural, dimensiones reglamentarias FIFA, marcacion y porterias profesionales.',
                'rules' => 'Uso exclusivo de guayos apropiados. Prohibido el ingreso con mascotas. El arrendatario es responsable del estado de la cancha. Limite de espectadores: 50 personas.',
                'address' => 'Via al Magdalena Km 3, Manizales',
                'price_per_hour' => 150000,
                'image' => null,
                'is_active' => true,
                'availabilities' => [
                    [2, '08:00:00', '20:00:00'],
                    [3, '08:00:00', '20:00:00'],
                    [4, '08:00:00', '20:00:00'],
                    [5, '08:00:00', '20:00:00'],
                    [6, '08:00:00', '20:00:00'],
                    [0, '08:00:00', '20:00:00'],
                ],
                'finished_slot' => ['day' => Carbon::THURSDAY, 'start' => '16:00:00', 'hours' => 2],
                'pending_slot' => ['start' => '10:00:00', 'hours' => 2],
            ],
            [
                'name' => 'Cancha Futsal Centro',
                'slug' => 'cancha-futsal-centro',
                'type' => 'cancha_futbol_sala',
                'capacity' => 10,
                'description' => 'Cancha de futbol sala techada con piso de parque deportivo, ideal para ligas y torneos. Marcacion oficial y arcos reglamentarios.',
                'rules' => 'Solo tenis o zapatillas deportivas en el piso. Prohibido el uso de guayos. Maximo 10 jugadores en cancha simultaneamente. Traer su propio balon o alquilar en recepcion.',
                'address' => 'Carrera 23 # 64-35, Manizales',
                'price_per_hour' => 60000,
                'image' => null,
                'is_active' => true,
                'availabilities' => [
                    [0, '07:00:00', '23:00:00'],
                    [1, '07:00:00', '23:00:00'],
                    [2, '07:00:00', '23:00:00'],
                    [3, '07:00:00', '23:00:00'],
                    [4, '07:00:00', '23:00:00'],
                    [5, '07:00:00', '23:00:00'],
                    [6, '07:00:00', '23:00:00'],
                ],
                'finished_slot' => ['day' => Carbon::FRIDAY, 'start' => '20:00:00', 'hours' => 1],
                'pending_slot' => ['start' => '19:00:00', 'hours' => 1],
            ],
        ];

        $nextMonday = now()->next(Carbon::MONDAY)->setTime(8, 0, 0);
        $tomorrow = now()->addDay()->startOfDay();

        foreach ($spaces as $index => $spaceData) {
            $space = Space::query()->updateOrCreate(
                ['slug' => $spaceData['slug']],
                collect($spaceData)->except(['availabilities', 'finished_slot', 'pending_slot'])->all()
            );

            $space->availabilities()->delete();
            $space->blockedSlots()->delete();

            foreach ($spaceData['availabilities'] as [$dayOfWeek, $startTime, $endTime]) {
                $space->availabilities()->create([
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);
            }

            $space->blockedSlots()->create([
                'start_time' => $nextMonday->copy(),
                'end_time' => $nextMonday->copy()->addHours(4),
                'reason' => 'Mantenimiento preventivo de la cancha',
            ]);

            $finishedDate = now()->subWeek()->next($spaceData['finished_slot']['day'])->setTimeFromTimeString($spaceData['finished_slot']['start']);
            $pendingDate = $this->nextOpenDateForSpace($tomorrow->copy(), $space);
            $pendingDate->setTimeFromTimeString($spaceData['pending_slot']['start']);

            $space->reservations()->updateOrCreate(
                ['slug' => $space->slug.'-seed-finished'],
                [
                    'start_time' => $finishedDate->copy(),
                    'end_time' => $finishedDate->copy()->addHours($spaceData['finished_slot']['hours']),
                    'status' => 'finished',
                    'user_name' => 'Cliente Historico '.($index + 1),
                    'user_email' => 'historico'.($index + 1).'@reservasfutbol.test',
                    'user_phone' => '300000000'.($index + 1),
                    'notes' => 'Reserva de referencia finalizada para pruebas.',
                ]
            );

            $space->reservations()->updateOrCreate(
                ['slug' => $space->slug.'-seed-pending'],
                [
                    'start_time' => $pendingDate->copy(),
                    'end_time' => $pendingDate->copy()->addHours($spaceData['pending_slot']['hours']),
                    'status' => 'pending',
                    'user_name' => 'Cliente Pendiente '.($index + 1),
                    'user_email' => 'pendiente'.($index + 1).'@reservasfutbol.test',
                    'user_phone' => '311000000'.($index + 1),
                    'notes' => 'Reserva pendiente para validar aprobaciones.',
                ]
            );
        }
    }

    protected function nextOpenDateForSpace(Carbon $date, Space $space): Carbon
    {
        $availableDays = $space->availabilities()
            ->pluck('day_of_week')
            ->unique()
            ->all();

        $candidate = $date->copy();

        while (! in_array($candidate->dayOfWeek, $availableDays, true)) {
            $candidate->addDay();
        }

        return $candidate;
    }
}
