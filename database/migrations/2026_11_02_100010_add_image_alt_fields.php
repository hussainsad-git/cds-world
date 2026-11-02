<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $map = [
        'articles' => 'featured_alt',
        'events' => 'banner_alt',
        'pages' => 'featured_alt',
        'categories' => 'cover_alt',
        'media_coverage' => 'thumb_alt',
        'videos' => 'thumb_alt',
        'gallery_albums' => 'cover_alt',
    ];

    public function up(): void
    {
        foreach ($this->map as $table => $column) {
            Schema::table($table, fn (Blueprint $t) => $t->string($column)->nullable());
        }
    }

    public function down(): void
    {
        foreach ($this->map as $table => $column) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));
        }
    }
};
