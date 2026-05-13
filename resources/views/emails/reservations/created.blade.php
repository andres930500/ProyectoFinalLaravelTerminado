@component('mail::message')
# Hola {{ $reservation->user_name }}

Hemos recibido tu solicitud de reserva y ya quedó registrada con estado **PENDIENTE**.

@component('mail::panel')
**Número de reserva:** {{ $reservation->slug }}

**Cancha:** {{ $reservation->space->name }}  
**Dirección:** {{ $reservation->space->address ?? 'Dirección no registrada' }}  
**Fecha:** {{ $reservation->start_time->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}  
**Hora inicio:** {{ $reservation->start_time->format('H:i') }}  
**Hora fin:** {{ $reservation->end_time->format('H:i') }}  
**Duración:** {{ $reservation->getDurationInMinutes() }} minutos  
**Precio total:** ${{ number_format($reservation->getTotalPrice(), 0, ',', '.') }}
@endcomponent

Muy pronto recibirás una confirmación con el estado final de tu reserva.

@component('mail::button', ['url' => config('app.url'), 'color' => 'success'])
Ir a ReservaCancha
@endcomponent

Guarda este número para futuras consultas: **{{ $reservation->slug }}**

ReservaCancha — Sistema de Reservas de Canchas de Fútbol
@endcomponent
