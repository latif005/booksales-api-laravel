<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    // Menampilkan semua transaksi (Admin)
    public function index()
    {
        $transactions = Transaction::with(['customer', 'book'])->get();

        return response()->json([
            'message' => 'Data transaksi berhasil diambil',
            'data' => $transactions
        ], 200);
    }

    // Menampilkan riwayat transaksi Customer
    public function myHistory(Request $request)
    {
        $transactions = Transaction::with(['book'])
            ->where('customer_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat transaksi berhasil diambil',
            'data' => $transactions
        ], 200);
    }

    // Menambahkan transaksi (Customer)
    public function store(Request $request)
    {
        $request->validate([
            'order_number' => 'required|unique:transactions,order_number',
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $transaction = DB::transaction(function () use ($request) {

            $book = Book::where('id', $request->book_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Mengecek ketersediaan stok
            if ($book->stock < $request->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Stok buku tidak mencukupi']
                ]);
            }

            // Mengurangi stok buku
            $book->decrement('stock', $request->quantity);

            // Menyimpan transaksi
            return Transaction::create([
                'order_number' => $request->order_number,
                'customer_id' => $request->user()->id,
                'book_id' => $request->book_id,
                'total_amount' => $book->price * $request->quantity
            ]);
        });

        return response()->json([
            'message' => 'Transaksi berhasil ditambahkan',
            'data' => $transaction->load(['customer', 'book'])
        ], 201);
    }


    // Checkout keranjang belanja (Customer)
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|unique:transactions,order_number',
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|integer|distinct|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:COD',
        ]);

        $transactions = DB::transaction(function () use ($request, $validated) {
            $results = [];

            foreach ($validated['items'] as $item) {
                $book = Book::where('id', $item['book_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // Mengecek ketersediaan stok
                if ($book->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ['Stok buku ' . $book->title . ' tidak mencukupi']
                    ]);
                }

                // Mengurangi stok buku
                $book->decrement('stock', $item['quantity']);

                // Menyimpan transaksi
                $transaction = Transaction::create([
                    'order_number' => $validated['order_number'],
                    'customer_id' => $request->user()->id,
                    'book_id' => $book->id,
                    'quantity' => $item['quantity'],
                    'total_amount' => $book->price * $item['quantity'],
                    'payment_method' => 'COD',
                    'payment_status' => 'pending',
                ]);

                $results[] = $transaction->load(['customer', 'book']);
            }

            return $results;
        });

        return response()->json([
            'success' => true,
            'message' => 'Checkout berhasil. Pembayaran dilakukan saat pesanan diterima.',
            'data' => $transactions
        ], 201);
    }

    // Menampilkan detail transaksi (Customer)
    public function show($id)
    {
        $transaction = Transaction::with(['customer', 'book'])
            ->where('customer_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'message' => 'Detail transaksi berhasil diambil',
            'data' => $transaction
        ], 200);
    }

    // Menghapus transaksi (Admin)
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return response()->json([
            'message' => 'Transaksi berhasil dihapus'
        ], 200);
    }
}
