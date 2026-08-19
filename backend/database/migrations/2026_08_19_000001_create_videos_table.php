<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Path file video asli (kualitas sumber) di storage, dipakai untuk stream & download
            $table->string('file_path');
            $table->string('mime_type')->default('video/mp4');
            $table->unsignedBigInteger('file_size')->default(0); // dalam byte
            $table->unsignedInteger('duration')->nullable(); // dalam detik
            $table->string('thumbnail_path')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('downloads')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};