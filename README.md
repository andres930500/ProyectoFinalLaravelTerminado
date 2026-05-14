# ReservaCancha - Sistema de Reservas de Canchas de Futbol

## Descripcion

ReservaCancha es una plataforma web para gestionar y reservar canchas de futbol.  
Fue construida con Laravel, Jetstream, Inertia, Vue 3 y TailwindCSS.

Permite:

- consultar disponibilidad en tiempo real
- reservar sin necesidad de crear cuenta
- administrar espacios, bloqueos y solicitudes desde un panel privado
- visualizar reportes operativos y metricas de negocio
- mostrar ubicacion de cada cancha con direccion y mapa embebido

## Tecnologias

- PHP 8.2+
- Laravel 12+ / compatible con el proyecto actual
- Laravel Jetstream
- Inertia.js + Vue 3
- TailwindCSS
- MySQL o SQLite
- Chart.js + vue-chartjs
- Mailtrap para desarrollo de correos
- Vite

## Requisitos previos

- PHP 8.2 o superior
- Composer 2.x
- Node.js 18+ y npm
- MySQL 8+ o SQLite
- XAMPP o un servidor equivalente si vas a usar phpMyAdmin

## Instalacion paso a paso

1. Clonar el repositorio

```bash
git clone [url] && cd sistema-de-reservas
```

2. Instalar dependencias PHP

```bash
composer install
```

3. Instalar dependencias frontend

```bash
npm install
```

4. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

5. Configurar `.env`

### Opcion MySQL

```env
APP_URL=http://127.0.0.1:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reservacancha
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="reservas@reservacancha.com"
MAIL_FROM_NAME="ReservaCancha"

RESERVATION_SLOT_MINUTES=60
VITE_WHATSAPP_URL="https://wa.me/573113886216?text=Hola,%20tengo%20una%20duda%20sobre%20una%20reserva"
```

### Opcion SQLite

```env
DB_CONNECTION=sqlite
```

6. Crear la base de datos si usas MySQL

```sql
CREATE DATABASE reservacancha CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

7. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

8. Crear enlace de almacenamiento

```bash
php artisan storage:link
```

9. Compilar assets

```bash
npm run build
```

o en desarrollo:

```bash
npm run dev
```

10. Iniciar Laravel

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

## Acceso al sistema

- Sitio publico: `http://127.0.0.1:8001/`
- Login admin: `http://127.0.0.1:8001/login`

Usuario admin por defecto:

- Email: `admin@reservacancha.com`
- Password: `Admin123!`

## Funcionalidades principales

### Modulo publico

- listado de canchas activas con filtros por tipo
- carrusel horizontal de tarjetas en home con maximo 3 canchas visibles en desktop
- carrusel automatico de imagenes dentro de cada card cuando la cancha tiene varias fotos
- detalle completo de cada cancha
- agenda diaria por bloques de 1 hora
- bloques disponibles en verde y no disponibles en rojo
- proximos horarios disponibles
- reserva publica sin autenticacion
- confirmacion visual al crear reserva
- ubicacion de la cancha con direccion y mapa de Google embebido
- boton flotante de WhatsApp para dudas
- diseno responsive y estilizado

### Panel administrativo

- dashboard con metricas clave
- sidebar colapsable con modo compacto por iconos
- graficas reales con Chart.js:
  - reservas ultimos 7 dias
  - reservas por estado
  - reservas por cancha
  - ingresos por semana
- CRUD completo de canchas
- creacion y edicion de direccion de cancha para mapa publico
- gestion de disponibilidad semanal
- gestion de bloqueos manuales
- listado, detalle y cambio de estado de reservas
- reservas nuevas priorizadas para aprobacion o rechazo
- calendario semanal por cancha
- gestion visual de clientes a partir del historial de reservas
- pagina de reportes con filtros por rango
- exportacion CSV de reservas

## Reportes implementados

La pagina `admin/reports` incluye:

- total de reservaciones
- reservas confirmadas, pendientes y rechazadas
- ingresos totales
- tasa de conversion
- promedio por dia
- ranking de canchas por reservas confirmadas e ingresos
- grafica de horas mas activas
- top 10 de clientes frecuentes
- exportacion CSV del rango filtrado

## Manejo de imagenes de canchas

Cada cancha puede tener hasta 3 imagenes desde el panel admin.

Caracteristicas:

- carga multiple desde crear o editar cancha
- almacenamiento en disco `public`
- la primera imagen se usa como principal
- en home publico se rotan automaticamente si la cancha tiene mas de una
- las rutas se normalizan para evitar imagenes rotas entre `localhost` y `127.0.0.1`

## Ubicacion y mapas

Cada cancha puede guardar una direccion desde el panel admin.

Caracteristicas:

- campo `Direccion de la cancha` en crear y editar
- visualizacion publica de la direccion exacta
- mapa embebido de Google Maps en la vista de detalle
- boton para abrir la ubicacion directamente en Google Maps

## Seeders demo

Ademas de los seeders base, el proyecto incluye un seeder opcional para poblar clientes y reservas demo de los proximos 8 dias:

```bash
php artisan db:seed --class=DemoClientsAndReservationsSeeder
```

Ese seeder:

- crea clientes demo a partir de reservas
- genera reservas para los proximos 8 dias
- distribuye estados como `confirmed`, `pending`, `cancelled` y `rejected`
- evita duplicar historicos demo anteriores porque primero limpia los correos `@demo.reservacancha.test`

## Correos y notificaciones

Correos implementados:

- `ReservationCreatedMail`
- `ReservationConfirmedMail`
- `ReservationRejectedMail`
- `ReservationCancelledMail`

Para publicar vistas Markdown base:

```bash
php artisan vendor:publish --tag=laravel-mail
```

## Comandos utiles

```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=DemoClientsAndReservationsSeeder
php artisan storage:link
php artisan route:list
php artisan config:clear
php artisan about
npm run dev
npm run build
```

## Estructura del proyecto

- `app/Models`: modelos del dominio
- `app/Http/Controllers`: controladores publicos y administrativos
- `app/Http/Controllers/Admin`: controladores dedicados del panel, incluyendo reportes
- `app/Http/Requests`: validaciones de formularios
- `app/Mail`: mailables del flujo de reservas
- `database/migrations`: estructura de base de datos
- `database/seeders`: seeders de canchas, reservas y admin
- `resources/js/Pages`: paginas Vue del sitio y del panel
- `resources/js/Layouts`: layouts Inertia
- `resources/views/emails`: vistas de correo

## Notas de desarrollo

- Si cambias variables de entorno, ejecuta:

```bash
php artisan config:clear
```

- Si ves pantalla en blanco y existe `public/hot`, elimina ese archivo cuando no estes corriendo Vite dev.
- Para desarrollo rapido con phpMyAdmin, asegurate de que `DB_CONNECTION=mysql` y `DB_DATABASE=reservacancha` esten activos.

## Creditos

Proyecto final de Framework Laravel - Cristian Camilo Echeverri Giraldo  
Canchas de futbol - Andres Gonzalez Lopez
