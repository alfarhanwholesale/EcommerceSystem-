@extends('layouts.admin')

@section('header_title')
    Dashboard Console
@endsection

@section('content')
<!-- Role Summary Ribbon -->
<div class="bg-gradient-to-r from-emerald-800 to-emerald-950 text-white rounded-3xl p-6 md:p-8 shadow-md mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Assalamualaikum, {{ $user->name }}</h1>
        <p class="text-emerald-200/80 text-sm mt-1">Welcome to the AlfarhanWholesale management console. You have authorization for the role of <strong class="text-white underline">{{ strtoupper($user->role) }}</strong>.</p>
    </div>
    <div class="bg-emerald-700/50 border border-emerald-500/25 px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider">
        Shift Status: Active
    </div>
</div>

<!-- ---------------------------------------------------- -->
<!-- STATS COUNTER: Visible to Full Admin or Outdoor Sales -->
<!-- ---------------------------------------------------- -->
@if(in_array($user->role, ['admin']))
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Total Revenue -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-500 uppercase">Total Sales Revenue</span>
                <h3 class="text-2xl font-extrabold text-emerald-800">RM{{ number_format($totalRevenue, 2) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl">💰</div>
        </div>

        <!-- Card 2: Online Sales -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-500 uppercase">Online Sales</span>
                <h3 class="text-2xl font-extrabold text-slate-800">RM{{ number_format($onlineRevenue, 2) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center text-xl">🌐</div>
        </div>

        <!-- Card 3: Outdoor Sales -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-500 uppercase">Outdoor Event Sales</span>
                <h3 class="text-2xl font-extrabold text-amber-800">RM{{ number_format($outdoorRevenue, 2) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-700 flex items-center justify-center text-xl">🎪</div>
        </div>

        <!-- Card 4: Order Count -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-500 uppercase">Total Transactions</span>
                <h3 class="text-2xl font-extrabold text-slate-800">{{ $orderCount }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-slate-50 text-slate-700 flex items-center justify-center text-xl">📦</div>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    
    <!-- LEFT & CENTER COLS (Main actions based on roles) -->
    <div class="lg:col-span-2 space-y-8">

        <!-- ROLE: OUTDOOR SALES AGENT PANEL (Create offline sales logs) -->
        @if(in_array($user->role, ['admin', 'outdoor_sales']))
            <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>🎪</span> Log New Outdoor Event Sale
                    </h3>
                    <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase">POS Terminal</span>
                </div>

                <form action="{{ route('admin.sales.outdoor') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Search Product Text Input -->
                        <div class="relative">
                            <label for="sales-product-search" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Product Item (Search System)</label>
                            <div class="relative">
                                <input type="text" id="sales-product-search" autocomplete="off" placeholder="Type product name or ID to check..."
                                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all pr-8"
                                       onkeyup="filterProducts()" onfocus="showProductDropdown()">
                                <button type="button" id="clear-product-btn" onclick="clearProductSelection()" class="hidden absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold">✕</button>
                            </div>
                            <input type="hidden" name="product_id" id="sales-product-id" required>

                            <!-- System Verification Status Badge -->
                            <div id="product-status-badge" class="mt-1.5 text-xs font-semibold hidden"></div>

                            <!-- Live Suggestions Dropdown Overlay -->
                            <div id="sales-product-dropdown" class="hidden absolute z-30 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto divide-y divide-slate-100">
                                <!-- Filled by JS -->
                            </div>
                        </div>

                        <!-- Select Variation -->
                        <div id="sales-variation-wrapper" class="hidden">
                            <label for="sales-variation-select" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Product Variation</label>
                            <select name="product_variation_id" id="sales-variation-select"
                                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                                <!-- Filled by JS -->
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Quantity -->
                        <div>
                            <label for="quantity" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Quantity Sold</label>
                            <input type="number" name="quantity" id="quantity" min="1" value="1" required
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        </div>
                        <!-- Customer Name -->
                        <div>
                            <label for="customer_name" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Customer Name</label>
                            <input type="text" name="customer_name" id="customer_name" required placeholder="Walk-in Buyer"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        </div>
                        <!-- Customer Phone -->
                        <div>
                            <label for="customer_phone" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Customer Phone</label>
                            <input type="text" name="customer_phone" id="customer_phone" placeholder="Optional"
                                   class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="delivery_address" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Event location / Notes</label>
                        <input type="text" name="delivery_address" id="delivery_address" placeholder="e.g. Mosque bazaar booth #3"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                    </div>

                    <button type="submit" 
                            class="w-full bg-emerald-800 hover:bg-emerald-950 text-white font-bold py-2.5 px-4 rounded-lg shadow-sm transition-all text-sm uppercase tracking-wide">
                        Record Completed Sale
                    </button>
                </form>
            </div>
        @endif

        <!-- ROLE: ADMIN (Category Management) -->
        @if(in_array($user->role, ['admin']))
            <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>🏷️</span> Manage Categories
                    </h3>
                    <span class="bg-blue-100 text-blue-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase">Store Categories</span>
                </div>

                <div class="space-y-4">
                    <!-- Add Category Form -->
                    <form action="{{ route('admin.categories.store') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="name" required placeholder="New Category Name" class="flex-grow px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        <button type="submit" class="bg-emerald-800 hover:bg-emerald-950 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-all text-sm uppercase tracking-wide">
                            Add Category
                        </button>
                    </form>

                    <!-- List of Categories -->
                    <div class="divide-y divide-slate-100 mt-4 border-t border-slate-100">
                        @foreach($categoriesList as $category)
                            <div class="py-3 flex justify-between items-center gap-4">
                                <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" class="flex-grow flex items-center gap-2">
                                    @csrf
                                    <input type="text" name="name" value="{{ $category->name }}" required class="flex-grow px-2 py-1.5 border border-slate-200 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600">
                                    <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs px-3 py-1.5 rounded font-bold transition-colors">
                                        Update
                                    </button>
                                </form>
                                <form action="{{ route('admin.categories.delete', $category->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded transition-colors" onclick="return confirm('Are you sure you want to delete this category?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        @endforeach
                        
                        @if($categoriesList->isEmpty())
                            <p class="text-center text-sm text-slate-500 py-4">No categories added yet. Add your first category above.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- ROLE: STOREKEEPER PANEL (Fulfill Pending & Paid Orders) -->
        @if(in_array($user->role, ['admin', 'storekeeper']))
            <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>📋</span> Perlu Tindakan
                        @if($pendingOrders->isNotEmpty())
                            <span class="bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingOrders->count() }}</span>
                        @endif
                    </h3>
                    <a href="{{ route('admin.orders') }}" class="text-emerald-700 hover:text-emerald-950 font-bold text-xs">
                        Lihat Semua Pesanan →
                    </a>
                </div>

                @if($pendingOrders->isEmpty())
                    <p class="text-slate-500 text-sm text-center py-6">Tiada pesanan menunggu tindakan.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($pendingOrders as $order)
                            <div class="py-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 text-sm font-medium">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-slate-900">Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <span class="bg-indigo-100 text-indigo-800 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">{{ $order->order_type }}</span>
                                        @if($order->status === 'paid')
                                            <span class="bg-emerald-100 text-emerald-700 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">✓ Dah Bayar</span>
                                        @else
                                            <span class="bg-amber-100 text-amber-700 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">COD</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500">Pembeli: {{ $order->customer_name }} | {{ $order->created_at->diffForHumans() }}</p>
                                    <p class="text-xs text-emerald-800 font-semibold max-w-sm truncate">
                                        Items: 
                                        @foreach($order->items as $item)
                                            {{ $item->product->name }} (x{{ $item->quantity }}){{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </p>
                                </div>

                                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <select name="status" class="px-2 py-1 border border-slate-200 rounded text-xs bg-white">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    <button type="submit" class="bg-emerald-800 hover:bg-emerald-950 text-white text-xs px-2.5 py-1.5 rounded transition-all font-bold">
                                        Kemaskini
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

    </div>

    <!-- RIGHT COLUMN (Notifications & Alerts) -->
    <div class="space-y-8">
        
        <!-- INVENTORY SYSTEM ALERTS -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                <span>⚠️</span> Inventory Alerts
            </h3>
            
            <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                <!-- Low stock products -->
                @foreach($lowStockProducts as $lp)
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs flex justify-between items-center">
                        <div>
                            <span class="font-bold text-amber-900 block">{{ $lp->name }}</span>
                            <span class="text-amber-700">Stock: {{ $lp->stock }} units (Low stock!)</span>
                        </div>
                        <span class="text-lg">⚠️</span>
                    </div>
                @endforeach

                <!-- Low stock variations -->
                @foreach($lowStockVariations as $lv)
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs flex justify-between items-center">
                        <div>
                            <span class="font-bold text-rose-900 block">{{ $lv->product->name }}</span>
                            <span class="text-rose-600">Option: {{ $lv->name }} - {{ $lv->value }} (Stock: {{ $lv->stock }})</span>
                        </div>
                        <span class="text-lg font-bold text-rose-600">!</span>
                    </div>
                @endforeach

                @if($lowStockProducts->isEmpty() && $lowStockVariations->isEmpty())
                    <p class="text-slate-500 text-xs text-center py-4">No low stock items. All inventory levels healthy!</p>
                @endif
            </div>
        </div>

        <!-- RECENT OUTDOOR SALES ACTIVITY -->
        @if(in_array($user->role, ['admin', 'outdoor_sales']))
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xs space-y-4">
                <h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                    <span>🎪</span> Recent Outdoor Log
                </h3>

                <div class="space-y-3 text-xs">
                    @forelse($outdoorOrders as $oo)
                        <div class="p-3 border border-slate-100 rounded-xl flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-800 block">Order #{{ str_pad($oo->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-slate-500">Seller: {{ $oo->creator ? $oo->creator->name : 'N/A' }}</span>
                                <span class="block text-[10px] text-emerald-800 mt-1">Amount: RM{{ number_format($oo->final_amount, 2) }}</span>
                            </div>
                            <span class="text-emerald-700 font-extrabold">Completed</span>
                        </div>
                    @empty
                        <p class="text-slate-500 text-center py-4">No outdoor sales logged recently.</p>
                    @endforelse
                </div>
            </div>
        @endif

    </div>

</div>

<!-- Dropdown variation population and live product search Javascript logic -->
<script>
    // Inject variables
    const productsList = @json($productsDropdown);
    const productVariations = @json($productsDropdown->mapWithKeys(fn($p) => [$p->id => $p->variations]));

    let selectedProductId = null;

    function showProductDropdown() {
        filterProducts();
    }

    function filterProducts() {
        const searchInput = document.getElementById('sales-product-search');
        const dropdown = document.getElementById('sales-product-dropdown');
        if (!searchInput || !dropdown) return;

        const query = searchInput.value.trim().toLowerCase();

        // If typing changed away from selected product name, reset selection
        if (selectedProductId) {
            const currentSelected = productsList.find(p => p.id == selectedProductId);
            if (currentSelected && searchInput.value.trim() !== currentSelected.name) {
                clearProductSelection(false);
            }
        }

        const filtered = productsList.filter(p => 
            p.name.toLowerCase().includes(query) || 
            p.id.toString() === query ||
            (p.category && p.category.toLowerCase().includes(query))
        );

        if (filtered.length === 0) {
            dropdown.innerHTML = `<div class="p-3 text-xs text-slate-400 text-center italic">No product found matching "${escapeHtml(searchInput.value)}"</div>`;
            dropdown.classList.remove('hidden');
            updateVerificationBadge(false, 'Product not found in system.');
            return;
        }

        let html = '';
        filtered.forEach(p => {
            const activePrice = (p.discount_price !== null && p.discount_price > 0 && p.discount_price < p.base_price) 
                ? p.discount_price 
                : p.base_price;
            const stockText = p.stock > 0 ? `${p.stock} units` : 'Out of stock';
            const stockColor = p.stock > 0 ? 'text-emerald-700 font-medium' : 'text-rose-600 font-bold';
            
            html += `
                <div onclick="selectProduct(${p.id})" class="p-2.5 hover:bg-emerald-50 cursor-pointer transition-colors flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800 block text-sm">${escapeHtml(p.name)}</span>
                        <span class="text-[11px] text-slate-500">ID: #${p.id} ${p.category ? '| Category: ' + escapeHtml(p.category) : ''}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-slate-900 block text-xs">RM${parseFloat(activePrice).toFixed(2)}</span>
                        <span class="text-[11px] ${stockColor}">${stockText}</span>
                    </div>
                </div>
            `;
        });

        dropdown.innerHTML = html;
        dropdown.classList.remove('hidden');

        if (!selectedProductId && query.length > 0) {
            const exactMatch = productsList.find(p => p.name.toLowerCase() === query || p.id.toString() === query);
            if (exactMatch) {
                selectProduct(exactMatch.id);
            } else {
                updateVerificationBadge(false, `${filtered.length} matching products found in system. Please select one.`);
            }
        } else if (!selectedProductId && query.length === 0) {
            updateVerificationBadge(false, '');
        }
    }

    function selectProduct(id) {
        const prod = productsList.find(p => p.id == id);
        if (!prod) return;

        selectedProductId = prod.id;
        document.getElementById('sales-product-id').value = prod.id;
        document.getElementById('sales-product-search').value = prod.name;
        document.getElementById('sales-product-dropdown').classList.add('hidden');
        document.getElementById('clear-product-btn').classList.remove('hidden');

        const activePrice = (prod.discount_price !== null && prod.discount_price > 0 && prod.discount_price < prod.base_price) 
            ? prod.discount_price 
            : prod.base_price;
            
        updateVerificationBadge(true, `Verified system product: ${prod.name} (Stock: ${prod.stock} | RM${parseFloat(activePrice).toFixed(2)})`);

        updateSalesVariations();
    }

    function clearProductSelection(clearInput = true) {
        selectedProductId = null;
        document.getElementById('sales-product-id').value = '';
        if (clearInput) {
            document.getElementById('sales-product-search').value = '';
            document.getElementById('clear-product-btn').classList.add('hidden');
            document.getElementById('sales-product-dropdown').classList.add('hidden');
            updateVerificationBadge(false, '');
        }
        updateSalesVariations();
    }

    function updateVerificationBadge(isVerified, message) {
        const badge = document.getElementById('product-status-badge');
        if (!badge) return;
        if (!message) {
            badge.classList.add('hidden');
            badge.innerHTML = '';
            return;
        }
        badge.classList.remove('hidden');
        if (isVerified) {
            badge.className = 'mt-1.5 text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-md p-2 flex items-center gap-1.5';
            badge.innerHTML = `<span>✓</span> <span>${escapeHtml(message)}</span>`;
        } else {
            badge.className = 'mt-1.5 text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-200 rounded-md p-2 flex items-center gap-1.5';
            badge.innerHTML = `<span>⚠️</span> <span>${escapeHtml(message)}</span>`;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('sales-product-dropdown');
        const searchInput = document.getElementById('sales-product-search');
        if (dropdown && searchInput && !dropdown.contains(e.target) && e.target !== searchInput) {
            dropdown.classList.add('hidden');
        }
    });

    // Outdoor sales variation listener
    function updateSalesVariations() {
        const varWrapper = document.getElementById('sales-variation-wrapper');
        const varSelect = document.getElementById('sales-variation-select');
        if (!varWrapper || !varSelect) return;

        const prodId = selectedProductId || document.getElementById('sales-product-id').value;
        if (!prodId || !productVariations[prodId] || productVariations[prodId].length === 0) {
            varWrapper.classList.add('hidden');
            varSelect.removeAttribute('required');
            varSelect.innerHTML = '';
            return;
        }

        varWrapper.classList.remove('hidden');
        varSelect.setAttribute('required', 'required');
        
        let html = '<option value="">Select variation...</option>';
        productVariations[prodId].forEach(v => {
            html += `<option value="${v.id}">${v.name}: ${v.value} (RM${parseFloat(v.price || 0).toFixed(2)}) [Stock: ${v.stock}]</option>`;
        });
        varSelect.innerHTML = html;
    }
</script>
@endsection
