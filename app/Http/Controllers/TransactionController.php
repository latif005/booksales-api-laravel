<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Midtrans\Config;
use Midtrans\Snap;

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
            'payment_method' => 'required|in:COD,MIDTRANS',
        ]);

        $result = DB::transaction(function () use ($request, $validated) {
            $results = [];
            $itemDetails = [];
            $grossAmount = 0;

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

                // Menghitung subtotal
                $subtotal = $book->price * $item['quantity'];
                $grossAmount += $subtotal;

                // Mengurangi stok buku
                $book->decrement('stock', $item['quantity']);

                // Menyimpan transaksi
                $transaction = Transaction::create([
                    'order_number' => $validated['order_number'],
                    'customer_id' => $request->user()->id,
                    'book_id' => $book->id,
                    'quantity' => $item['quantity'],
                    'total_amount' => $subtotal,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'pending',
                ]);

                $results[] = $transaction->load(['customer', 'book']);

                // Menyiapkan detail item untuk Midtrans
                $itemDetails[] = [
                    'id' => (string) $book->id,
                    'price' => (int) $book->price,
                    'quantity' => (int) $item['quantity'],
                    'name' => substr($book->title, 0, 50),
                ];
            }

            // Jika pembayaran menggunakan Midtrans
            if ($validated['payment_method'] === 'MIDTRANS') {
                \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
                \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
                \Midtrans\Config::$isSanitized = config('services.midtrans.is_sanitized');
                \Midtrans\Config::$is3ds = config('services.midtrans.is_3ds');

                $user = $request->user();

                $params = [
                    'transaction_details' => [
                        'order_id' => $validated['order_number'],
                        'gross_amount' => (int) $grossAmount,
                    ],
                    'item_details' => $itemDetails,
                    'customer_details' => [
                        'first_name' => $user->name,
                        'email' => $user->email,
                    ],
                ];

                $snapToken = \Midtrans\Snap::createTransaction($params)->token;

                return [
                    'transactions' => $results,
                    'snap_token' => $snapToken,
                ];
            }

            return [
                'transactions' => $results,
                'snap_token' => null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => $validated['payment_method'] === 'MIDTRANS'
                ? 'Checkout berhasil. Silakan lanjutkan pembayaran.'
                : 'Checkout berhasil. Pembayaran dilakukan saat pesanan diterima.',
            'data' => $result['transactions'],
            'snap_token' => $result['snap_token'],
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

    public function midtransWebhook(Request $request)
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');

        try {
            $notification = new \Midtrans\Notification();

            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;

            $transactions = Transaction::where('order_number', $orderId)->get();

            if ($transactions->isEmpty()) {
                return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
            }

            $status = 'pending';

            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                $status = 'success';
            } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $status = 'failed';

                foreach ($transactions as $tx) {
                    $book = Book::find($tx->book_id);
                    if ($book) {
                        $book->increment('stock', $tx->quantity);
                    }
                }
            } elseif ($transactionStatus == 'pending') {
                $status = 'pending';
            }

            Transaction::where('order_number', $orderId)->update([
                'payment_status' => $status
            ]);

            return response()->json(['message' => 'Webhook berhasil diproses']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
