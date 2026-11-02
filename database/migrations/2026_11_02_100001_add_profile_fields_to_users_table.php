<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('designation')->nullable()->after('email');
            $table->text('bio')->nullable()->after('designation');
            $table->json('social_links')->nullable()->after('bio');
            $table->boolean('is_active')->default(true)->index()->after('social_links');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['designation', 'bio', 'social_links', 'is_active', 'last_login_at', 'deleted_at']);
        });
    }
};
