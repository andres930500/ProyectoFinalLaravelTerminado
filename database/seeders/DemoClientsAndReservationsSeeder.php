<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoClientsAndReservationsSeeder extends Seeder
{
    protected string $seedStartDate = '2026-05-20';

    protected int $seedDays = 31;

    protected array $demoClients = [
        ['name' => 'Mateo Giraldo', 'email' => 'mateo.giraldo@demo.reservacancha.test', 'phone' => '3001000001'],
        ['name' => 'Valentina Ospina', 'email' => 'valentina.ospina@demo.reservacancha.test', 'phone' => '3001000002'],
        ['name' => 'Santiago Ramirez', 'email' => 'santiago.ramirez@demo.reservacancha.test', 'phone' => '3001000003'],
        ['name' => 'Isabella Cardenas', 'email' => 'isabella.cardenas@demo.reservacancha.test', 'phone' => '3001000004'],
        ['name' => 'Sebastian Mejia', 'email' => 'sebastian.mejia@demo.reservacancha.test', 'phone' => '3001000005'],
        ['name' => 'Mariana Lopez', 'email' => 'mariana.lopez@demo.reservacancha.test', 'phone' => '3001000006'],
        ['name' => 'Juan Pablo Arango', 'email' => 'juanpablo.arango@demo.reservacancha.test', 'phone' => '3001000007'],
        ['name' => 'Sara Duque', 'email' => 'sara.duque@demo.reservacancha.test', 'phone' => '3001000008'],
        ['name' => 'Andres Bedoya', 'email' => 'andres.bedoya@demo.reservacancha.test', 'phone' => '3001000009'],
        ['name' => 'Camila Restrepo', 'email' => 'camila.restrepo@demo.reservacancha.test', 'phone' => '3001000010'],
        ['name' => 'Nicolas Henao', 'email' => 'nicolas.henao@demo.reservacancha.test', 'phone' => '3001000011'],
        ['name' => 'Laura Jaramillo', 'email' => 'laura.jaramillo@demo.reservacancha.test', 'phone' => '3001000012'],
        ['name' => 'Daniela Montoya', 'email' => 'daniela.montoya@demo.reservacancha.test', 'phone' => '3001000013'],
        ['name' => 'Felipe Cardona', 'email' => 'felipe.cardona@demo.reservacancha.test', 'phone' => '3001000014'],
        ['name' => 'Juliana Rios', 'email' => 'juliana.rios@demo.reservacancha.test', 'phone' => '3001000015'],
        ['name' => 'Esteban Quintero', 'email' => 'esteban.quintero@demo.reservacancha.test', 'phone' => '3001000016'],
        ['name' => 'Paula Correa', 'email' => 'paula.correa@demo.reservacancha.test', 'phone' => '3001000017'],
        ['name' => 'Alejandro Marin', 'email' => 'alejandro.marin@demo.reservacancha.test', 'phone' => '3001000018'],
        ['name' => 'Luisa Fernanda Toro', 'email' => 'luisa.toro@demo.reservacancha.test', 'phone' => '3001000019'],
        ['name' => 'Carlos Andres Londoño', 'email' => 'carlos.londono@demo.reservacancha.test', 'phone' => '3001000020'],
        ['name' => 'Manuela Villegas', 'email' => 'manuela.villegas@demo.reservacancha.test', 'phone' => '3001000021'],
        ['name' => 'Jhonatan Salazar', 'email' => 'jhonatan.salazar@demo.reservacancha.test', 'phone' => '3001000022'],
        ['name' => 'Tatiana Castaño', 'email' => 'tatiana.castano@demo.reservacancha.test', 'phone' => '3001000023'],
        ['name' => 'Kevin Stiven Osorio', 'email' => 'kevin.osorio@demo.reservacancha.test', 'phone' => '3001000024'],
    ];

    protected array $statusCycle = [
        'confirmed',
        'pending',
        'confirmed',
        'confirmed',
        'pending',
        'cancelled',
        'rejected',
    ];

    public function run(): void
    {
        Reservation::query()
            ->where('user_email', 'like', '%@demo.reservacancha.test')
            ->delete();

        $spaces = Space::query()
            ->with('availabilities')
            ->active()
            ->orderBy('id')
            ->get();

        if ($spaces->isEmpty()) {
            $this->command?->warn('No hay canchas activas para generar reservas demo.');
            return;
        }

        $createdReservations = 0;
        $clientIndex = 0;
        $slotCursor = 0;
        $seedStart = Carbon::parse($this->seedStartDate)->startOfDay();

        for ($dayOffset = 0; $dayOffset < $this->seedDays; $dayOffset++) {
            $date = $seedStart->copy()->addDays($dayOffset);

            foreach ($spaces as $spaceIndex => $space) {
                $availableSlots = collect($space->getDailyReservationSlots($date))
                    ->filter(fn (array $slot) => $slot['is_available'])
                    ->values();

                if ($availableSlots->isEmpty()) {
                    continue;
                }

                $reservationsForSpace = min($this->reservationsPerSpaceForDate($date), $availableSlots->count());
                $pickedIndexes = [];

                for ($reservationOffset = 0; $reservationOffset < $reservationsForSpace; $reservationOffset++) {
                    $slotIndex = ($spaceIndex + $dayOffset + ($reservationOffset * 3) + $slotCursor) % $availableSlots->count();

                    while (in_array($slotIndex, $pickedIndexes, true)) {
                        $slotIndex = ($slotIndex + 1) % $availableSlots->count();
                    }

                    $pickedIndexes[] = $slotIndex;
                    $slot = $availableSlots[$slotIndex];
                    $client = $this->demoClients[$clientIndex % count($this->demoClients)];
                    $status = $this->statusCycle[($dayOffset + $spaceIndex + $reservationOffset) % count($this->statusCycle)];

                    Reservation::query()->create([
                        'space_id' => $space->id,
                        'start_time' => Carbon::parse($slot['start']),
                        'end_time' => Carbon::parse($slot['end']),
                        'status' => $status,
                        'user_name' => $client['name'],
                        'user_email' => $client['email'],
                        'user_phone' => $client['phone'],
                        'notes' => 'Reserva demo generada automaticamente para poblar clientes y agenda.',
                    ]);

                    $createdReservations++;
                    $clientIndex++;
                }

                $slotCursor++;
            }
        }

        $this->command?->info(sprintf(
            'Reservas demo creadas: %d | Clientes demo usados: %d | Rango: %s a %s',
            $createdReservations,
            count($this->demoClients),
            $seedStart->toDateString(),
            $seedStart->copy()->addDays($this->seedDays - 1)->toDateString()
        ));
    }

    protected function reservationsPerSpaceForDate(Carbon $date): int
    {
        return match ($date->dayOfWeek) {
            Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY => 4,
            Carbon::WEDNESDAY, Carbon::THURSDAY => 3,
            default => 2,
        };
    }
}
