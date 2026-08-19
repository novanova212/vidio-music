<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Traits\StreamsMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    use StreamsMedia;

    // GET /api/videos - daftar semua video
    public function index()
    {
        $videos = Video::latest()->paginate(12);

        return response()->json($videos);
    }

    // GET /api/videos/{slug} - detail satu video
    public function show(string $slug)
    {
        $video = Video::where('slug', $slug)->firstOrFail();

        return response()->json($video);
    }

    // GET /api/videos/{slug}/stream - putar video (mendukung seek)
    public function stream(string $slug)
    {
        $video = Video::where('slug', $slug)->firstOrFail();
        $video->increment('views');

        return $this->streamFile($video->file_path, $video->mime_type);
    }

    // GET /api/videos/{slug}/download - unduh file video asli
    public function download(string $slug)
    {
        $video = Video::where('slug', $slug)->firstOrFail();
        $video->increment('downloads');

        $extension = pathinfo($video->file_path, PATHINFO_EXTENSION);
        $fileName = Str::slug($video->title).'.'.$extension;

        return $this->downloadFile($video->file_path, $fileName);
    }

    // POST /api/videos - upload video baru (sumber asli disimpan apa adanya)
    public function store(Request $request)
    {
        $maxKb = (int) env('MEDIA_MAX_UPLOAD_MB', 500) * 1024;

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => "required|file|mimetypes:video/mp4,video/webm,video/quicktime|max:{$maxKb}",
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $path = $request->file('file')->store('videos', 'public');
        $thumbPath = $request->hasFile('thumbnail')
            ? $request->file('thumbnail')->store('thumbnails', 'public')
            : null;

        $video = Video::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(6),
            'description' => $data['description'] ?? null,
            'file_path' => $path,
            'mime_type' => $request->file('file')->getMimeType(),
            'file_size' => $request->file('file')->getSize(),
            'thumbnail_path' => $thumbPath,
        ]);

        return response()->json($video, 201);
    }
}