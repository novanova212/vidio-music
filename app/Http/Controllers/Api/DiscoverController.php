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
    public function videos(\Illuminate\Http\Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            return $this->search($request);
        }

        return response()->json(
            \Illuminate\Support\Facades\Cache::remember('discover:videos:'.now()->format('YmdH'), 3600, function () {
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

        // GET /api/youtube/search?q=...
    public function search(\Illuminate\Http\Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $apiKey = config('services.youtube.key');

        if (!empty($apiKey)) {
            $response = \Illuminate\Support\Facades\Http::timeout(12)->get(
                'https://www.googleapis.com/youtube/v3/search',
                [
                    'part' => 'snippet',
                    'type' => 'video',
                    'maxResults' => 24,
                    'q' => $q,
                    'safeSearch' => 'strict',
                    'regionCode' => 'ID',
                    'key' => $apiKey,
                ]
            );

            if ($response->successful()) {
                $items = collect($response->json('items', []))
                    ->map(function ($item) {
                        $id = $item['id']['videoId'] ?? null;
                        if (!$id) return null;
                        return [
                            'id' => 'yt-'.$id,
                            'title' => $item['snippet']['title'] ?? '',
                            'thumbnail_url' => 'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg',
                            'source_url' => 'https://www.youtube.com/watch?v='.$id,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                if (count($items)) {
                    return response()->json($items);
                }
            }
        }

        // Cadangan: tetap bisa cari tanpa API key
        $yt = \Illuminate\Support\Facades\Http::timeout(12)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->post('https://www.youtube.com/youtubei/v1/search?prettyPrint=false', [
                'context' => [
                    'client' => [
                        'clientName' => 'WEB',
                        'clientVersion' => '2.20241201.00.00',
                        'hl' => 'id',
                        'gl' => 'ID',
                        'safeSearch' => 'STRICT',
                    ],
                ],
                'query' => $q,
            ]);

        $found = [];
        if ($yt->ok()) {
            $this->collectSearch($yt->json(), $found);
        }

        return response()->json(array_values($found));
    }

    private function collectSearch(mixed $node, array &$out): void
    {
        if (count($out) >= 24 || !is_array($node)) {
            return;
        }

        if (isset($node['videoRenderer']['videoId'])) {
            $id = $node['videoRenderer']['videoId'];
            $title = '';
            foreach ($node['videoRenderer']['title']['runs'] ?? [] as $run) {
                $title .= $run['text'] ?? '';
            }
            if ($id && $title) {
                $out[$id] = [
                    'id' => 'yt-'.$id,
                    'title' => $title,
                    'thumbnail_url' => 'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg',
                    'source_url' => 'https://www.youtube.com/watch?v='.$id,
                ];
            }
        }

        foreach ($node as $child) {
            if (is_array($child)) {
                $this->collectSearch($child, $out);
            }
        }
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