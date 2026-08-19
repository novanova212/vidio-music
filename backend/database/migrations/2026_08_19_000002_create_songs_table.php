<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('artist')->nullable();
            $table->string('album')->nullable();
            // Path file musik asli (kualitas sumber) di storage, dipakai untuk stream & download
            $table->string('file_path');
            $table->string('mime_type')->default('audio/mpeg');
            $table->unsignedBigInteger('file_size')->default(0); // dalam byte
            $table->unsignedInteger('duration')->nullable(); // dalam detik
            $table->string('cover_path')->nullable();
            $table->unsignedBigInteger('plays')->default(0);
            $table->unsignedBigInteger('downloads')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};