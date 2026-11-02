<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Book;
use Illuminate\Http\Request;

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

    // Menambahkan transaksi (Customer)
    public function store(Request $request)
    {
        $request->validate([
            'order_number' => 'required|unique:transactions,order_number',
            'book_id' => 'required|exists:books,id',
            'total_amount' => 'required|numeric|min:0'
        ]);

        $transaction = Transaction::create([
            'order_number' => $request->order_number,
            'customer_id' => $request->user()->id,
            'book_id' => $request->book_id,
            'total_amount' => $request->total_amount
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil ditambahkan',
            'data' => $transaction->load(['customer', 'book'])
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

    // Mengubah transaksi (Customer)
    public function update(Request $request, $id)
    {
        $transaction = Transaction::where('customer_id', auth()->id())
            ->findOrFail($id);

        $request->validate([
            'order_number' => 'required|unique:transactions,order_number,' . $id,
            'book_id' => 'required|exists:books,id',
            'total_amount' => 'required|numeric|min:0'
        ]);

        $transaction->update([
            'order_number' => $request->order_number,
            'book_id' => $request->book_id,
            'total_amount' => $request->total_amount
        ]);

        return response()->json([
            'message' => 'Transaksi berhasil diperbarui',
            'data' => $transaction->load(['customer', 'book'])
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