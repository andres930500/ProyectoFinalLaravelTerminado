<?php

namespace Tests\Feature;

use App\Models\Space;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaceImageUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_space_image_uses_relative_storage_url_for_local_uploads(): void
    {
        config([
            'app.url' => 'http://localhost',
            'filesystems.disks.public.url' => 'http://localhost/storage',
        ]);

        $space = Space::factory()->create([
            'image' => 'spaces/demo.jpg',
            'images' => ['spaces/demo.jpg', 'storage/spaces/extra.jpg'],
        ]);

        $this->assertSame('/storage/spaces/demo.jpg', $space->fresh()->image);
        $this->assertSame([
            '/storage/spaces/demo.jpg',
            '/storage/spaces/extra.jpg',
        ], $space->fresh()->images);
    }
}
