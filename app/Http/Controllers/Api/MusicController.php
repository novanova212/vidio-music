<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MusicController extends Controller
{
    public function index()
    {
        $songs = Song::latest()->paginate(20);

        return response()->json($songs);
    }

    public function show(string $slug)
    {
        $song = Song::where('slug', $slug)->firstOrFail();
        $song->increment('plays');
        $song->increment('views');

        return response()->json($song->fresh());
    }

    public function download(string $slug)
    {
        $song = Song::where('slug', $slug)->firstOrFail();
        $song->increment('downloads');

        return redirect()->away($song->source_url);
    }

    public function destroy(string $slug)
    {
        $song = Song::where('slug', $slug)->firstOrFail();
        $song->delete();

        return response()->json(['ok' => true]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'album' => 'nullable|string|max:255',
            'source_url' => 'required|url|max:2048',
            'cover_url' => 'nullable|url|max:2048',
            'mime_type' => 'nullable|string|max:100',
        ]);

        $song = Song::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(6),
            'artist' => $data['artist'] ?? null,
            'album' => $data['album'] ?? null,
            'source_url' => $data['source_url'],
            'cover_url' => $data['cover_url'] ?? null,
            ...(isset($data['mime_type']) ? ['mime_type' => $data['mime_type']] : []),
        ]);

        return response()->json($song, 201);
    }
}
