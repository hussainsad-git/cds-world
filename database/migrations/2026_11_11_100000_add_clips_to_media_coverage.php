<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Broadcast clips for a coverage item: a list of {type: youtube|file, url}.
     * Television coverage is the clip, so the item's page leads with it.
     */
    public function up(): void
    {
        Schema::table('media_coverage', function (Blueprint $table) {
            $table->json('clips')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('media_coverage', function (Blueprint $table) {
            $table->dropColumn('clips');
        });
    }
};
