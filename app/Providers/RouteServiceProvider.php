<?php

namespace App\Providers;

use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::bind('space', fn (string $value) => Space::where('slug', $value)->firstOrFail());
        Route::bind('reservation', fn (string $value) => Reservation::where('slug', $value)->firstOrFail());
    }
}
