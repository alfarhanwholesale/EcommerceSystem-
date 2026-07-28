@extends('layouts.admin')

@section('header_title')
    Product Management
@endsection

@section('content')
<div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800">All Products</h3>
            <p class="text-xs text-slate-500 mt-1">Add, edit, delete, or manage variations and active discounts on the storefront catalog.</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <!-- Search Form -->
            <form action="{{ route('admin.products') }}" method="GET" class="flex items-center gap-2 flex-1 sm:w-64">
                <div class="relative w-full">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari produk / variasi..." 
                           class="w-full pl-9 pr-8 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    @if(request('search'))
                        <a href="{{ route('admin.products') }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 font-bold text-xs">✕</a>
                    @endif
                </div>
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-3 rounded-xl text-xs transition-colors">
                    Cari
                </button>
            </form>
            <a href="{{ route('admin.products.create') }}" 
               class="bg-emerald-800 hover:bg-emerald-950 text-white font-bold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wide transition-all shadow-xs flex items-center gap-1.5 shrink-0">
                <span>+</span> Product
            </a>
        </div>
    </div>

    <!-- Products Table -->
    <div class="overflow-x-auto border border-slate-100 rounded-2xl">
        <table class="w-full text-left border-collapse text-sm font-medium text-slate-700">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                    <th class="p-4 pl-6">Product details</th>
                    <th class="p-4">Category</th>
                    <th class="p-4 text-right">Base Price</th>
                    <th class="p-4 text-right">Discount Price</th>
                    <th class="p-4 text-center">Stock</th>
                    <th class="p-4">Variations</th>
                    <th class="p-4 text-right pr-6">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50/40 transition-colors">
                        <!-- Name & Image -->
                        <td class="p-4 pl-6 flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                                <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover"
                                     onerror="this.src='{{ asset('images/products/placeholder.jpg') }}'">

                            </div>
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $product->name }}</span>
                                <span class="text-[10px] text-slate-400 block line-clamp-1 max-w-[200px]">{{ $product->description }}</span>
                            </div>
                        </td>

                        <!-- Category -->
                        <td class="p-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-100">
                                {{ $product->category }}
                            </span>
                        </td>

                        <!-- Base Price -->
                        <td class="p-4 text-right font-semibold text-slate-800">
                            RM{{ number_format($product->base_price, 2) }}
                        </td>

                        <!-- Discount Price -->
                        <td class="p-4 text-right">
                            @if($product->discount_price)
                                <span class="text-rose-600 font-bold">RM{{ number_format($product->discount_price, 2) }}</span>
                            @else
                                <span class="text-slate-400 text-xs italic">No discount</span>
                            @endif
                        </td>

                        <!-- Stock levels -->
                        <td class="p-4 text-center">
                            @if($product->variations->isNotEmpty())
                                <span class="text-slate-500 text-xs font-semibold">
                                    {{ $product->variations->sum('stock') }} total
                                </span>
                            @else
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $product->stock <= 0 ? 'bg-red-100 text-red-800' : ($product->stock < 10 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800') }}">
                                    {{ $product->stock }} units
                                </span>
                            @endif
                        </td>

                        <!-- Variations lists -->
                        <td class="p-4 max-w-[220px]">
                            @if($product->variations->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($product->variations as $var)
                                        <span class="bg-slate-100 text-slate-600 text-[9px] font-bold px-1.5 py-0.5 rounded border border-slate-200">
                                            {{ $var->value }} ({{ $var->stock }})
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400 text-xs italic">No variations</span>
                            @endif
                        </td>

                        <!-- Action Buttons -->
                        <td class="p-4 text-right pr-6">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.products.edit', $product->id) }}" 
                                   class="bg-emerald-50 text-emerald-800 hover:bg-emerald-700 hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs" 
                                   title="Edit Product and Variations">
                                    Edit
                                </a>
                                <form action="{{ route('admin.products.delete', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product? All variations and references will be removed.');">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-red-50 text-red-700 hover:bg-red-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs" 
                                            title="Delete Product">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500 font-medium">No products in database. Create one above!</td>
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
