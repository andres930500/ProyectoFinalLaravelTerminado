<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->json('images')->nullable()->after('image');
        });

        DB::table('spaces')
            ->whereNotNull('image')
            ->orderBy('id')
            ->eachById(function (object $space): void {
                DB::table('spaces')
                    ->where('id', $space->id)
                    ->update([
                        'images' => json_encode([$space->image], JSON_UNESCAPED_SLASHES),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
