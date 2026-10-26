<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            ['name' => 'Fiksi', 'description' => 'Cerita dan imajinasi.'],
            ['name' => 'Pendidikan', 'description' => 'Buku pembelajaran.'],
            ['name' => 'Teknologi', 'description' => 'Buku teknologi.'],
            ['name' => 'Bisnis', 'description' => 'Buku tentang bisnis.'],
            ['name' => 'Pengembangan Diri', 'description' => 'Buku pengembangan diri.']
        ];

        foreach ($genres as $genre) {
            Genre::create($genre);
        }
    }
}
