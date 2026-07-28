@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Breadcrumb -->
    <nav class="flex text-slate-500 text-xs font-semibold uppercase tracking-wider mb-8" aria-label="Breadcrumb">
        <a href="{{ route('shop.home') }}" class="hover:text-emerald-700">Home</a>
        <span class="mx-2 text-slate-300">/</span>
        <a href="{{ route('shop.index', ['category' => $product->category]) }}" class="hover:text-emerald-700">{{ ucfirst($product->category) }}</a>
        <span class="mx-2 text-slate-300">/</span>
        <span class="text-slate-800">{{ $product->name }}</span>
    </nav>

    <!-- Main Product Block -->
    <div class="bg-white rounded-3xl border border-emerald-100 shadow-sm overflow-hidden p-4 md:p-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-12">
            
            <!-- Left: Product Image -->
            <div class="space-y-4">
                <div class="bg-slate-50 border border-emerald-50 rounded-2xl overflow-hidden aspect-square flex items-center justify-center">
                    <img id="main-product-image" src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" 
                         class="w-full h-full object-cover"
                         onerror="this.src='{{ asset('images/products/placeholder.jpg') }}'">

                </div>
            </div>

            <!-- Right: Product Information -->
            <div class="space-y-6 flex flex-col justify-between">
                <div class="space-y-4">
                    <span class="inline-block bg-emerald-50 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                        {{ ucfirst($product->category) }}
                    </span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 serif-font">{{ $product->name }}</h1>
                    
                    <!-- Pricing Summary -->
                    <div class="py-2 flex items-center gap-3">
                        <span id="display-price" class="text-3xl font-extrabold text-emerald-800">
                            @if($product->variations->isNotEmpty())
                                RM{{ number_format($product->variations->first()->active_price, 2) }}
                            @else
                                RM{{ number_format($product->active_price, 2) }}
                            @endif
                        </span>
                        
                        @if(!$product->variations->isNotEmpty() && $product->discount_price)
                            <span class="text-slate-400 line-through text-lg font-medium">RM{{ number_format($product->base_price, 2) }}</span>
                            <span class="bg-red-50 text-red-700 text-xs font-extrabold px-2 py-0.5 rounded-md">
                                Save RM{{ number_format($product->base_price - $product->discount_price, 2) }}
                            </span>
                        @endif
                    </div>

                    <!-- Description -->
                    <p class="text-slate-600 text-sm leading-relaxed">
                        {{ $product->description }}
                    </p>

                    <!-- Stock indicator -->
                    <div class="flex items-center gap-2 pt-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500" id="stock-dot"></span>
                        <span id="display-stock" class="text-emerald-700 text-sm font-semibold">
                            @if($product->variations->isNotEmpty())
                                {{ $product->variations->first()->stock }} units available
                            @else
                                {{ $product->stock }} units available
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Shopping cart Form -->
                <form action="{{ route('cart.add') }}" method="POST" class="space-y-6 pt-6 border-t border-emerald-50">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    @if($product->variations->isNotEmpty())
                        <!-- Product Variations Dropdown -->
                        <div>
                            <label for="variation-selector" class="block text-sm font-bold text-slate-700 mb-2">Select Option</label>
                            <select name="product_variation_id" id="variation-selector" onchange="updatePriceAndStock()"
                                    class="w-full px-4 py-3 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                                @foreach($product->variations as $idx => $v)
                                    <option value="{{ $v->id }}" 
                                            data-price="{{ $v->active_price }}" 
                                            data-stock="{{ $v->stock }}">
                                        {{ $v->name }}: {{ $v->value }} — RM{{ number_format($v->active_price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Quantity Selector -->
                    <div class="flex items-center gap-4">
                        <div class="w-32">
                            <label for="quantity" class="block text-sm font-bold text-slate-700 mb-2">Quantity</label>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" required
                                   class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        </div>
                        <div class="flex-grow pt-8">
                            <button type="submit" id="submit-btn"
                                    class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                Add to Shopping Cart
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->isNotEmpty())
        <div class="mt-16 space-y-8">
            <h2 class="text-2xl font-bold text-slate-900 serif-font">You May Also Like</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                @foreach($relatedProducts as $related)
                    <div class="bg-white border border-emerald-100 rounded-2xl shadow-xs overflow-hidden hover:shadow-md transition-all flex flex-col group">
                        <div class="relative overflow-hidden bg-slate-100 aspect-square">
                            <img src="{{ asset($related->image_url) }}" alt="{{ $related->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 onerror="this.src='{{ asset('images/products/placeholder.jpg') }}'">

                        </div>
                        <div class="p-6">
                            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">{{ ucfirst($related->category) }}</span>
                            <h3 class="font-bold text-slate-800 text-sm mt-1 group-hover:text-emerald-800 transition-colors">
                                <a href="{{ route('shop.show', $related->id) }}">{{ $related->name }}</a>
                            </h3>
                            <span class="block mt-4 text-slate-950 font-extrabold text-base">RM{{ number_format($related->active_price, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
    function updatePriceAndStock() {
        const selector = document.getElementById('variation-selector');
        if(!selector) return;

        const selectedOption = selector.options[selector.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price'));
        const stock = parseInt(selectedOption.getAttribute('data-stock'));

        // Update price display
        document.getElementById('display-price').innerText = 'RM' + price.toFixed(2);

        // Update stock display & button state
        const stockDisplay = document.getElementById('display-stock');
        const stockDot = document.getElementById('stock-dot');
        const submitBtn = document.getElementById('submit-btn');

        if (stock <= 0) {
            stockDisplay.innerText = 'Out of stock';
            stockDisplay.className = 'text-rose-600 text-sm font-semibold';
            stockDot.className = 'w-2.5 h-2.5 rounded-full bg-rose-500';
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Out of Stock';
            submitBtn.className = 'w-full bg-slate-200 text-slate-500 font-bold py-3 px-6 rounded-lg cursor-not-allowed flex items-center justify-center gap-2';
        } else {
            stockDisplay.innerText = stock + ' units available';
            stockDisplay.className = 'text-emerald-700 text-sm font-semibold';
            stockDot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500';
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Add to Shopping Cart
            `;
            submitBtn.className = 'w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2';
        }
    }

    // Trigger on page load to set correct initial states
    window.addEventListener('DOMContentLoaded', (event) => {
        updatePriceAndStock();
    });
</script>
@endsection
