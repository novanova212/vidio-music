<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DiscoverController extends Controller
{
    private array $videoKeywords = [
        'desk setup aesthetic', 'laptop setup tour', 'coding javascript',
        'tech review indonesia', 'dj set edm official', 'cinematic broll',
        'photography night city', 'basketball highlights', 'study with me',
        'guitar cover acoustic',
    ];

    private array $musicKeywords = [
        'edm remix', 'lofi beats', 'lagu indonesia terbaru', 'musik pop indonesia',
        'electronic dance music', 'top hits', 'pop indonesia', 'chill hits',
    ];

    public function music(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            return response()->json($this->fetchFromSpotify($q, 20));
        }

        if ($request->boolean('refresh')) {
            return response()->json(
                $this->fetchFromSpotify($this->musicKeywords[array_rand($this->musicKeywords)], 12)
            );
        }

        return response()->json(
            Cache::remember('discover:music:spotify:'.now()->format('YmdH'), 3600, function () {
                return $this->fetchFromSpotify($this->musicKeywords[array_rand($this->musicKeywords)], 12);
            })
        );
    }

    /**
     * Ambil daftar lagu langsung dari Spotify Search API (Client Credentials Flow).
     * Ini GRATIS — cuma butuh daftar app di https://developer.spotify.com/dashboard
     * lalu isi SPOTIFY_CLIENT_ID & SPOTIFY_CLIENT_SECRET di .env. Tidak ada file
     * musik yang diunduh/disimpan di server maupun perangkat kita — hanya metadata
     * (judul, artis, cover, link) yang ditampilkan, persis seperti cara video YouTube
     * ditampilkan lewat iframe di atas.
     */
    private function fetchFromSpotify(string $keyword, int $limit = 12): array
    {
        $token = $this->spotifyToken();
        if (! $token) {
            return [];
        }

        $response = Http::withToken($token)->timeout(12)->get('https://api.spotify.com/v1/search', [
            'q' => $keyword,
            'type' => 'track',
            'market' => 'ID',
            'limit' => $limit,
        ]);

        if (! $response->successful()) {
            return [];
        }

        return collect($response->json('tracks.items', []))
            ->map(function ($track) {
                $id = $track['id'] ?? null;
                if (! $id) {
                    return null;
                }

                $artists = collect($track['artists'] ?? [])->pluck('name')->filter()->implode(', ');
                $cover = $track['album']['images'][1]['url']
                    ?? $track['album']['images'][0]['url']
                    ?? null;

                return [
                    // Nama field disamakan dengan format discover video (youtube_id,
                    // channel_title, watch_url) supaya frontend tidak perlu bentuk baru.
                    'youtube_id' => $id,
                    'title' => $track['name'] ?? 'Tanpa judul',
                    'channel_title' => $artists ?: 'Tidak diketahui',
                    'thumbnail_url' => $cover,
                    'watch_url' => $track['external_urls']['spotify'] ?? "https://open.spotify.com/track/{$id}",
                    'duration_ms' => $track['duration_ms'] ?? null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function spotifyToken(): ?string
    {
        $clientId = env('SPOTIFY_CLIENT_ID');
        $clientSecret = env('SPOTIFY_CLIENT_SECRET');
        if (empty($clientId) || empty($clientSecret)) {
            return null;
        }

        return Cache::remember('spotify:client_credentials_token', 3300, function () use ($clientId, $clientSecret) {
            $res = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->timeout(10)
                ->post('https://accounts.spotify.com/api/token', [
                    'grant_type' => 'client_credentials',
                ]);

            return $res->successful() ? $res->json('access_token') : null;
        });
    }

    public function videos(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            return $this->search($request);
        }

        if ($request->boolean('refresh')) {
            return response()->json(
                $this->fetchFromYouTube($this->videoKeywords[array_rand($this->videoKeywords)])
            );
        }

        return response()->json(
            Cache::remember('discover:videos:'.now()->format('YmdH'), 3600, function () {
                return $this->fetchFromYouTube($this->videoKeywords[array_rand($this->videoKeywords)]);
            })
        );
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $apiKey = config('services.youtube.key') ?: env('YOUTUBE_API_KEY');
        $merged = [];

        if (! empty($apiKey)) {
            $channelId = $this->findChannelId($apiKey, $q);
            $fromChannel = $channelId ? $this->videosByChannel($apiKey, $channelId) : [];
            $fromSearch = $this->searchVideos($apiKey, $q);
            $seen = [];
            foreach (array_merge($fromChannel, $fromSearch) as $item) {
                if (isset($seen[$item['id']])) {
                    continue;
                }
                $seen[$item['id']] = true;
                $merged[] = $item;
            }
        }

        if (count($merged) === 0) {
            $merged = $this->searchWithoutKey($q);
        }

        return response()->json($merged);
    }

    private function searchWithoutKey(string $q): array
    {
        $yt = Http::timeout(15)
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

        return array_values($found);
    }

    private function findChannelId(string $apiKey, string $q): ?string
    {
        if (preg_match('#youtube\.com/channel/(UC[\w-]+)#', $q, $m)) {
            return $m[1];
        }

        $lookup = $q;
        if (preg_match('#youtube\.com/@([^/?]+)#', $q, $m)) {
            $lookup = '@'.$m[1];
        } elseif (preg_match('#youtube\.com/(?:c|user)/([^/?]+)#', $q, $m)) {
            $lookup = $m[1];
        }

        $res = Http::timeout(12)->get('https://www.googleapis.com/youtube/v3/search', [
            'part' => 'snippet',
            'type' => 'channel',
            'maxResults' => 1,
            'q' => $lookup,
            'key' => $apiKey,
        ]);

        return $res->json('items.0.id.channelId');
    }

    private function videosByChannel(string $apiKey, string $channelId): array
    {
        $res = Http::timeout(12)->get('https://www.googleapis.com/youtube/v3/search', [
            'part' => 'snippet',
            'type' => 'video',
            'channelId' => $channelId,
            'order' => 'date',
            'maxResults' => 24,
            'safeSearch' => 'strict',
            'key' => $apiKey,
        ]);

        return $this->mapSearchItems($res->json('items', []));
    }

    private function searchVideos(string $apiKey, string $q): array
    {
        $res = Http::timeout(12)->get('https://www.googleapis.com/youtube/v3/search', [
            'part' => 'snippet',
            'type' => 'video',
            'maxResults' => 24,
            'q' => $q,
            'safeSearch' => 'strict',
            'regionCode' => 'ID',
            'key' => $apiKey,
        ]);

        return $this->mapSearchItems($res->json('items', []));
    }

    private function mapSearchItems(array $items): array
    {
        return collect($items)
            ->map(function ($item) {
                $id = $item['id']['videoId'] ?? null;
                if (! $id) {
                    return null;
                }

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
    }

    private function collectSearch(mixed $node, array &$out): void
    {
        if (count($out) >= 24 || ! is_array($node)) {
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
        $apiKey = config('services.youtube.key') ?: env('YOUTUBE_API_KEY');
        if (empty($apiKey)) {
            return [];
        }

        $response = Http::timeout(12)->get('https://www.googleapis.com/youtube/v3/search', array_filter([
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