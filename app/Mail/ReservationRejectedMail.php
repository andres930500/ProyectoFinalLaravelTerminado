<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationRejectedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Reservation $reservation)
    {
        $this->reservation->loadMissing('space');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('❌ Reserva no disponible — %s', $this->reservation->space->name)
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.rejected',
            with: [
                'reservation' => $this->reservation,
            ],
        );
    }
}
