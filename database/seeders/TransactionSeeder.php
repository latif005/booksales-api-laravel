<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::create([
            'order_number' => 'ORD001',
            'customer_id' => 1,
            'book_id' => 1,
            'total_amount' => 250000.00,
        ]);

        Transaction::create([
            'order_number' => 'ORD002',
            'customer_id' => 1,
            'book_id' => 2,
            'total_amount' => 50000.00,
        ]);
    }
}
