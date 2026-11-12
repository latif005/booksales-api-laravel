<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
    'order_number',
    'customer_id',
    'book_id',
    'quantity',
    'total_amount',
    'payment_method',
    'payment_status',
    ];

    // Relasi ke User atau Customer
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Relasi ke Book
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
