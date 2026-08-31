<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('media_comments', 'guest_id')) {
                $table->string('guest_id', 64)->nullable()->after('target_key');
                $table->index('guest_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media_comments', function (Blueprint $table) {
            if (Schema::hasColumn('media_comments', 'guest_id')) {
                $table->dropColumn('guest_id');
            }
        });
    }
};
