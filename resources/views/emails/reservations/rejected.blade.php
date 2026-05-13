@component('mail::message')
# No fue posible confirmar tu reserva

Hola {{ $reservation->user_name }}, lamentamos el inconveniente.

@component('mail::panel')
**Número de reserva:** {{ $reservation->slug }}  
**Cancha:** {{ $reservation->space->name }}  
**Fecha solicitada:** {{ $reservation->start_time->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}  
**Horario:** {{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}
@endcomponent

**Motivo del rechazo:**  
{{ $reservation->notes ?: 'El horario ya no se encuentra disponible.' }}

Te invitamos a consultar otros horarios disponibles e intentar nuevamente.

@component('mail::button', ['url' => config('app.url'), 'color' => 'success'])
Ver otras canchas
@endcomponent

ReservaCancha — Sistema de Reservas de Canchas de Fútbol
@endcomponent
