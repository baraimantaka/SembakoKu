<?php

namespace App\Http\Controllers;

use App\Events\CourierLocationUpdated;
use App\Events\OrderStatusUpdated;
use App\Models\CourierLocationLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CourierController extends Controller
{
    /**
     * Courier driver mobile dashboard.
     */
    public function index(Request $request): View
    {
        // If logged in as driver, show their own dashboard
        $currentUser = Auth::user();
        $courierId = $request->query('courier_id');

        if ($courierId) {
            $courier = User::where('id', $courierId)->whereIn('role', ['driver', 'courier'])->first();
        } elseif ($currentUser && $currentUser->isDriver()) {
            $courier = $currentUser;
        } else {
            $courier = User::whereIn('role', ['driver', 'courier'])->first();
        }

        if (!$courier) {
            abort(404, 'Kurir tidak ditemukan. Pastikan akun kurir telah dibuat melalui seeder.');
        }

        // Active task assigned to this courier (PROCESSING or SHIPPED)
        $activeTask = Order::where('courier_id', $courier->id)
            ->whereIn('order_status', ['PROCESSING', 'SHIPPED'])
            ->with(['customer', 'items.product', 'locationLogs'])
            ->latest()
            ->first();

        // Pending available tasks that haven't been assigned yet
        $availableTasks = Order::whereNull('courier_id')
            ->whereIn('order_status', ['CONFIRMED', 'PROCESSING'])
            ->with(['customer', 'items.product'])
            ->latest()
            ->get();

        // Courier's completed deliveries history
        $completedTasks = Order::where('courier_id', $courier->id)
            ->where('order_status', 'DELIVERED')
            ->with(['customer', 'items.product'])
            ->latest()
            ->take(10)
            ->get();

        $allCouriers = User::whereIn('role', ['driver', 'courier'])->get();

        return view('courier.index', compact('courier', 'activeTask', 'availableTasks', 'completedTasks', 'allCouriers'));
    }

    /**
     * Toggle courier online/offline status.
     */
    public function toggleOnline(Request $request, User $courier): JsonResponse
    {
        $courier->is_online = !$courier->is_online;
        $courier->save();

        return response()->json([
            'success' => true,
            'is_online' => $courier->is_online,
            'message' => $courier->is_online ? 'Status Anda sekarang ONLINE. Siap menerima tugas.' : 'Status Anda OFFLINE.',
        ]);
    }

    /**
     * Courier accepts an available order task.
     */
    public function acceptTask(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $courierId = $request->input('courier_id');
        $courier = User::where('id', $courierId)->whereIn('role', ['driver', 'courier'])->first()
            ?? User::whereIn('role', ['driver', 'courier'])->firstOrFail();

        $order->courier_id = $courier->id;
        $order->order_status = 'SHIPPED';
        $order->save();

        event(new OrderStatusUpdated($order));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diterima. Silakan antarkan pesanan.',
            ]);
        }

        return back()->with('success', "Tugas pesanan {$order->invoice_no} berhasil diterima.");
    }

    /**
     * Courier updates GPS location log and broadcasts to WebSocket / Reverb.
     */
    public function updateLocation(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'courier_id' => ['nullable', 'string', 'exists:users,id'],
        ]);

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        // Update courier current coordinates in users table if courier exists
        if ($order->courier) {
            $order->courier->update([
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
        }

        // Save location log
        CourierLocationLog::create([
            'order_id' => $order->id,
            'latitude' => $lat,
            'longitude' => $lng,
            'created_at' => now(),
        ]);

        // Broadcast to WebSocket Reverb / Pusher
        event(new CourierLocationUpdated(
            orderId: $order->id,
            latitude: $lat,
            longitude: $lng,
            courierName: $order->courier?->name ?? 'Kurir SembakoKu',
            updatedAt: now()->toIso8601String()
        ));

        return response()->json([
            'success' => true,
            'message' => 'Lokasi kurir berhasil diperbarui & dibroadcast.',
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }

    /**
     * Start delivery (mark status as SHIPPED).
     */
    public function startDelivery(Order $order): JsonResponse
    {
        $order->order_status = 'SHIPPED';
        $order->save();

        event(new OrderStatusUpdated($order));

        return response()->json([
            'success' => true,
            'message' => 'Pengantaran dimulai. Sistem GPS tracking aktif.',
            'order_status' => $order->order_status,
        ]);
    }

    /**
     * Complete delivery with Proof of Delivery (POD) photo upload.
     */
    public function completeDelivery(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $request->validate([
            'proof_image' => ['nullable', 'image', 'max:5120'], // max 5MB photo
            'proof_image_url' => ['nullable', 'string', 'max:1000'],
        ]);

        $proofUrl = $request->input('proof_image_url')
            ?? 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=600&q=80';

        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('proofs', 'public');
            $proofUrl = asset('storage/' . $path);
        }

        $order->order_status = 'DELIVERED';
        $order->proof_image_url = $proofUrl;

        // If payment method is COD, mark as PAID when customer receives goods
        if ($order->payment_method === 'COD') {
            $order->payment_status = 'PAID';
        }

        $order->save();

        event(new OrderStatusUpdated($order));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil diselesaikan dengan bukti penerimaan.',
                'order_status' => 'DELIVERED',
                'proof_url' => $order->proof_image_url,
            ]);
        }

        return back()->with('success', "Pesanan {$order->invoice_no} berhasil diselesaikan.");
    }
}
