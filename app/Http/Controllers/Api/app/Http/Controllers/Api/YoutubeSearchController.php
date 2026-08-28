<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class YoutubeSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $response = Http::timeout(12)
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

        if (!$response->ok()) {
            return response()->json([]);
        }

        $found = [];
        $this->collect($response->json(), $found);

        return response()->json(array_values($found));
    }

    private function collect(mixed $node, array &$out): void
    {
        if (count($out) >= 24 || !is_array($node)) {
            return;
        }

        if (isset($node['videoRenderer']['videoId'])) {
            $vr = $node['videoRenderer'];
            $id = $vr['videoId'];
            $title = '';
            foreach ($vr['title']['runs'] ?? [] as $run) {
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
                $this->collect($child, $out);
            }
        }
    }
}