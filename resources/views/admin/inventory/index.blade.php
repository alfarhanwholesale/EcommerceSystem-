@extends('layouts.admin')

@section('header_title')
    Stock Inventory Control
@endsection

@section('content')
<div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-100 pb-4 gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800 font-serif">Product & Variation Stock Status</h3>
            <p class="text-xs text-slate-500 mt-1">Review active quantities. Red and yellow flags indicate low stock. Use the quick-adder forms to replenish.</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <!-- Search Form -->
            <form action="{{ route('admin.inventory') }}" method="GET" class="flex items-center gap-2 flex-1 md:w-72">
                <div class="relative w-full">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari stok produk / variasi..." 
                           class="w-full pl-9 pr-8 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    @if(request('search'))
                        <a href="{{ route('admin.inventory') }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 font-bold text-xs">✕</a>
                    @endif
                </div>
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-3.5 rounded-xl text-xs transition-colors shrink-0">
                    Cari
                </button>
            </form>
            <span class="bg-blue-50 border border-blue-200 text-blue-800 text-[10px] font-bold px-3 py-1 rounded-full uppercase shrink-0">Purchaser Panel</span>
        </div>
    </div>

    <!-- Inventory list table -->
    <div class="overflow-x-auto border border-slate-100 rounded-2xl">
        <table class="w-full text-left border-collapse text-sm font-medium text-slate-700">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                    <th class="p-4 pl-6">Product Details</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Option / Variation</th>
                    <th class="p-4 text-center">Current Stock</th>
                    <th class="p-4 text-center">Status Flag</th>
                    <th class="p-4 text-right pr-6">Quick Replenish (Add Units)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    @if($product->variations->isNotEmpty())
                        <!-- Product has variations: list each variation as a row -->
                        @foreach($product->variations as $var)
                            <tr class="hover:bg-slate-50/20 transition-colors">
                                <!-- Product details -->
                                <td class="p-4 pl-6 flex items-center gap-3">
                                    <div class="w-10 h-10 rounded bg-slate-50 border overflow-hidden shrink-0">
                                        <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover"
                                             onerror="this.src='{{ asset('images/products/placeholder.jpg') }}'">

                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 text-sm">{{ $product->name }}</span>
                                        <span class="text-[10px] text-slate-400 block">Category: {{ $product->category }}</span>
                                    </div>
                                </td>
                                
                                <!-- Category -->
                                <td class="p-4 text-xs">{{ ucfirst($product->category) }}</td>

                                <!-- Variation details -->
                                <td class="p-4 font-bold text-slate-800 text-xs">
                                    {{ $var->name }}: <span class="bg-slate-100 px-2 py-0.5 border rounded-md text-slate-600 font-semibold">{{ $var->value }}</span>
                                </td>

                                <!-- Current stock -->
                                <td class="p-4 text-center font-bold text-sm">
                                    {{ $var->stock }}
                                </td>

                                <!-- Stock status status badge -->
                                <td class="p-4 text-center">
                                    @if($var->stock <= 0)
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">Out of Stock</span>
                                    @elseif($var->stock < 10)
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">Low Stock</span>
                                    @else
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">Healthy</span>
                                    @endif
                                </td>

                                <!-- Quick Replenish form -->
                                <td class="p-4 text-right pr-6">
                                    <form action="{{ route('admin.inventory.restock') }}" method="POST" class="flex justify-end items-center gap-1.5">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="product_variation_id" value="{{ $var->id }}">
                                        <input type="number" name="quantity" min="1" placeholder="Qty" required 
                                               class="w-16 px-2 py-1 border border-slate-200 rounded-lg text-xs text-center focus:outline-none focus:ring-1 focus:ring-emerald-600">
                                        <button type="submit" class="bg-emerald-800 hover:bg-emerald-950 text-white font-bold px-3 py-1 rounded-lg text-xs uppercase shadow-xs">
                                            Restock
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <!-- Product has NO variations: display base product row -->
                        <tr class="hover:bg-slate-50/20 transition-colors">
                            <!-- Product details -->
                            <td class="p-4 pl-6 flex items-center gap-3">
                                <div class="w-10 h-10 rounded bg-slate-50 border overflow-hidden shrink-0">
                                    <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover"
                                         onerror="this.src='{{ asset('images/products/placeholder.jpg') }}'">

                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 text-sm">{{ $product->name }}</span>
                                    <span class="text-[10px] text-slate-400 block">Category: {{ $product->category }}</span>
                                </div>
                            </td>
                            
                            <!-- Category -->
                            <td class="p-4 text-xs">{{ ucfirst($product->category) }}</td>

                            <!-- Variation details -->
                            <td class="p-4 font-bold text-slate-400 text-xs italic">
                                Base (No options)
                            </td>

                            <!-- Current stock -->
                            <td class="p-4 text-center font-bold text-sm">
                                {{ $product->stock }}
                            </td>

                            <!-- Stock status status badge -->
                            <td class="p-4 text-center">
                                @if($product->stock <= 0)
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">Out of Stock</span>
                                @elseif($product->stock < 10)
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">Low Stock</span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">Healthy</span>
                                @endif
                            </td>

                            <!-- Quick Replenish form -->
                            <td class="p-4 text-right pr-6">
                                <form action="{{ route('admin.inventory.restock') }}" method="POST" class="flex justify-end items-center gap-1.5">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="number" name="quantity" min="1" placeholder="Qty" required 
                                           class="w-16 px-2 py-1 border border-slate-200 rounded-lg text-xs text-center focus:outline-none focus:ring-1 focus:ring-emerald-600">
                                    <button type="submit" class="bg-emerald-800 hover:bg-emerald-950 text-white font-bold px-3 py-1 rounded-lg text-xs uppercase shadow-xs">
                                        Restock
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500 font-medium">No items in the catalog yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection
