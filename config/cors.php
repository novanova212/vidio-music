<?php

// Konfigurasi CORS: mengizinkan frontend (Vercel) memanggil API ini
// (Railway). Domain frontend diatur lewat env FRONTEND_URL.

return [
    'paths' => ['api/*', 'storage/*'],

    'allowed_methods' => ['*'],

    // Bisa lebih dari satu domain (misal preview deployment Vercel),
    // pisahkan dengan koma di env FRONTEND_URL.
    'allowed_origins' => array_filter(
        array_map('trim', explode(',', env('FRONTEND_URL', 'http://localhost:5173')))
    ),

    'allowed_origins_patterns' => [
        // Izinkan semua preview deployment Vercel (contoh: proyek-git-branch.vercel.app)
        '#^https://.*\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Range', 'Content-Length'],

    'max_age' => 0,

    'supports_credentials' => false,
];