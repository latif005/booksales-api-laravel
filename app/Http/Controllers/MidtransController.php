<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function notification(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string', 'max:100'],
        ]);

        $orderNumber = $validated['order_id'];

        Log::info('MIDTRANS WEBHOOK RECEIVED', [
            'order_id' => $orderNumber,
        ]);

        try {
            // Cari transaksi berdasarkan order number
            $transactions = Transaction::where('order_number', $orderNumber)
                ->where('payment_method', 'MIDTRANS')
                ->get();

            // Tangani notifikasi pengujian dari Midtrans
            if ($transactions->isEmpty()) {
                if (str_starts_with($orderNumber, 'payment_notif_test_')) {
                    Log::info('Midtrans test notification received', [
                        'order_id' => $orderNumber,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Test notification berhasil diterima',
                    ], 200);
                }

                Log::warning('Midtrans order not found', [
                    'order_id' => $orderNumber,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan',
                ], 404);
            }

            // Ambil konfigurasi Midtrans
            $statusKey = config('services.midtrans.status_key');
            $merchantId = config('services.midtrans.merchant_id');

            if (! $statusKey || ! $merchantId) {
                throw new \Exception('Konfigurasi Midtrans belum lengkap');
            }

            $isProduction = config('services.midtrans.is_production');

            $baseUrl = $isProduction
                ? 'https://api.midtrans.com'
                : 'https://api.sandbox.midtrans.com';

            // Ambil status terbaru langsung dari Midtrans
            $response = Http::withBasicAuth($statusKey, '')
                ->acceptJson()
                ->timeout(15)
                ->get($baseUrl.'/v2/'.rawurlencode($orderNumber).'/status');

            if (! $response->successful()) {
                Log::error('Midtrans Status API failed', [
                    'order_id' => $orderNumber,
                    'http_status' => $response->status(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memverifikasi transaksi ke Midtrans',
                ], 502);
            }

            $midtransData = $response->json();

            if (
                ! is_array($midtransData) ||
                empty($midtransData['order_id']) ||
                empty($midtransData['merchant_id']) ||
                empty($midtransData['gross_amount']) ||
                empty($midtransData['transaction_status'])
            ) {
                Log::error('Invalid Midtrans Status API response', [
                    'order_id' => $orderNumber,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Respons status transaksi dari Midtrans tidak valid',
                ], 502);
            }

            // Cocokkan identitas transaksi
            if (
                $midtransData['order_id'] !== $orderNumber ||
                $midtransData['merchant_id'] !== $merchantId
            ) {
                Log::warning('Midtrans transaction identity mismatch', [
                    'order_id' => $orderNumber,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Identitas transaksi tidak cocok',
                ], 403);
            }

            // Cocokkan total pembayaran dengan database
            $expectedAmount = number_format(
                (float) $transactions->sum('total_amount'),
                2,
                '.',
                ''
            );

            $actualAmount = number_format(
                (float) $midtransData['gross_amount'],
                2,
                '.',
                ''
            );

            if ($expectedAmount !== $actualAmount) {
                Log::warning('Midtrans amount mismatch', [
                    'order_id' => $orderNumber,
                    'expected_amount' => $expectedAmount,
                    'actual_amount' => $actualAmount,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Total pembayaran tidak cocok',
                ], 403);
            }

            // Tentukan status berdasarkan respons resmi Midtrans
            $transactionStatus = $midtransData['transaction_status'];
            $fraudStatus = $midtransData['fraud_status'] ?? null;

            if ($transactionStatus === 'capture') {
                $paymentStatus = $fraudStatus === 'accept'
                    ? 'paid'
                    : 'pending';
            } elseif ($transactionStatus === 'settlement') {
                $paymentStatus = 'paid';
            } elseif ($transactionStatus === 'pending') {
                $paymentStatus = 'pending';
            } elseif (in_array($transactionStatus, [
                'deny',
                'cancel',
                'expire',
            ], true)) {
                $paymentStatus = 'failed';
            } elseif ($transactionStatus === 'refund') {
                $paymentStatus = 'refunded';
            } elseif ($transactionStatus === 'partial_refund') {
                $paymentStatus = 'partially_refunded';
            } else {
                Log::warning('Unknown Midtrans transaction status', [
                    'order_id' => $orderNumber,
                    'transaction_status' => $transactionStatus,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Status transaksi tidak dikenali',
                ], 400);
            }

            // Perbarui status seluruh transaksi dalam pesanan
            $updated = Transaction::where('order_number', $orderNumber)
                ->where('payment_method', 'MIDTRANS')
                ->update([
                    'payment_status' => $paymentStatus,
                ]);

            Log::info('Midtrans payment status updated', [
                'order_id' => $orderNumber,
                'payment_status' => $paymentStatus,
                'updated_rows' => $updated,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi pembayaran berhasil diproses',
                'payment_status' => $paymentStatus,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Midtrans notification error', [
                'order_id' => $orderNumber,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses notifikasi pembayaran',
            ], 500);
        }
    }
}
