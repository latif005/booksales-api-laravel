<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            [
                'title' => 'Laskar Pelangi',
                'description' => 'Kisah perjuangan anak-anak Belitung.',
                'price' => 85000,
                'stock' => 20,
                'cover_photo' => 'laskar-pelangi.jpg',
                'genre_id' => 1,
                'author_id' => 1
            ],
            [
                'title' => 'Bumi',
                'description' => 'Novel petualangan dunia paralel.',
                'price' => 90000,
                'stock' => 15,
                'cover_photo' => 'bumi.jpg',
                'genre_id' => 1,
                'author_id' => 2
            ],
            [
                'title' => 'Perahu Kertas',
                'description' => 'Kisah mimpi dan perjalanan cinta.',
                'price' => 78000,
                'stock' => 12,
                'cover_photo' => 'perahu-kertas.jpg',
                'genre_id' => 1,
                'author_id' => 3
            ],
            [
                'title' => 'Koala Kumal',
                'description' => 'Kumpulan cerita humor dan kehidupan.',
                'price' => 65000,
                'stock' => 10,
                'cover_photo' => 'koala-kumal.jpg',
                'genre_id' => 1,
                'author_id' => 4
            ],
            [
                'title' => 'Bumi Manusia',
                'description' => 'Novel tentang kehidupan pada masa kolonial.',
                'price' => 95000,
                'stock' => 8,
                'cover_photo' => 'bumi-manusia.jpg',
                'genre_id' => 1,
                'author_id' => 5
            ]
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}