<?php

namespace App\Http\Controllers;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Handle incoming webhook notification from Midtrans / Xendit QRIS payment gateway.
     */
    public function midtransWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Payment Webhook Received:', $payload);

        $invoiceNo = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');

        $order = Order::where('invoice_no', $invoiceNo)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            $order->payment_status = 'PAID';
            if ($order->order_status === 'PENDING') {
                $order->order_status = 'CONFIRMED';
            }
        } elseif ($transactionStatus === 'pending') {
            $order->payment_status = 'PENDING';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $order->payment_status = 'FAILED';
            $order->order_status = 'CANCELLED';
        }

        $order->save();

        event(new OrderStatusUpdated($order));

        return response()->json([
            'status' => 'success',
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
        ]);
    }

    /**
     * Demo one-click simulated QRIS payment verification.
     */
    public function simulateQrisPay(Order $order): RedirectResponse|JsonResponse
    {
        $order->payment_status = 'PAID';
        if ($order->order_status === 'PENDING') {
            $order->order_status = 'CONFIRMED';
        }
        $order->save();

        event(new OrderStatusUpdated($order));

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran QRIS berhasil diverifikasi otomatis!',
                'payment_status' => 'PAID',
                'order_status' => $order->order_status,
            ]);
        }

        return back()->with('success', "Pembayaran QRIS untuk {$order->invoice_no} BERHASIL diverifikasi!");
    }
}

