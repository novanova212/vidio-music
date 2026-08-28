<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['Never Gonna Give You Up', 'dQw4w9WgXcQ'],
            ['Despacito', 'kJQP7kiw5Fk'],
            ['Shape of You', 'JGwWNGJdvx8'],
            ['Gangnam Style', '9bZkp7q19f0'],
            ['Bohemian Rhapsody', 'fJ9rUzIMcZQ'],
            ['Uptown Funk', 'OPf0YbXqDm0'],
            ['Counting Stars', 'hT_nvWreIhg'],
            ['Perfect', '2Vv-BfVoq4g'],
            ['See You Again', 'RgKAFK5djSk'],
            ['Baby Shark', 'XqZsoesa55w'],
        ];

        foreach ($items as [$title, $id]) {
            $url = 'https://www.youtube.com/watch?v='.$id;
            Video::updateOrCreate(
                ['source_url' => $url],
                [
                    'title' => $title,
                    'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
                    'description' => 'Video YouTube',
                    'thumbnail_url' => 'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg',
                ]
            );
        }
    }
}