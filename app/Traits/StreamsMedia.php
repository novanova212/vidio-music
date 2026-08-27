<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Trait untuk streaming file media (video/musik) dari storage lokal,
 * mendukung HTTP Range Request supaya bisa di-seek (maju/mundur) oleh
 * player di browser, dan untuk resume download.
 */
trait StreamsMedia
{
    protected function streamFile(string $diskPath, string $mimeType, string $disk = 'public'): StreamedResponse
    {
        $storage = Storage::disk($disk);
        $fullPath = $storage->path($diskPath);
        $size = filesize($fullPath);
        $start = 0;
        $end = $size - 1;
        $status = 200;
        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
        ];

        if ($range = request()->header('Range')) {
            $status = 206;
            [, $range] = explode('=', $range, 2);
            [$start, $end] = array_pad(explode('-', $range), 2, null);
            $start = (int) $start;
            $end = $end === '' || $end === null ? $size - 1 : (int) $end;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        $length = $end - $start + 1;
        $headers['Content-Length'] = $length;

        return response()->stream(function () use ($fullPath, $start, $length) {
            $handle = fopen($fullPath, 'rb');
            fseek($handle, $start);
            $bufferSize = 8192;
            $bytesLeft = $length;
            while ($bytesLeft > 0 && !feof($handle)) {
                $read = min($bufferSize, $bytesLeft);
                echo fread($handle, $read);
                $bytesLeft -= $read;
                flush();
            }
            fclose($handle);
        }, $status, $headers);
    }

    // Download file asli utuh (Content-Disposition: attachment) agar
    // bisa disimpan & dibuka di perangkat lain dengan kualitas sumber asli.
    protected function downloadFile(string $diskPath, string $downloadName, string $disk = 'public')
    {
        return Storage::disk($disk)->download($diskPath, $downloadName);
    }
}