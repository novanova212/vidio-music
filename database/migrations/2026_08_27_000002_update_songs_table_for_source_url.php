<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Sama seperti videos: dari 'simpan file di server' jadi 'simpan link'.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->string('source_url')->nullable()->after('album');
            $table->string('cover_url')->nullable()->after('source_url');
        });

        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_size', 'cover_path']);
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('album');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('cover_path')->nullable();
            $table->dropColumn(['source_url', 'cover_url']);
        });
    }
};