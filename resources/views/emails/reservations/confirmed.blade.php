@component('mail::message')
# ¡Tu reserva fue confirmada!

Hola {{ $reservation->user_name }}, ya tienes tu espacio apartado y listo para jugar.

@component('mail::panel')
**Número de reserva:** {{ $reservation->slug }}

**Cancha:** {{ $reservation->space->name }}  
**Fecha:** {{ $reservation->start_time->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}  
**Hora inicio:** {{ $reservation->start_time->format('H:i') }}  
**Hora fin:** {{ $reservation->end_time->format('H:i') }}  
**Precio a pagar:** ${{ number_format($reservation->getTotalPrice(), 0, ',', '.') }}
@endcomponent

## Reglas de uso

{{ $reservation->space->rules ?: 'Consulta en recepción las reglas específicas de uso de la cancha.' }}

## Instrucciones

- Llega 10 minutos antes de la hora programada.
- Presenta tu número de reserva: **{{ $reservation->slug }}**.
- Si aplica cobro, realiza el pago en recepción o por el medio acordado con la administración.

@component('mail::button', ['url' => config('app.url'), 'color' => 'success'])
Ver sitio
@endcomponent

ReservaCancha — Sistema de Reservas de Canchas de Fútbol
@endcomponent
