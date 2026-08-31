<?php

namespace App\Services;

use App\Models\MediaComment;
use App\Models\MediaReaction;
use App\Models\MediaStat;
use App\Models\Song;
use App\Models\Video;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EngagementService
{
    public const TYPES = ['video', 'song', 'youtube'];

    public function resolve(string $type, string $key): array
    {
        $type = strtolower(trim($type));
        $key = trim($key);

        if (! in_array($type, self::TYPES, true)) {
            throw ValidationException::withMessages([
                'type' => 'Jenis media tidak valid.',
            ]);
        }

        if ($type === 'video') {
            $item = Video::where('slug', $key)->firstOrFail();

            return [$type, $item->slug, $item];
        }

        if ($type === 'song') {
            $item = Song::where('slug', $key)->firstOrFail();

            return [$type, $item->slug, $item];
        }

        $key = preg_replace('/^yt-/', '', $key);
        if (! preg_match('/^[\w-]{6,20}$/', $key)) {
            throw ValidationException::withMessages([
                'key' => 'ID video tidak valid.',
            ]);
        }

        return ['youtube', $key, null];
    }

    public function summary(string $type, string $key, ?string $guestId): array
    {
        [$type, $key, $item] = $this->resolve($type, $key);
        $stats = $this->counts($type, $key, $item);

        $mine = null;
        if ($guestId) {
            $mine = MediaReaction::where([
                'target_type' => $type,
                'target_key' => $key,
                'guest_id' => $guestId,
            ])->value('reaction');
        }

        return [
            'target_type' => $type,
            'target_key' => $key,
            'views' => $stats['views'],
            'likes' => $stats['likes'],
            'dislikes' => $stats['dislikes'],
            'comments_count' => MediaComment::where('target_type', $type)->where('target_key', $key)->count(),
            'my_reaction' => $mine,
        ];
    }

    public function recordView(string $type, string $key): array
    {
        [$type, $key, $item] = $this->resolve($type, $key);

        if ($item instanceof Video) {
            $item->increment('views');
        } elseif ($item instanceof Song) {
            $item->increment('views');
            $item->increment('plays');
        } else {
            $stat = MediaStat::firstOrCreate(
                ['target_type' => $type, 'target_key' => $key],
                ['views' => 0, 'likes' => 0, 'dislikes' => 0]
            );
            $stat->increment('views');
        }

        return $this->summary($type, $key, null);
    }

    public function react(string $type, string $key, string $guestId, string $reaction): array
    {
        if (! in_array($reaction, ['like', 'dislike'], true)) {
            throw ValidationException::withMessages([
                'reaction' => 'Reaksi harus like atau dislike.',
            ]);
        }

        [$type, $key, $item] = $this->resolve($type, $key);

        DB::transaction(function () use ($type, $key, $guestId, $reaction) {
            $existing = MediaReaction::where([
                'target_type' => $type,
                'target_key' => $key,
                'guest_id' => $guestId,
            ])->lockForUpdate()->first();

            if ($existing && $existing->reaction === $reaction) {
                $existing->delete();
            } elseif ($existing) {
                $existing->update(['reaction' => $reaction]);
            } else {
                MediaReaction::create([
                    'target_type' => $type,
                    'target_key' => $key,
                    'guest_id' => $guestId,
                    'reaction' => $reaction,
                ]);
            }

            $this->syncCounts($type, $key);
        });

        return $this->summary($type, $key, $guestId);
    }

    public function comments(string $type, string $key)
    {
        [$type, $key] = $this->resolve($type, $key);

        return MediaComment::where('target_type', $type)
            ->where('target_key', $key)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (MediaComment $c) => [
                'id' => $c->id,
                'author_name' => $c->author_name,
                'body' => $c->body,
                'created_at' => $c->created_at?->toIso8601String(),
            ]);
    }

    public function addComment(string $type, string $key, string $author, string $body): array
    {
        [$type, $key] = $this->resolve($type, $key);

        $author = trim(strip_tags($author));
        $body = trim(strip_tags($body));

        if ($author === '' || mb_strlen($author) > 40) {
            throw ValidationException::withMessages([
                'author_name' => 'Nama wajib diisi (maksimal 40 karakter).',
            ]);
        }

        if ($body === '' || mb_strlen($body) > 500) {
            throw ValidationException::withMessages([
                'body' => 'Komentar wajib diisi (maksimal 500 karakter).',
            ]);
        }

        $comment = MediaComment::create([
            'target_type' => $type,
            'target_key' => $key,
            'author_name' => $author,
            'body' => $body,
        ]);

        return [
            'id' => $comment->id,
            'author_name' => $comment->author_name,
            'body' => $comment->body,
            'created_at' => $comment->created_at?->toIso8601String(),
        ];
    }

    private function counts(string $type, string $key, Video|Song|null $item): array
    {
        if ($item instanceof Video || $item instanceof Song) {
            return [
                'views' => (int) ($item->views ?? $item->plays ?? 0),
                'likes' => (int) ($item->likes ?? 0),
                'dislikes' => (int) ($item->dislikes ?? 0),
            ];
        }

        $stat = MediaStat::firstOrCreate(
            ['target_type' => $type, 'target_key' => $key],
            ['views' => 0, 'likes' => 0, 'dislikes' => 0]
        );

        return [
            'views' => (int) $stat->views,
            'likes' => (int) $stat->likes,
            'dislikes' => (int) $stat->dislikes,
        ];
    }

    private function syncCounts(string $type, string $key): void
    {
        $likes = MediaReaction::where('target_type', $type)->where('target_key', $key)->where('reaction', 'like')->count();
        $dislikes = MediaReaction::where('target_type', $type)->where('target_key', $key)->where('reaction', 'dislike')->count();

        if ($type === 'video') {
            Video::where('slug', $key)->update(['likes' => $likes, 'dislikes' => $dislikes]);

            return;
        }

        if ($type === 'song') {
            Song::where('slug', $key)->update(['likes' => $likes, 'dislikes' => $dislikes]);

            return;
        }

        MediaStat::updateOrCreate(
            ['target_type' => $type, 'target_key' => $key],
            ['likes' => $likes, 'dislikes' => $dislikes]
        );
    }
}
