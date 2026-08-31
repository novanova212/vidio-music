<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            if (! Schema::hasColumn('videos', 'likes')) {
                $table->unsignedBigInteger('likes')->default(0);
            }
            if (! Schema::hasColumn('videos', 'dislikes')) {
                $table->unsignedBigInteger('dislikes')->default(0);
            }
        });

        Schema::table('songs', function (Blueprint $table) {
            if (! Schema::hasColumn('songs', 'likes')) {
                $table->unsignedBigInteger('likes')->default(0);
            }
            if (! Schema::hasColumn('songs', 'dislikes')) {
                $table->unsignedBigInteger('dislikes')->default(0);
            }
            if (! Schema::hasColumn('songs', 'views')) {
                $table->unsignedBigInteger('views')->default(0);
            }
        });

        Schema::create('media_stats', function (Blueprint $table) {
            $table->id();
            $table->string('target_type', 20);
            $table->string('target_key', 80);
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('dislikes')->default(0);
            $table->timestamps();
            $table->unique(['target_type', 'target_key']);
        });

        Schema::create('media_reactions', function (Blueprint $table) {
            $table->id();
            $table->string('target_type', 20);
            $table->string('target_key', 80);
            $table->string('guest_id', 64);
            $table->string('reaction', 10);
            $table->timestamps();
            $table->unique(['target_type', 'target_key', 'guest_id']);
        });

        Schema::create('media_comments', function (Blueprint $table) {
            $table->id();
            $table->string('target_type', 20);
            $table->string('target_key', 80);
            $table->string('author_name', 40);
            $table->text('body');
            $table->timestamps();
            $table->index(['target_type', 'target_key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_comments');
        Schema::dropIfExists('media_reactions');
        Schema::dropIfExists('media_stats');

        Schema::table('videos', function (Blueprint $table) {
            foreach (['likes', 'dislikes'] as $col) {
                if (Schema::hasColumn('videos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('songs', function (Blueprint $table) {
            foreach (['likes', 'dislikes', 'views'] as $col) {
                if (Schema::hasColumn('songs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
