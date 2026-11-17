<?php

namespace App\Http\Controllers;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\LogisticService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Store Admin POS Live Order Management.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'ALL');

        $query = Order::with(['customer', 'courier', 'items.product'])
            ->orderBy('created_at', 'desc');

        if ($statusFilter !== 'ALL') {
            $query->where('order_status', $statusFilter);
        }

        $orders = $query->paginate(15);

        // Couriers available for dispatch
        $onlineCouriers = User::whereIn('role', ['driver', 'courier'])
            ->where('is_online', true)
            ->get();

        // Status counts for badge tabs
        $counts = [
            'ALL' => Order::count(),
            'PENDING' => Order::where('order_status', 'PENDING')->count(),
            'CONFIRMED' => Order::where('order_status', 'CONFIRMED')->count(),
            'PROCESSING' => Order::where('order_status', 'PROCESSING')->count(),
            'SHIPPED' => Order::where('order_status', 'SHIPPED')->count(),
            'DELIVERED' => Order::where('order_status', 'DELIVERED')->count(),
            'CANCELLED' => Order::where('order_status', 'CANCELLED')->count(),
        ];

        return view('admin.orders', compact('orders', 'onlineCouriers', 'statusFilter', 'counts'));
    }

    /**
     * Real-time polling API for admin dashboard to detect new incoming orders.
     */
    public function apiLiveOrders(Request $request): JsonResponse
    {
        $lastChecked = $request->query('last_time', now()->subSeconds(30)->toDateTimeString());

        $newOrdersCount = Order::where('created_at', '>', $lastChecked)->count();
        $pendingCount = Order::where('order_status', 'PENDING')->count();
        $processingCount = Order::whereIn('order_status', ['CONFIRMED', 'PROCESSING'])->count();

        return response()->json([
            'new_orders_count' => $newOrdersCount,
            'pending_count' => $pendingCount,
            'processing_count' => $processingCount,
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Update order status from admin dashboard.
     */
    public function updateOrderStatus(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $request->validate([
            'order_status' => ['required', 'in:PENDING,CONFIRMED,PROCESSING,SHIPPED,DELIVERED,CANCELLED'],
        ]);

        $newStatus = $request->order_status;
        $order->order_status = $newStatus;

        if ($newStatus === 'DELIVERED' && $order->payment_method === 'COD') {
            $order->payment_status = 'PAID';
        }

        $order->save();

        event(new OrderStatusUpdated($order));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diperbarui.',
                'order_status' => $order->order_status,
                'status_label' => $order->status_label,
            ]);
        }

        return back()->with('success', "Status pesanan {$order->invoice_no} diubah ke {$order->status_label}.");
    }

    /**
     * Assign courier to order and mark as SHIPPED.
     */
    public function assignCourier(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $request->validate([
            'courier_id' => ['required', 'string', 'exists:users,id'],
        ]);

        $courier = User::where('id', $request->courier_id)->whereIn('role', ['driver', 'courier'])->firstOrFail();

        $order->courier_id = $courier->id;
        $order->order_status = 'SHIPPED';
        $order->save();

        event(new OrderStatusUpdated($order));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kurir {$courier->name} berhasil ditugaskan.",
            ]);
        }

        return back()->with('success', "Kurir {$courier->name} berhasil ditugaskan untuk pengantaran.");
    }

    /**
     * Products management view.
     */
    public function products(Request $request): View
    {
        $category = $request->query('category', 'ALL');
        $query = Product::query()->orderBy('category')->orderBy('title');

        if ($category !== 'ALL') {
            $query->where('category', $category);
        }

        $products = $query->paginate(20);
        $categories = ['Beras', 'Minyak & Bumbu', 'Telur & Susu', 'Gas & Galon', 'Makanan Ringan', 'Kebersihan'];

        return view('admin.products', compact('products', 'categories', 'category'));
    }

    /**
     * Store new product.
     */
    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'unit' => ['required', 'string', 'max:20'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'image_file' => ['nullable', 'image', 'max:2048'],
        ]);

        $imageUrl = $validated['image_url'] ?? 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80';

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $imageUrl = asset('storage/' . $path);
        }

        Product::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'unit' => $validated['unit'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'total_sold' => 0,
            'image_url' => $imageUrl,
            'is_active' => true,
        ]);

        return redirect()->route('admin.products')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Update existing product.
     */
    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'unit' => ['required', 'string', 'max:20'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'total_sold' => ['required', 'integer', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'image_file' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $validated['image_url'] = asset('storage/' . $path);
        }

        $product->update($validated);

        return redirect()->route('admin.products')->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Quick toggle product active / out of stock status without page reload.
     */
    public function toggleProductStatus(Product $product): JsonResponse
    {
        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json([
            'success' => true,
            'is_active' => $product->is_active,
            'message' => $product->is_active ? 'Produk diaktifkan.' : 'Produk dinonaktifkan.',
        ]);
    }

    /**
     * Multi-courier live tracking & monitoring map view.
     */
    public function courierMonitoring(): View
    {
        $couriers = User::whereIn('role', ['driver', 'courier'])->with(['courierOrders' => function ($q) {
            $q->whereIn('order_status', ['PROCESSING', 'SHIPPED'])->with('customer');
        }])->get();

        $settings = LogisticService::getSettings();
        $storeLat = $settings->store_latitude;
        $storeLng = $settings->store_longitude;
        $storeName = $settings->store_name;

        return view('admin.courier-monitoring', compact('couriers', 'storeLat', 'storeLng', 'storeName'));
    }

    /**
     * Thermal Receipt (58mm / 80mm) print view.
     */
    public function receipt(Order $order): View
    {
        $order->load(['customer', 'courier', 'items.product']);

        $settings = LogisticService::getSettings();
        $storeName = $settings->store_name;
        $storeAddress = $settings->store_address;
        $storePhone = '0812-3456-7890';

        return view('admin.receipt', compact('order', 'storeName', 'storeAddress', 'storePhone'));
    }

    /**
     * Analytics and Monthly KPI Report Dashboard.
     */
    public function analytics(): View
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();

        // 1. KPI Cards
        $monthlyRevenue = Order::where('order_status', '!=', 'CANCELLED')
            ->whereBetween('created_at', [$currentMonth, $endMonth])
            ->sum('total_price');

        $completedOrdersCount = Order::where('order_status', 'DELIVERED')
            ->whereBetween('created_at', [$currentMonth, $endMonth])
            ->count();

        $totalOrdersCount = Order::where('order_status', '!=', 'CANCELLED')
            ->whereBetween('created_at', [$currentMonth, $endMonth])
            ->count();

        $averageOrderValue = $totalOrdersCount > 0 ? ($monthlyRevenue / $totalOrdersCount) : 0;

        // 2. Daily revenue chart data for current month
        $daysInMonth = Carbon::now()->daysInMonth;
        $chartLabels = [];
        $chartData = [];

        for ($day = 1; $day <= min($daysInMonth, Carbon::now()->day); $day++) {
            $date = Carbon::now()->setDay($day)->toDateString();
            $chartLabels[] = "Tgl {$day}";

            $dailyTotal = Order::where('order_status', '!=', 'CANCELLED')
                ->whereDate('created_at', $date)
                ->sum('total_price');

            $chartData[] = (float) $dailyTotal;
        }

        // 3. Top Selling Products Ranking Table (Eloquest Query)
        $topProducts = Product::orderBy('total_sold', 'desc')
            ->take(10)
            ->get();

        return view('admin.analytics', compact(
            'monthlyRevenue',
            'completedOrdersCount',
            'averageOrderValue',
            'chartLabels',
            'chartData',
            'topProducts'
        ));
    }

    /**
     * Export sales report to Excel / CSV format.
     */
    public function exportReport(Request $request): Response
    {
        $orders = Order::with(['customer', 'courier', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'Laporan_Penjualan_Sembako_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $output = "\xEF\xBB\xBF"; // UTF-8 BOM for Microsoft Excel compatibility
        $output .= "No Invoice,Tanggal,Nama Pelanggan,Telepon,Alamat,Metode Bayar,Status Bayar,Status Pesanan,Subtotal (Rp),Ongkir (Rp),Total (Rp),Rincian Barang\n";

        foreach ($orders as $order) {
            $itemsList = $order->items->map(function ($it) {
                return ($it->product?->title ?? 'Item') . ' (' . $it->quantity . 'x)';
            })->implode('; ');

            $cleanAddress = str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $order->delivery_address);
            $cleanItems = str_replace('"', '""', $itemsList);

            $output .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d,%d,%d,\"%s\"\n",
                $order->invoice_no,
                $order->created_at->format('Y-m-d H:i'),
                $order->customer?->name ?? '-',
                $order->customer?->phone ?? '-',
                $cleanAddress,
                $order->payment_method,
                $order->payment_status,
                $order->status_label,
                $order->subtotal,
                $order->shipping_fee,
                $order->total_price,
                $cleanItems
            );
        }

        return response($output, 200, $headers);
    }

    /**
     * Printable PDF / Paper format for monthly report.
     */
    public function printReport(): View
    {
        $currentMonthName = Carbon::now()->locale('id')->isoFormat('MMMM Y');
        $orders = Order::with(['customer', 'courier', 'items.product'])
            ->where('order_status', '!=', 'CANCELLED')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue = $orders->sum('total_price');
        $totalOrders = $orders->count();
        $topProducts = Product::orderBy('total_sold', 'desc')->take(5)->get();

        return view('admin.print-report', compact(
            'orders',
            'totalRevenue',
            'totalOrders',
            'topProducts',
            'currentMonthName'
        ));
    }
}

