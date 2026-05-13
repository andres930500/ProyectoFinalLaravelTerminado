<?php

namespace App\Mail;

use App\Models\Reservation;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCancelledMail extends Mailable
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
            subject: sprintf(
                '🚫 Reserva cancelada — %s el %s',
                $this->reservation->space->name,
                $this->formatDate($this->reservation->start_time)
            )
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reservations.cancelled',
            with: [
                'reservation' => $this->reservation,
            ],
        );
    }

    protected function formatDate(CarbonInterface $date): string
    {
        return $date->locale('es')->translatedFormat('d/m/Y');
    }
}
