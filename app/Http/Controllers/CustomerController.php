<?php

namespace App\Http\Controllers;

use App\Events\NewOrderCreated;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\LogisticService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display customer storefront catalog.
     */
    public function index(Request $request): View
    {
        $categories = [
            'Semua' => 'lucide-layout-grid',
            'Beras' => 'lucide-wheat',
            'Minyak & Bumbu' => 'lucide-flame',
            'Telur & Susu' => 'lucide-egg',
            'Gas & Galon' => 'lucide-fuel',
            'Makanan Ringan' => 'lucide-cookie',
            'Kebersihan' => 'lucide-sparkles',
        ];

        $selectedCategory = $request->query('category', 'Semua');
        $search = $request->query('q');

        $query = Product::query()->where('is_active', true);

        if ($selectedCategory && $selectedCategory !== 'Semua') {
            $query->where('category', $selectedCategory);
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $products = $query->orderBy('stock', 'desc')
            ->orderBy('total_sold', 'desc')
            ->get();

        $storeSettings = LogisticService::getSettings();
        $storeStatus = LogisticService::getStoreStatus();

        // Customer Profile: use authenticated user or default demo customer in Semarang
        if (Auth::check()) {
            $customerProfile = Auth::user();
        } else {
            $customerProfile = User::where('role', 'customer')->first() ?? (object) [
                'name' => 'Siti Nurhaliza',
                'phone' => '081234567892',
                'address' => 'Jl. Erlangga Barat VII No. 12, Pleburan, Kec. Semarang Selatan, Kota Semarang',
                'latitude' => -7.0125000,
                'longitude' => 110.4320000,
            ];
        }

        $customerLat = (float) ($customerProfile->latitude ?? -7.0125000);
        $customerLng = (float) ($customerProfile->longitude ?? 110.4320000);

        $initialShipping = LogisticService::calculateFee($customerLat, $customerLng);

        return view('customer.index', compact(
            'products',
            'categories',
            'selectedCategory',
            'search',
            'storeSettings',
            'storeStatus',
            'customerProfile',
            'initialShipping'
        ));
    }

    /**
     * Live search API for debounced instant search.
     */
    public function liveSearch(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        $category = $request->query('category', 'Semua');

        $query = Product::query()->where('is_active', true);

        if (!empty($q)) {
            $query->where('title', 'like', "%{$q}%");
        }

        if ($category !== 'Semua') {
            $query->where('category', $category);
        }

        $products = $query->orderBy('total_sold', 'desc')->take(20)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'category' => $p->category,
                'unit' => $p->unit,
                'price' => $p->price,
                'formatted_price' => $p->formatted_price,
                'stock' => $p->stock,
                'stock_badge' => $p->stock_badge,
                'total_sold' => $p->total_sold,
                'formatted_sold' => $p->formatted_sold,
                'image_url' => $p->image_url,
            ];
        });

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Dynamic calculate shipping fee based on map coordinates.
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $feeData = LogisticService::calculateFee((float) $request->lat, (float) $request->lng);

        return response()->json([
            'distance_km' => $feeData['distance_km'],
            'shipping_fee' => $feeData['fee'],
            'service_fee' => $feeData['service_fee'],
            'store_lat' => $feeData['store_lat'],
            'store_lng' => $feeData['store_lng'],
            'formatted_shipping_fee' => 'Rp ' . number_format($feeData['fee'], 0, ',', '.'),
            'formatted_service_fee' => 'Rp ' . number_format($feeData['service_fee'], 0, ',', '.'),
        ]);
    }

    /**
     * Process checkout and save new order.
     * Note: Guest users must login first.
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'require_login' => true,
                'redirect_url' => route('login'),
                'message' => 'Silakan masuk ke akun Anda terlebih dahulu untuk menyelesaikan pesanan.',
            ], 401);
        }

        $validated = $request->validated();
        /** @var User $customer */
        $customer = Auth::user();

        // Update customer profile details if provided
        $customer->update([
            'name' => $validated['customer_name'],
            'phone' => $validated['customer_phone'] ?? $customer->phone,
            'address' => $validated['delivery_address'],
            'latitude' => $validated['delivery_lat'],
            'longitude' => $validated['delivery_lng'],
        ]);

        $order = DB::transaction(function () use ($validated, $customer) {
            // Compute subtotal safely from verified product database
            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($validated['cart_items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['id']);
                $qty = (int) $item['quantity'];

                $lineTotal = $product->price * $qty;
                $subtotal += $lineTotal;

                // Deduct stock and increment total_sold
                $product->decrement('stock', $qty);
                $product->increment('total_sold', $qty);

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $product->price,
                ];
            }

            // Calculate dynamic shipping fee from store settings
            $feeData = LogisticService::calculateFee(
                (float) $validated['delivery_lat'],
                (float) $validated['delivery_lng']
            );

            $shippingFee = $feeData['fee'];
            $totalPrice = $subtotal + $shippingFee;

            // Create Order
            $order = Order::create([
                'invoice_no' => Order::generateInvoiceNo(),
                'customer_id' => $customer->id,
                'courier_id' => null,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total_price' => $totalPrice,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'PENDING',
                'order_status' => 'PENDING',
                'min_order_satisfied' => ($subtotal >= 50000),
                'delivery_address' => $validated['delivery_address'],
                'delivery_lat' => $validated['delivery_lat'],
                'delivery_lng' => $validated['delivery_lng'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Save order items
            foreach ($itemsToCreate as $itemData) {
                $order->items()->create($itemData);
            }

            return $order;
        });

        // Trigger Event for WebSocket / Reverb Broadcast to Store POS and Courier App
        event(new NewOrderCreated($order));

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat!',
            'order_id' => $order->id,
            'redirect_url' => route('orders.track', $order->id),
        ]);
    }

    /**
     * Customer order live tracking view.
     */
    public function tracking(Order $order): View
    {
        $order->load(['customer', 'courier', 'items.product', 'locationLogs']);

        $settings = LogisticService::getSettings();
        $storeLat = $settings->store_latitude;
        $storeLng = $settings->store_longitude;
        $storeName = $settings->store_name;
        $storeAddress = $settings->store_address;

        // Latest courier position or default store position
        $latestLog = $order->locationLogs->first();
        $courierLat = $latestLog ? $latestLog->latitude : ($order->courier?->latitude ?? $storeLat);
        $courierLng = $latestLog ? $latestLog->longitude : ($order->courier?->longitude ?? $storeLng);

        return view('customer.tracking', compact(
            'order',
            'storeLat',
            'storeLng',
            'storeName',
            'storeAddress',
            'courierLat',
            'courierLng'
        ));
    }

    /**
     * Real-time polling API endpoint for order tracking.
     */
    public function apiOrderStatus(Order $order): JsonResponse
    {
        $order->load(['courier', 'locationLogs']);
        $latestLog = $order->locationLogs->first();

        $settings = LogisticService::getSettings();
        $courierLat = $latestLog ? $latestLog->latitude : ($order->courier?->latitude ?? $settings->store_latitude);
        $courierLng = $latestLog ? $latestLog->longitude : ($order->courier?->longitude ?? $settings->store_longitude);

        return response()->json([
            'order_id' => $order->id,
            'invoice_no' => $order->invoice_no,
            'order_status' => $order->order_status,
            'status_label' => $order->status_label,
            'status_step_index' => $order->status_step_index,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'courier' => $order->courier ? [
                'name' => $order->courier->name,
                'phone' => $order->courier->phone,
            ] : null,
            'courier_location' => [
                'latitude' => $courierLat,
                'longitude' => $courierLng,
                'updated_at' => $latestLog?->created_at?->diffForHumans() ?? 'Baru saja',
            ],
        ]);
    }
}
