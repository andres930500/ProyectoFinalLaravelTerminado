<?php

namespace Tests\Feature;

use App\Models\Space;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SpaceLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_space_page_includes_google_maps_location_data(): void
    {
        $space = Space::factory()->create([
            'slug' => 'goles',
            'address' => 'Carrera 23 # 64-35, Manizales',
            'is_active' => true,
        ]);

        $response = $this->get(route('public.spaces.show', $space->slug));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Spaces/Show')
            ->where('location.address', 'Carrera 23 # 64-35, Manizales')
            ->where('location.embedUrl', fn ($value) => is_string($value) && str_contains($value, 'google.com/maps'))
            ->where('location.mapsUrl', fn ($value) => is_string($value) && str_contains($value, 'google.com/maps/search'))
        );
    }
}
