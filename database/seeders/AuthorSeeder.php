<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        $authors = [
            [
                'name' => 'Andrea Hirata',
                'photo' => 'andrea-hirata.jpg',
                'bio' => 'Penulis novel Laskar Pelangi.'
            ],
            [
                'name' => 'Tere Liye',
                'photo' => 'tere-liye.jpg',
                'bio' => 'Penulis novel Indonesia.'
            ],
            [
                'name' => 'Dee Lestari',
                'photo' => 'dee-lestari.jpg',
                'bio' => 'Penulis novel dan penyanyi.'
            ],
            [
                'name' => 'Raditya Dika',
                'photo' => 'raditya-dika.jpg',
                'bio' => 'Penulis dan komedian Indonesia.'
            ],
            [
                'name' => 'Pramoedya Ananta Toer',
                'photo' => 'pramoedya.jpg',
                'bio' => 'Sastrawan Indonesia.'
            ]
        ];

        foreach ($authors as $author) {
            Author::create($author);
        }
    }
}
