<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ubah tabel videos: dari 'simpan file di server' jadi 'simpan link ke
// sumber asli' (source_url) supaya tidak butuh storage lokal sama sekali.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('source_url')->nullable()->after('description');
            $table->string('thumbnail_url')->nullable()->after('source_url');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_size', 'thumbnail_path']);
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('description');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('thumbnail_path')->nullable();
            $table->dropColumn(['source_url', 'thumbnail_url']);
        });
    }
};