# 🏟️ ReservaCancha — Sistema de Reservas de Canchas de Fútbol

## Descripción

Plataforma web para gestión y reserva de canchas de fútbol desarrollada con Laravel 12, Jetstream (Inertia + Vue 3) y TailwindCSS. Permite consultar disponibilidad en tiempo real, realizar reservas sin necesidad de cuenta, y administrar todos los aspectos del negocio desde un panel protegido.

## Tecnologías

- PHP 8.2+, Laravel 12.x
- Laravel Jetstream con Inertia.js + Vue 3
- TailwindCSS 3.x
- MySQL / SQLite
- Mailtrap (desarrollo) para notificaciones por email
- Vite para compilación de assets

## Requisitos previos

- PHP 8.2 o superior con extensiones: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
- Composer 2.x
- Node.js 18+ y npm
- MySQL 8.0+ o SQLite

## Instalación paso a paso

1. Clonar el repositorio:

```bash
git clone [url] && cd sistema-de-reservas
```

2. Instalar dependencias PHP:

```bash
composer install
```

3. Instalar dependencias Node.js:

```bash
npm install
```

4. Configurar el archivo de entorno:

```bash
cp .env.example .env
php artisan key:generate
```

5. Configurar `.env` (sección base de datos y correo):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reservacancha
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=[tu_usuario_mailtrap]
MAIL_PASSWORD=[tu_password_mailtrap]

RESERVATION_SLOT_MINUTES=60
```

6. Crear la base de datos en MySQL:

```sql
CREATE DATABASE reservacancha CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

7. Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

8. (Opcional) Crear enlace de almacenamiento:

```bash
php artisan storage:link
```

9. Compilar assets:

```bash
npm run build
```

o para desarrollo:

```bash
npm run dev
```

10. Iniciar el servidor:

```bash
php artisan serve
```

11. Acceder a:

- Sitio público: `http://localhost:8000`
- Panel admin: `http://localhost:8000/login`
- Credenciales admin por defecto:
    - Email: `admin@reservacancha.com`
    - Password: `Admin123!`

## Comandos útiles de desarrollo

- `php artisan migrate:fresh --seed` reiniciar DB con datos de prueba
- `php artisan route:list` ver todas las rutas
- `php artisan tinker` consola interactiva
- `npm run dev` hot reload para desarrollo

## Variables de entorno importantes

- `RESERVATION_SLOT_MINUTES=60` duración mínima de cada reserva en minutos
- `APP_URL=http://localhost:8000` URL base de la aplicación
- `MAIL_MAILER=smtp` mailer usado para notificaciones
- `FILESYSTEM_DISK=public` recomendado para manejo de imágenes de canchas

## Estructura del proyecto

- `app/Models`:
  modelos principales del dominio como `Space`, `Reservation`, `Availability` y `BlockedSlot`.
- `app/Http/Controllers`:
  controladores del módulo público y panel administrativo.
- `app/Http/Requests`:
  validaciones dedicadas, por ejemplo `ReservationRequest`.
- `app/Mail`:
  mailables para reservas creadas, confirmadas, rechazadas y canceladas.
- `resources/js/Pages`:
  páginas Vue para el sitio público y el panel admin.
- `resources/js/Layouts`:
  layouts Inertia, incluyendo `PublicLayout` y `AppLayout`.
- `database/migrations`:
  estructura de base de datos.
- `database/seeders`:
  seeders de canchas, reservas y usuario administrador.
- `resources/views/emails`:
  vistas Markdown para correos del sistema.

## Funcionalidades principales

### Módulo público

- Listado de canchas activas con filtros por tipo.
- Vista de detalle de cada cancha con reglas, descripción y disponibilidad semanal.
- Consulta de próximos horarios disponibles.
- Creación de reservas sin necesidad de cuenta.
- Confirmación visual de reserva enviada.
- Envío de correo al crear la reserva.

### Panel administrativo

- Dashboard con métricas operativas.
- Gestión completa de canchas.
- Gestión de disponibilidad semanal por cancha.
- Gestión de bloqueos manuales de horarios.
- Listado y detalle de reservas.
- Aprobación, rechazo y cancelación de reservas.
- Calendario semanal por cancha con estados visuales de slots.
- Correos automáticos al confirmar, rechazar o cancelar una reserva.

## Usuario administrador por defecto

Al ejecutar `php artisan migrate --seed`, el sistema crea automáticamente un usuario administrador:

- Email: `admin@reservacancha.com`
- Password: `Admin123!`
- Nombre: `Administrador`

## Correos y Mailtrap

Para desarrollo se recomienda usar Mailtrap con las variables SMTP configuradas en `.env`.

Mailables implementados:

- `ReservationCreatedMail`
- `ReservationConfirmedMail`
- `ReservationRejectedMail`
- `ReservationCancelledMail`

## Publicar vistas Markdown de Laravel Mail

Para publicar las vistas base de correos Markdown:

```bash
php artisan vendor:publish --tag=laravel-mail
```

Luego puedes cambiar el color del botón a verde en:

`resources/views/vendor/mail/html/themes/default.css`

Busca estas clases y ajusta el color:

- `.button-primary`
- `.button-success`

Ejemplo:

```css
.button-primary,
.button-success {
    background-color: #059669;
    border-bottom: 8px solid #059669;
    border-left: 18px solid #059669;
    border-right: 18px solid #059669;
    border-top: 8px solid #059669;
}
```

## Notas de desarrollo

- Para usar imágenes de canchas subidas desde el panel, se recomienda mantener `FILESYSTEM_DISK=public`.
- Si usas SQLite para desarrollo rápido, deja `DB_CONNECTION=sqlite` y crea el archivo `database/database.sqlite`.
- Si cambias variables de entorno sensibles, ejecuta:

```bash
php artisan config:clear
```

## Créditos

Proyecto final de Framework Laravel — Cristian Camilo Echeverri Giraldo  
Canchas de fútbol — Andres Gonzalez lopez

---

ReservaCancha — Sistema de Reservas de Canchas de Fútbol
