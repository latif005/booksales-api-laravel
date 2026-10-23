<?php

namespace App\Models;

class Author
{
    public static function all()
    {
        return [
            [
                'id' => 1,
                'name' => 'Andrea Hirata',
                'country' => 'Indonesia'
            ],
            [
                'id' => 2,
                'name' => 'Tere Liye',
                'country' => 'Indonesia'
            ],
            [
                'id' => 3,
                'name' => 'Dee Lestari',
                'country' => 'Indonesia'
            ],
            [
                'id' => 4,
                'name' => 'Raditya Dika',
                'country' => 'Indonesia'
            ],
            [
                'id' => 5,
                'name' => 'Pramoedya Ananta Toer',
                'country' => 'Indonesia'
            ]
        ];
    }
}
