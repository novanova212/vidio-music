<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Traits\StreamsMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MusicController extends Controller
{
    use StreamsMedia;

    // GET /api/songs - daftar semua musik
    public function index()
    {
        $songs = Song::latest()->paginate(20);

        return response()->json($songs);
    }

    // GET /api/songs/{slug} - detail satu musik
    public function show(string $slug)
    {
        $song = Song::where('slug', $slug)->firstOrFail();

        return response()->json($song);
    }

    // GET /api/songs/{slug}/stream - putar musik (mendukung seek)
    public function stream(string $slug)
    {
        $song = Song::where('slug', $slug)->firstOrFail();
        $song->increment('plays');

        return $this->streamFile($song->file_path, $song->mime_type);
    }

    // GET /api/songs/{slug}/download - unduh file musik asli
    public function download(string $slug)
    {
        $song = Song::where('slug', $slug)->firstOrFail();
        $song->increment('downloads');

        $extension = pathinfo($song->file_path, PATHINFO_EXTENSION);
        $fileName = Str::slug($song->title).'.'.$extension;

        return $this->downloadFile($song->file_path, $fileName);
    }

    // POST /api/songs - upload musik baru (sumber asli disimpan apa adanya)
    public function store(Request $request)
    {
        $maxKb = (int) env('MEDIA_MAX_UPLOAD_MB', 500) * 1024;

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'album' => 'nullable|string|max:255',
            'file' => "required|file|mimetypes:audio/mpeg,audio/mp4,audio/wav,audio/flac|max:{$maxKb}",
            'cover' => 'nullable|image|max:5120',
        ]);

        $path = $request->file('file')->store('songs', 'public');
        $coverPath = $request->hasFile('cover')
            ? $request->file('cover')->store('covers', 'public')
            : null;

        $song = Song::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(6),
            'artist' => $data['artist'] ?? null,
            'album' => $data['album'] ?? null,
            'file_path' => $path,
            'mime_type' => $request->file('file')->getMimeType(),
            'file_size' => $request->file('file')->getSize(),
            'cover_path' => $coverPath,
        ]);

        return response()->json($song, 201);
    }
}