<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Catatan arsitektur: controller ini TIDAK menyimpan file video di server.
// Yang disimpan cuma link (source_url) ke video yang sudah ada di tempat
// lain (Google Drive, hosting sendiri, dsb). Jadi tidak butuh disk/storage
// besar, dan laptop/server kita tetap ringan.
class VideoController extends Controller
{
    // GET /api/videos - daftar semua video
    public function index()
    {
        $videos = Video::latest()->paginate(12);

        return response()->json($videos);
    }

    // GET /api/videos/{slug} - detail satu video (sekaligus tambah hitungan views)
    public function show(string $slug)
    {
        $video = Video::where('slug', $slug)->firstOrFail();
        $video->increment('views');

        return response()->json($video);
    }

    // GET /api/videos/{slug}/download - hitung unduhan, lalu arahkan
    // (redirect) langsung ke source_url (video aslinya, di server lain).
    public function download(string $slug)
    {
        $video = Video::where('slug', $slug)->firstOrFail();
        $video->increment('downloads');

        return redirect()->away($video->source_url);
    }

    // POST /api/videos - tambah video baru CUKUP DENGAN LINK, tanpa upload file
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source_url' => 'required|url|max:2048',
            'thumbnail_url' => 'nullable|url|max:2048',
            'mime_type' => 'nullable|string|max:100',
        ]);

        $video = Video::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(6),
            'description' => $data['description'] ?? null,
            'source_url' => $data['source_url'],
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            ...(isset($data['mime_type']) ? ['mime_type' => $data['mime_type']] : []),
        ]);

        return response()->json($video, 201);
    }
}