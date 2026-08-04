<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    // Check role access
    private function checkAccess($roles)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, (array)$roles)) {
            abort(403, 'Unauthorized access.');
        }
    }

    // Main Administrative Dashboard redirecting or rendering based on role
    public function dashboard()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Stats for Admin Overview
        // Exclude unpaid online orders (order_type=online, status=pending, paid_at=null)
        // These are abandoned payment attempts — user never completed payment at gateway
        $unpaidOnlineFilter = function ($q) {
            $q->where(function ($inner) {
                // Exclude online orders that are pending with no paid_at (abandoned)
                $inner->where('order_type', 'online')
                      ->where('status', 'pending')
                      ->whereNull('paid_at');
            });
        };

        $totalRevenue = Order::where('status', '!=', 'cancelled')
                             ->whereNot($unpaidOnlineFilter)
                             ->sum('final_amount');
        $onlineRevenue = Order::where('order_type', 'online')
                              ->where('status', '!=', 'cancelled')
                              ->whereNot($unpaidOnlineFilter)
                              ->sum('final_amount');
        $outdoorRevenue = Order::where('order_type', 'outdoor')
                               ->where('status', '!=', 'cancelled')
                               ->sum('final_amount');
        // Only count real orders (exclude abandoned online payment attempts)
        $orderCount = Order::whereNot($unpaidOnlineFilter)->count();
        // Pending orders for dashboard = COD pending + paid online orders that need fulfillment
        $pendingOrders = Order::with('items.product')
                              ->whereNot($unpaidOnlineFilter)
                              ->whereIn('status', ['pending', 'paid'])
                              ->orderBy('created_at', 'desc')
                              ->take(10)
                              ->get();

        // Stock alerts
        $lowStockProducts = Product::where('stock', '<', 10)->whereDoesntHave('variations')->get();
        $lowStockVariations = ProductVariation::with('product')->where('stock', '<', 10)->get();

        // Outdoor sales list
        $outdoorOrders = Order::where('order_type', 'outdoor')->orderBy('created_at', 'desc')->take(5)->get();

        // Feed products for dropdowns
        $productsDropdown = Product::with('variations')->get();

        // Categories for management
        $categoriesList = \App\Models\Category::all();

        return view('admin.dashboard', compact(
            'user',
            'totalRevenue',
            'onlineRevenue',
            'outdoorRevenue',
            'orderCount',
            'pendingOrders',
            'lowStockProducts',
            'lowStockVariations',
            'outdoorOrders',
            'productsDropdown',
            'categoriesList'
        ));
    }

    // ----------------------------------------------------
    // ADMIN ONLY: PRODUCT MANAGEMENT (CRUD)
    // ----------------------------------------------------

    public function productList(Request $request)
    {
        $this->checkAccess('admin');
        $query = Product::with('variations');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('variations', function ($vq) use ($search) {
                      $vq->where('name', 'like', "%{$search}%")
                         ->orWhere('value', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        return view('admin.products.index', compact('products'));
    }

    public function productCreate()
    {
        $this->checkAccess('admin');
        $categories = \App\Models\Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function productStore(Request $request)
    {
        $this->checkAccess('admin');
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:base_price',
            'weight' => 'nullable|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'variations' => 'nullable|array',
            'variations.*.name' => 'required_with:variations|string|max:255',
            'variations.*.value' => 'required_with:variations|string|max:255',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',
        ]);

        $imagePath = 'images/products/placeholder.jpg';
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadProductImage($request->file('image'));
        }

        $categorySlug = \Illuminate\Support\Str::slug($request->category);
        if (!\App\Models\Category::where('slug', $categorySlug)->exists()) {
            \App\Models\Category::create([
                'name' => $request->category,
                'slug' => $categorySlug
            ]);
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $categorySlug,
            'base_price' => $request->base_price,
            'discount_price' => $request->discount_price,
            'weight' => $request->weight ?? 0.50,
            'stock' => $request->stock,
            'image_url' => $imagePath,
        ]);

        // Process inline variations if submitted
        if ($request->filled('variations')) {
            $hasWeightCol = Schema::hasColumn('product_variations', 'weight');
            foreach ($request->variations as $varData) {
                if (!empty($varData['name']) && !empty($varData['value'])) {
                    $payload = [
                        'product_id' => $product->id,
                        'name'       => $varData['name'],
                        'value'      => $varData['value'],
                        'price'      => $varData['price'] ?: null,
                        'stock'      => (int) ($varData['stock'] ?? 0),
                    ];
                    if ($hasWeightCol) {
                        $payload['weight'] = isset($varData['weight']) && $varData['weight'] !== '' ? (float)$varData['weight'] : null;
                    }
                    ProductVariation::create($payload);
                }
            }
        }

        return redirect()->route('admin.products.edit', $product->id)->with('success', 'Produk berjaya dicipta! Anda boleh urus variation di sini.');
    }

    public function productEdit($id)
    {
        $this->checkAccess('admin');
        $product = Product::with('variations')->findOrFail($id);
        $categories = \App\Models\Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function productUpdate(Request $request, $id)
    {
        $this->checkAccess('admin');
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:base_price',
            'weight' => 'nullable|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $imagePath = $product->image_url;
        if ($request->hasFile('image')) {
            // Padam gambar lama (kalau bukan placeholder)
            if ($imagePath !== 'images/products/placeholder.jpg') {
                $oldFile = public_path($imagePath);
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
            }
            $imagePath = $this->uploadProductImage($request->file('image'));
        }

        $categorySlug = \Illuminate\Support\Str::slug($request->category);
        if (!\App\Models\Category::where('slug', $categorySlug)->exists()) {
            \App\Models\Category::create([
                'name' => $request->category,
                'slug' => $categorySlug
            ]);
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $categorySlug,
            'base_price' => $request->base_price,
            'discount_price' => $request->discount_price,
            'weight' => $request->weight ?? 0.50,
            'stock' => $request->stock,
            'image_url' => $imagePath,
        ]);

        return redirect()->route('admin.products')->with('success', 'Product updated successfully.');
    }

    /**
     * Convert mana-mana format gambar (png, webp, gif, dll) kepada .jpg
     * dan simpan ke public/images/products/.
     * Return: path relatif dari public (contoh: "images/products/1720000000.jpg")
     */
    private function uploadProductImage($file): string
    {
        $destDir = public_path('images/products');
        if (! File::exists($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        $fileName = time() . '_' . uniqid() . '.jpg';
        $destPath = $destDir . DIRECTORY_SEPARATOR . $fileName;

        // Baca gambar → encode ke JPEG kualiti 85 → simpan
        Image::read($file->getRealPath())
            ->toJpeg(85)
            ->save($destPath);

        return 'images/products/' . $fileName;
    }

    public function productDestroy($id)
    {
        $this->checkAccess('admin');
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Product deleted successfully.');
    }

    // Add variation to product
    public function variationStore(Request $request, $productId)
    {
        $this->checkAccess('admin');
        $product = Product::findOrFail($productId);

        $request->validate([
            'variation_name' => 'required|string|max:255',
            'variation_value' => 'required|string|max:255',
            'variation_price' => 'nullable|numeric|min:0',
            'variation_weight' => 'nullable|numeric|min:0',
            'variation_stock' => 'required|integer|min:0',
        ]);

        $payload = [
            'product_id' => $product->id,
            'name' => $request->variation_name,
            'value' => $request->variation_value,
            'price' => $request->variation_price,
            'stock' => $request->variation_stock,
        ];

        if (Schema::hasColumn('product_variations', 'weight')) {
            $payload['weight'] = $request->filled('variation_weight') ? $request->variation_weight : null;
        }

        ProductVariation::create($payload);

        return back()->with('success', 'Variation added successfully.');
    }

    // Delete variation
    public function variationDestroy($id)
    {
        $this->checkAccess('admin');
        $variation = ProductVariation::findOrFail($id);
        $variation->delete();

        return back()->with('success', 'Variation deleted successfully.');
    }

    // ----------------------------------------------------
    // ADMIN ONLY: CATEGORY MANAGEMENT (CRUD)
    // RESTORED: Category functions are still needed by dashboard
    // ----------------------------------------------------
    
    public function categoryStore(Request $request)
    {
        $this->checkAccess('admin');
        $request->validate(['name' => 'required|string|max:255']);
        \App\Models\Category::create(['name' => $request->name]);
        return back()->with('success', 'Category added successfully.');
    }

    public function categoryUpdate(Request $request, $id)
    {
        $this->checkAccess('admin');
        $request->validate(['name' => 'required|string|max:255']);
        $category = \App\Models\Category::findOrFail($id);
        $category->update(['name' => $request->name]);
        return back()->with('success', 'Category updated successfully.');
    }

    public function categoryDestroy($id)
    {
        $this->checkAccess('admin');
        $category = \App\Models\Category::findOrFail($id);
        $category->delete();
        return back()->with('success', 'Category deleted successfully.');
    }

    // ----------------------------------------------------
    // PURCHASER: INVENTORY MONITOR & RESTOCKING
    // NOTE: Restock Product is the main inventory function
    // ----------------------------------------------------

    public function inventoryList(Request $request)
    {
        $this->checkAccess(['admin', 'purchaser', 'storekeeper']);
        $query = Product::with('variations');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhereHas('variations', function ($vq) use ($search) {
                      $vq->where('name', 'like', "%{$search}%")
                         ->orWhere('value', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();
        return view('admin.inventory.index', compact('products'));
    }

    public function restock(Request $request)
    {
        $this->checkAccess(['admin', 'purchaser']);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($request->product_variation_id) {
            $variation = ProductVariation::findOrFail($request->product_variation_id);
            $variation->stock += $request->quantity;
            $variation->save();
            $name = $variation->product->name . ' (' . $variation->value . ')';
        } else {
            $product = Product::findOrFail($request->product_id);
            $product->stock += $request->quantity;
            $product->save();
            $name = $product->name;
        }

        return back()->with('success', 'Restocked ' . $request->quantity . ' units of ' . $name . ' successfully.');
    }

    // ----------------------------------------------------
    // STOREKEEPER: FULFILLMENT MANAGEMENT
    // ----------------------------------------------------

    public function orderList()
    {
        $this->checkAccess(['admin', 'storekeeper']);
        // Exclude abandoned online payment orders (online, pending, paid_at null)
        // These are orders where user initiated online payment but never completed it
        $orders = Order::with('items.product')
                       ->where(function ($q) {
                           $q->where('order_type', '!=', 'online')
                             ->orWhere('status', '!=', 'pending')
                             ->orWhereNotNull('paid_at');
                       })
                       ->orderBy('created_at', 'desc')
                       ->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $this->checkAccess(['admin', 'storekeeper']);

        $request->validate([
            'status' => 'required|in:pending,paid,processing,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        
        // If order status is changing from cancelled back to active or vice-versa, handle stock corrections?
        // For simplicity, we just change the status
        $order->status = $request->status;
        $order->save();

        return back()->with('success', 'Order status updated to ' . ucfirst($request->status) . '.');
    }

    // Manually update tracking number and courier name (without EasyParcel)
    public function updateShipping(Request $request, $id)
    {
        $this->checkAccess(['admin', 'storekeeper']);

        $request->validate([
            'shipping_courier' => 'nullable|string|max:100',
            'tracking_code'    => 'nullable|string|max:100',
        ]);

        $order = Order::findOrFail($id);

        $courier = $request->input('shipping_courier');
        $tracking = $request->input('tracking_code');

        $order->shipping_courier = !empty(trim((string)$courier)) ? trim((string)$courier) : null;
        $order->tracking_code    = !empty(trim((string)$tracking)) ? trim((string)$tracking) : null;
        $order->save();

        return back()->with('success', 'Maklumat penghantaran untuk Order #' . str_pad($id, 6, '0', STR_PAD_LEFT) . ' telah dikemaskini.');
    }

    // Book shipment with EasyParcel
    public function bookCourier($id)
    {
        $this->checkAccess(['admin', 'storekeeper']);

        $order = Order::with('items.product')->findOrFail($id);

        if (empty($order->shipping_courier)) {
            return back()->with('error', 'Sila pilih kurier untuk pesanan ini terlebih dahulu.');
        }

        if (!empty($order->tracking_code)) {
            return back()->with('error', 'Pesanan ini sudah mempunyai kod penjejakan: ' . $order->tracking_code);
        }

        $easyParcel = new \App\Services\EasyParcelService();
        $result = $easyParcel->createShipment($order);

        if (isset($result['status']) && $result['status'] === 'Success') {
            $order->tracking_code = $result['tracking_code'];
            $order->easyparcel_order_id = $result['easyparcel_order_id'];
            $order->status = 'processing';
            $order->save();

            return back()->with('success', 'Tempahan kurier berjaya! Kod Penjejakan: ' . $result['tracking_code']);
        } else {
            $msg = $result['message'] ?? 'Ralat EasyParcel API yang tidak diketahui.';
            return back()->with('error', 'Gagal menempah kurier: ' . $msg);
        }
    }

    // ----------------------------------------------------
    // OUTDOOR SALES AGENT: LOG SALES
    // ----------------------------------------------------

    public function logOutdoorSale(Request $request)
    {
        $this->checkAccess(['admin', 'outdoor_sales']);

        $request->validate([
            'product_id'           => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'quantity'             => 'required|integer|min:1',
            'customer_name'        => 'required|string|max:255',
            'customer_phone'       => 'nullable|string|max:50',
            'delivery_address'     => 'nullable|string|max:500',
        ]);

        $product = Product::with('variations')->findOrFail($request->product_id);
        $variation = null;

        if ($request->filled('product_variation_id')) {
            $variation = ProductVariation::where('product_id', $product->id)->findOrFail($request->product_variation_id);
            if ($variation->stock < $request->quantity) {
                return back()->with('error', "Stok tidak mencukupi untuk variasi '{$variation->value}'. Stok semasa: {$variation->stock}");
            }
            $unitPrice = $variation->active_price;
        } else {
            if ($product->stock < $request->quantity) {
                return back()->with('error', "Stok tidak mencukupi untuk produk '{$product->name}'. Stok semasa: {$product->stock}");
            }
            $unitPrice = $product->active_price;
        }

        $totalAmount = $unitPrice * $request->quantity;

        // Create Order record
        $order = Order::create([
            'user_id'          => null,
            'created_by'        => Auth::id(),
            'order_type'        => 'outdoor',
            'customer_name'     => $request->customer_name,
            'customer_phone'    => $request->customer_phone,
            'delivery_address'  => $request->delivery_address ?: 'Outdoor Event Sale',
            'total_amount'      => $totalAmount,
            'discount_amount'   => 0,
            'final_amount'      => $totalAmount,
            'status'            => 'completed',
            'paid_at'           => now(),
        ]);

        // Create OrderItem record
        OrderItem::create([
            'order_id'             => $order->id,
            'product_id'           => $product->id,
            'product_variation_id' => $variation ? $variation->id : null,
            'quantity'             => $request->quantity,
            'price'                => $unitPrice,
        ]);

        // Deduct inventory stock
        if ($variation) {
            $variation->decrement('stock', $request->quantity);
        } else {
            $product->decrement('stock', $request->quantity);
        }

        return back()->with('success', "Jualan outdoor untuk '{$product->name}' (x{$request->quantity}) berjaya direkodkan!");
    }

    // ----------------------------------------------------
    // REPORTS GENERATION
    // ----------------------------------------------------

    public function reports(Request $request)
    {
        $this->checkAccess(['admin', 'outdoor_sales']);

        // Sales by type
        $onlineRevenue = Order::where('order_type', 'online')->where('status', '!=', 'cancelled')->sum('final_amount');
        $outdoorRevenue = Order::where('order_type', 'outdoor')->where('status', '!=', 'cancelled')->sum('final_amount');
        $totalRevenue = $onlineRevenue + $outdoorRevenue;

        // Daily sales data (last 30 days)
        $dailySales = Order::select(
            DB::raw('date(created_at) as sales_date'),
            DB::raw('sum(final_amount) as daily_revenue'),
            DB::raw('count(*) as order_count')
        )
        ->where('status', '!=', 'cancelled')
        ->groupBy('sales_date')
        ->orderBy('sales_date', 'desc')
        ->take(15)
        ->get();

        // Product performance (best sellers)
        $bestSellers = OrderItem::select(
            'product_id',
            DB::raw('sum(quantity) as units_sold'),
            DB::raw('sum(quantity * price) as revenue')
        )
        ->with('product')
        ->groupBy('product_id')
        ->orderBy('units_sold', 'desc')
        ->take(10)
        ->get();

        // Outdoor sales breakdown by staff agent
        $staffSales = Order::select(
            'created_by',
            DB::raw('sum(final_amount) as total_sales'),
            DB::raw('count(*) as order_count')
        )
        ->where('order_type', 'outdoor')
        ->where('status', '!=', 'cancelled')
        ->with('creator')
        ->groupBy('created_by')
        ->get();

        // Low stock overview
        $lowStockProducts = Product::where('stock', '<', 10)->whereDoesntHave('variations')->get();
        $lowStockVariations = ProductVariation::with('product')->where('stock', '<', 10)->get();

        return view('admin.reports.index', compact(
            'onlineRevenue',
            'outdoorRevenue',
            'totalRevenue',
            'dailySales',
            'bestSellers',
            'staffSales',
            'lowStockProducts',
            'lowStockVariations'
        ));
    }
}
