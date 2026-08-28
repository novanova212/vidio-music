<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// Controller ini TIDAK menyimpan apa pun ke database. Setiap kali dipanggil
// (atau tiap 1 jam sekali karena di-cache), dia bertanya ke YouTube Data
// API v3 (cara resmi Google, bukan scraping): "kasih aku video apa saja
// yang lagi rame sekarang". Jadi beranda selalu ada isi walau tidak ada
// satupun video/musik yang di-upload manual oleh pemilik web.
class DiscoverController extends Controller
{
    // Kata kunci diacak tiap kali cache di-refresh, supaya hasilnya
    // terasa "berubah-ubah" bukan itu-itu saja.
    private array $videoKeywords = [
        'video viral indonesia', 'trending hari ini', 'video lucu',
        'konten kreator indonesia', 'vlog indonesia', 'berita terkini',
    ];

    private array $musicKeywords = [
        'lagu indonesia terbaru', 'musik pop indonesia', 'lagu viral tiktok',
        'musik akustik', 'lagu hits 2026', 'musik indie indonesia',
    ];

    // GET /api/discover/videos
    public function videos(): JsonResponse
    {
        return response()->json(
            Cache::remember('discover:videos:'.now()->format('YmdH'), 3600, function () {
                return $this->fetchFromYouTube($this->videoKeywords[array_rand($this->videoKeywords)]);
            })
        );
    }

    // GET /api/discover/music (dicari lewat kategori 'Music' di YouTube)
    public function music(): JsonResponse
    {
        return response()->json(
            Cache::remember('discover:music:'.now()->format('YmdH'), 3600, function () {
                return $this->fetchFromYouTube($this->musicKeywords[array_rand($this->musicKeywords)], videoCategoryId: 10);
            })
        );
    }

    private function fetchFromYouTube(string $keyword, ?int $videoCategoryId = null): array
    {
        $apiKey = config('services.youtube.key');

        if (empty($apiKey)) {
            // Belum diisi YOUTUBE_API_KEY di .env / environment Railway.
            return [];
        }

        $response = Http::get('https://www.googleapis.com/youtube/v3/search', array_filter([
            'part' => 'snippet',
            'type' => 'video',
            'maxResults' => 12,
            'order' => 'viewCount',
            'q' => $keyword,
            'regionCode' => 'ID',
            'videoCategoryId' => $videoCategoryId,
            'key' => $apiKey,
        ]));

        if (! $response->successful()) {
            return [];
        }

        return collect($response->json('items', []))
            ->map(fn ($item) => [
                'youtube_id' => $item['id']['videoId'] ?? null,
                'title' => $item['snippet']['title'] ?? '',
                'channel_title' => $item['snippet']['channelTitle'] ?? '',
                'thumbnail_url' => $item['snippet']['thumbnails']['medium']['url'] ?? null,
                'watch_url' => isset($item['id']['videoId'])
                    ? 'https://www.youtube.com/watch?v='.$item['id']['videoId']
                    : null,
            ])
            ->filter(fn ($item) => $item['youtube_id'] !== null)
            ->values()
            ->all();
    }
}