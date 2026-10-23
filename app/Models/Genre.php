<?php

namespace App\Models;

class Genre
{
    public static function all()
    {
        return [
            [
                'id' => 1,
                'name' => 'Fiksi',
                'description' => 'Buku cerita dan imajinasi'
            ],
            [
                'id' => 2,
                'name' => 'Pendidikan',
                'description' => 'Buku untuk pembelajaran'
            ],
            [
                'id' => 3,
                'name' => 'Teknologi',
                'description' => 'Buku tentang teknologi'
            ],
            [
                'id' => 4,
                'name' => 'Bisnis',
                'description' => 'Buku tentang bisnis'
            ],
            [
                'id' => 5,
                'name' => 'Pengembangan Diri',
                'description' => 'Buku pengembangan diri'
            ]
        ];
    }
}
