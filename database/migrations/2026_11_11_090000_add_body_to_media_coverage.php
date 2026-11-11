<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Media coverage gets its own page again, as it had on the previous site at /media/{slug}.
     * The body holds what CDS wrote about the coverage; the outlet's own story stays a link.
     */
    public function up(): void
    {
        Schema::table('media_coverage', function (Blueprint $table) {
            $table->longText('body')->nullable()->after('summary');
        });
    }

    public function down(): void
    {
        Schema::table('media_coverage', function (Blueprint $table) {
            $table->dropColumn('body');
        });
    }
};
