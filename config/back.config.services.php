<?php

return [
    // API Key YouTube Data API v3, dipakai DiscoverController untuk
    // mengambil video/musik "otomatis" tanpa perlu di-upload manual.
    // Daftar gratis di https://console.cloud.google.com
    'youtube' => [
        'key' => env('YOUTUBE_API_KEY'),
    ],
];