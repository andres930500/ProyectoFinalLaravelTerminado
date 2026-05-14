<?php

namespace Tests\Feature;

use App\Mail\ReservationCreatedMail;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_reservation_is_saved_as_pending_and_sends_confirmation_mail(): void
    {
        Mail::fake();

        $space = $this->createReservableSpace();
        $start = now()->addDay()->setTime(8, 0, 0);
        $end = $start->copy()->addHour();

        $response = $this->post(route('public.reservations.store'), [
            'space_id' => $space->id,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
            'user_name' => 'Cliente Demo',
            'user_email' => 'cliente@example.com',
            'user_phone' => '3001234567',
            'notes' => 'Prueba de reserva',
        ]);

        $response->assertSessionHasNoErrors();
        $reservation = Reservation::query()->first();

        $response->assertRedirect(route('public.reservations.confirmation', $reservation->slug, false));

        $this->assertDatabaseHas('reservations', [
            'space_id' => $space->id,
            'status' => 'pending',
            'user_email' => 'cliente@example.com',
        ]);

        Mail::assertSent(ReservationCreatedMail::class, fn (ReservationCreatedMail $mail) => $mail->reservation->is($reservation));
    }

    public function test_public_reservation_rejects_misaligned_hour_blocks(): void
    {
        Mail::fake();

        $space = $this->createReservableSpace();
        $start = now()->addDay()->setTime(8, 30, 0);
        $end = $start->copy()->addHour();

        $response = $this->from(route('public.reservations.create', [
            'space' => $space->slug,
            'start' => $start->format('Y-m-d H:i:s'),
        ]))->post(route('public.reservations.store'), [
            'space_id' => $space->id,
            'start_time' => $start->format('Y-m-d H:i:s'),
            'end_time' => $end->format('Y-m-d H:i:s'),
            'user_name' => 'Cliente Demo',
            'user_email' => 'cliente@example.com',
        ]);

        $response->assertSessionHasErrors('slot_alignment');
        $this->assertDatabaseCount('reservations', 0);
        Mail::assertNothingSent();
    }

    public function test_admin_list_shows_pending_reservations_first(): void
    {
        $admin = User::factory()->create();
        $space = $this->createReservableSpace();

        $confirmed = Reservation::query()->create([
            'space_id' => $space->id,
            'start_time' => now()->addDays(2)->setTime(10, 0, 0),
            'end_time' => now()->addDays(2)->setTime(11, 0, 0),
            'status' => 'confirmed',
            'user_name' => 'Cliente Confirmado',
            'user_email' => 'confirmado@example.com',
        ]);

        $pending = Reservation::query()->create([
            'space_id' => $space->id,
            'start_time' => now()->addDay()->setTime(8, 0, 0),
            'end_time' => now()->addDay()->setTime(9, 0, 0),
            'status' => 'pending',
            'user_name' => 'Cliente Pendiente',
            'user_email' => 'pendiente@example.com',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reservations.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reservations/Index')
            ->where('reservations.data.0.slug', $pending->slug)
            ->where('reservations.data.1.slug', $confirmed->slug)
            ->where('summary.pending', 1)
            ->where('summary.confirmed', 1)
        );
    }

    protected function createReservableSpace(): Space
    {
        $space = Space::factory()->create([
            'slug' => 'space-demo',
        ]);

        for ($dayOfWeek = Carbon::SUNDAY; $dayOfWeek <= Carbon::SATURDAY; $dayOfWeek++) {
            $space->availabilities()->create([
                'day_of_week' => $dayOfWeek,
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
            ]);
        }

        return $space;
    }
}
