@extends('layouts.admin')

@section('header_title')
    Edit Product: {{ $product->name }}
@endsection

@section('content')
<div class="space-y-8 max-w-4xl">
    
    <!-- 1. EDIT PRODUCT FORM -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs">
        <div class="border-b border-slate-100 pb-4 mb-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Edit Product details</h3>
                <p class="text-xs text-slate-500 mt-1">Modify core details, base prices, categories, and image.</p>
            </div>
            <a href="{{ route('admin.products') }}" class="text-slate-500 hover:text-slate-700 font-bold text-xs transition-colors">
                ← Back to Products List
            </a>
        </div>

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Product Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Product Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Category Dropdown (Fixed) -->
                <div>
                    <label for="category" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Kategori</label>
                    @php
                        $catName = old('category', $product->category);
                        $matchingCat = $categories->where('slug', $product->category)->first();
                        if ($matchingCat && !old('category')) {
                            $catName = $matchingCat->name;
                        }
                    @endphp
                    <select name="category" id="category" required
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ $catName == $cat->name ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock -->
                <div>
                    <label for="stock" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Base Stock (Units)</label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', $product->variations->isNotEmpty() ? 0 : $product->stock) }}" min="0" required
                           {{ $product->variations->isNotEmpty() ? 'readonly' : '' }}
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all {{ $product->variations->isNotEmpty() ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : '' }}">
                    @if($product->variations->isNotEmpty())
                        <span class="text-[10px] text-amber-700 font-bold mt-1 block">🔒 Stok asas dikunci (0). Stok diuruskan mengikut variasi di bawah (Jumlah: {{ $product->variations->sum('stock') }} unit).</span>
                    @else
                        <span class="text-[10px] text-slate-400 font-semibold mt-1 block">Tukar kepada 0 jika ingin menambah variasi.</span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Base Price -->
                <div>
                    <label for="base_price" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Base Price (RM)</label>
                    <input type="number" name="base_price" id="base_price" step="0.01" value="{{ old('base_price', $product->base_price) }}" required min="0"
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                </div>

                <!-- Discount Price -->
                <div>
                    <label for="discount_price" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Discount Price (RM)</label>
                    <input type="number" name="discount_price" id="discount_price" step="0.01" value="{{ old('discount_price', $product->discount_price) }}" min="0"
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                </div>

                <!-- Weight -->
                <div>
                    <label for="weight" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Berat Produk (kg)</label>
                    <input type="number" name="weight" id="weight" step="0.01" value="{{ old('weight', $product->weight ?? '0.50') }}" min="0.01" required
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                           placeholder="0.50">
                    <span class="text-[10px] text-slate-400 font-semibold mt-1 block">Dalam kilogram (kg).</span>
                </div>
            </div>

            <!-- Image preview and upload -->
            <div class="flex flex-col sm:flex-row gap-6 items-start sm:items-center">
                <div class="w-20 h-20 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                    <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover"
                         onerror="this.src='{{ asset('images/products/placeholder.jpg') }}'">

                </div>
                <div class="flex-grow">
                    <label for="image" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Update Thumbnail Image</label>
                    <input type="file" name="image" id="image" accept="image/*"
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>
            </div>

            <button type="submit" 
                    class="w-full bg-emerald-800 hover:bg-emerald-950 text-white font-bold py-3 px-6 rounded-lg shadow-sm transition-all text-sm uppercase tracking-wide">
                Update Core Product Details
            </button>
        </form>
    </div>

    <!-- 2. VARIATIONS MANAGEMENT PORTAL -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Product Variations</h3>
            <p class="text-xs text-slate-500 mt-1">Manage variations like size packaging or weights, price overrides, and variation-specific stock levels.</p>
        </div>

        <!-- Existing Variations Table -->
        <div class="overflow-x-auto border border-slate-100 rounded-2xl">
            <table class="w-full text-left border-collapse text-xs font-semibold text-slate-600">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[9px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="p-4 pl-6">Variation Name</th>
                        <th class="p-4">Value</th>
                        <th class="p-4 text-center">Weight (kg)</th>
                        <th class="p-4 text-right">Price Override</th>
                        <th class="p-4 text-center">Stock</th>
                        <th class="p-4 text-right pr-6">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($product->variations as $var)
                        <tr>
                            <td class="p-4 pl-6 font-bold text-slate-900">{{ $var->name }}</td>
                            <td class="p-4">{{ $var->value }}</td>
                            <td class="p-4 text-center font-semibold text-slate-800">
                                {{ number_format($var->active_weight, 2) }} kg
                                @if($var->weight === null)
                                    <span class="text-[10px] text-slate-400 block font-normal">(Auto/Base)</span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-semibold text-emerald-800">
                                @if($var->price !== null)
                                    RM{{ number_format($var->price, 2) }}
                                @else
                                    <span class="text-slate-400 italic">Inherited (RM{{ number_format($product->active_price, 2) }})</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full font-bold {{ $var->stock <= 0 ? 'bg-red-100 text-red-800' : ($var->stock < 10 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $var->stock }} units
                                </span>
                            </td>
                            <td class="p-4 text-right pr-6">
                                <form action="{{ route('admin.variations.delete', $var->id) }}" method="POST" onsubmit="return confirm('Delete this variation?');">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-bold transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400 italic">No variations added yet for this product.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Variation Form -->
        <div class="border-t border-slate-100 pt-6">
            <h4 class="text-sm font-bold text-slate-800 mb-4">Add Variation</h4>
            
            <form action="{{ route('admin.variations.store', $product->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                @csrf
                <!-- Variation Name -->
                <div class="md:col-span-2">
                    <label for="variation_name" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Name (e.g. Size)</label>
                    <input type="text" name="variation_name" id="variation_name" required placeholder="Size, Weight..."
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                </div>

                <!-- Variation Value -->
                <div class="md:col-span-2">
                    <label for="variation_value" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Value (e.g. 1kg)</label>
                    <input type="text" name="variation_value" id="variation_value" required placeholder="500g, 1kg..."
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                </div>

                <!-- Weight Override -->
                <div class="md:col-span-2">
                    <label for="variation_weight" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Weight kg (Optional)</label>
                    <input type="number" name="variation_weight" id="variation_weight" step="0.01" min="0" placeholder="Auto if empty"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                </div>

                <!-- Price Override -->
                <div class="md:col-span-2">
                    <label for="variation_price" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Price override (Optional)</label>
                    <input type="number" name="variation_price" id="variation_price" step="0.01" min="0" placeholder="Inherited if empty"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                </div>

                <!-- Stock Quantity -->
                <div class="md:col-span-2">
                    <label for="variation_stock" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Stock Quantity</label>
                    <input type="number" name="variation_stock" id="variation_stock" required min="0" value="0"
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                </div>

                <!-- Submit Button -->
                <div class="md:col-span-2">
                    <button type="submit" 
                            class="w-full bg-emerald-800 hover:bg-emerald-950 text-white font-bold py-2 px-3 rounded-lg text-xs uppercase tracking-wider transition-colors shrink-0 h-[38px] flex items-center justify-center">
                        Add Option
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
