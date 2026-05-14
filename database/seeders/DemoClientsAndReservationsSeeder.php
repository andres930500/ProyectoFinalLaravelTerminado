<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoClientsAndReservationsSeeder extends Seeder
{
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

        for ($dayOffset = 0; $dayOffset < 8; $dayOffset++) {
            $date = now()->addDays($dayOffset + 1)->startOfDay();

            foreach ($spaces as $spaceIndex => $space) {
                $availableSlots = collect($space->getDailyReservationSlots($date))
                    ->filter(fn (array $slot) => $slot['is_available'])
                    ->values();

                if ($availableSlots->isEmpty()) {
                    continue;
                }

                $reservationsForSpace = min(2, $availableSlots->count());
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
            'Reservas demo creadas: %d | Clientes demo usados: %d',
            $createdReservations,
            count($this->demoClients)
        ));
    }
}
