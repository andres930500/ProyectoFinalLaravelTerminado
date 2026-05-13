@component('mail::message')
# Tu reserva fue cancelada

Hola {{ $reservation->user_name }}, te informamos que la siguiente reserva fue cancelada.

@component('mail::panel')
**Número de reserva:** {{ $reservation->slug }}

**Cancha:** {{ $reservation->space->name }}  
**Fecha:** {{ $reservation->start_time->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}  
**Hora inicio:** {{ $reservation->start_time->format('H:i') }}  
**Hora fin:** {{ $reservation->end_time->format('H:i') }}
@endcomponent

Lamentamos cualquier molestia que esto pueda causarte. Estaremos encantados de ayudarte a reservar otro horario.

@component('mail::button', ['url' => config('app.url'), 'color' => 'success'])
Reservar de nuevo
@endcomponent

ReservaCancha — Sistema de Reservas de Canchas de Fútbol
@endcomponent
