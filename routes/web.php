<?php

use App\Http\Controllers\AdminAvailabilityController;
use App\Http\Controllers\AdminBlockedSlotController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\AdminSpaceController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SpaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SpaceController::class, 'index'])->name('home');
Route::get('/spaces/{space:slug}', [SpaceController::class, 'show'])->name('public.spaces.show');
Route::get('/reservations/new', [ReservationController::class, 'create'])->name('public.reservations.create');
Route::post('/reservations', [ReservationController::class, 'store'])->name('public.reservations.store');
Route::get('/reservations/{reservation:slug}/confirmation', [ReservationController::class, 'confirmation'])->name('public.reservations.confirmation');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/dashboard', '/home')->name('dashboard.redirect');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('admin.calendar');
    Route::get('/admin/notifications/summary', [NotificationController::class, 'summary'])->name('admin.notifications.summary');
    Route::get('/admin/clients', [ClientController::class, 'index'])->name('admin.clients.index');
    Route::get('/admin/clients/{email}', [ClientController::class, 'show'])
        ->where('email', '.*')
        ->name('admin.clients.show');
    Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('/admin/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
    Route::resource('admin/spaces', AdminSpaceController::class)->names('admin.spaces');
    Route::resource('admin/reservations', AdminReservationController::class)->only(['index', 'show'])->names('admin.reservations');
    Route::post('admin/reservations/{reservation:slug}/accept', [AdminReservationController::class, 'accept'])->name('admin.reservations.accept');
    Route::post('admin/reservations/{reservation:slug}/reject', [AdminReservationController::class, 'reject'])->name('admin.reservations.reject');
    Route::post('admin/reservations/{reservation:slug}/cancel', [AdminReservationController::class, 'cancel'])->name('admin.reservations.cancel');
    Route::post('admin/reservations/{reservation:slug}/finish', [AdminReservationController::class, 'finish'])->name('admin.reservations.finish');
    Route::resource('admin/blocked-slots', AdminBlockedSlotController::class)->except(['show'])->names('admin.blocked-slots');
    Route::resource('admin/availabilities', AdminAvailabilityController::class)->except(['show', 'index'])->names('admin.availabilities');
});
