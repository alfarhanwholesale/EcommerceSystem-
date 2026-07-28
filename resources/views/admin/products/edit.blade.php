@extends('layouts.admin')

@section('header_title')
    Sunting Produk: {{ $product->name }}
@endsection

@section('content')
<div class="max-w-4xl space-y-8 pb-24">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800 font-serif">Sunting Produk</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kemas kini maklumat produk, harga, variasi dan status stok.</p>
        </div>
        <a href="{{ route('admin.products') }}" class="text-slate-500 hover:text-slate-700 font-bold text-xs transition-colors flex items-center gap-1">
            ← Kembali ke Katalog
        </a>
    </div>

    <!-- MAIN FORM -->
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="edit-product-form" class="space-y-8">
        @csrf

        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <!-- STEP 1: MAKLUMAT ASAS (BASIC INFORMATION)                                   -->
        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">1</span>
                <h3 class="text-base font-bold text-slate-800">Maklumat Asas</h3>
            </div>

            <!-- Nama Produk -->
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <span id="name-char-count" class="text-[11px] text-slate-400 font-medium">{{ strlen($product->name) }} / 120 aksara</span>
                </div>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required maxlength="120"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                <p class="text-[11px] text-slate-400 mt-1">💡 <strong>Panduan SEO:</strong> Gunakan format: Jenis Produk + Jenama + Spesifikasi Utama.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kategori -->
                <div>
                    <label for="category" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">Kategori <span class="text-red-500">*</span></label>
                    @php
                        $catName = old('category', $product->category);
                    @endphp
                    <select name="category" id="category" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ strtolower($catName) == strtolower($cat->name) ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Gambar Cover -->
                <div>
                    <label for="image" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">Kemaskini Gambar Cover</label>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                            <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="w-full h-full object-cover"
                                 onerror="this.src='{{ asset('images/products/placeholder.jpg') }}'">
                        </div>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-800 hover:file:bg-emerald-100 transition-all">
                    </div>
                </div>
            </div>

            <!-- Deskripsi Produk -->
            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">Deskripsi Produk</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>

        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <!-- STEP 2 & 3: MAKLUMAT JUALAN & VARIASI (SALES INFO & VARIATIONS)              -->
        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">2</span>
                <h3 class="text-base font-bold text-slate-800">Harga & Stok Asas</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Base Price -->
                <div>
                    <label for="base_price" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">Harga Asas (RM) <span class="text-red-500">*</span></label>
                    <input type="number" name="base_price" id="base_price" step="0.01" value="{{ old('base_price', $product->base_price) }}" required min="0"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                </div>

                <!-- Discount Price -->
                <div>
                    <label for="discount_price" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">Harga Diskaun (RM)</label>
                    <input type="number" name="discount_price" id="discount_price" step="0.01" value="{{ old('discount_price', $product->discount_price) }}" min="0"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                           placeholder="Kosongkan jika tiada diskaun">
                </div>

                <!-- Base Stock -->
                <div>
                    <label for="stock" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">Stok Asas (Unit) <span class="text-red-500">*</span></label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', $product->variations->isNotEmpty() ? 0 : $product->stock) }}" min="0" required
                           {{ $product->variations->isNotEmpty() ? 'readonly' : '' }}
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all {{ $product->variations->isNotEmpty() ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : '' }}">
                    @if($product->variations->isNotEmpty())
                        <span class="text-[10px] text-amber-700 font-bold mt-1 block">🔒 Stok asas dikunci (0). Stok diuruskan melalui variasi di bawah (Jumlah: {{ $product->variations->sum('stock') }} unit).</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <!-- STEP 3: SENARAI VARIASI (VARIATION MATRIX & QUICK ADD)                       -->
        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">3</span>
                    <h3 class="text-base font-bold text-slate-800">Senarai Variasi Produk</h3>
                </div>
                <span class="text-xs text-slate-500 font-semibold">{{ $product->variations->count() }} Variasi Didaftarkan</span>
            </div>

            <!-- Table Variasi Sedia Ada -->
            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                <table class="w-full text-left border-collapse text-xs font-semibold text-slate-600">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="p-4 pl-6">Nama Variasi</th>
                            <th class="p-4">Nilai Pilihan</th>
                            <th class="p-4 text-right">Harga (RM)</th>
                            <th class="p-4 text-center">Stok (Unit)</th>
                            <th class="p-4 text-right pr-6">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($product->variations as $var)
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="p-4 pl-6 font-bold text-slate-900">{{ $var->name }}</td>
                                <td class="p-4"><span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded-lg border font-bold">{{ $var->value }}</span></td>
                                <td class="p-4 text-right font-bold text-emerald-800">
                                    @if($var->price !== null)
                                        RM{{ number_format($var->price, 2) }}
                                    @else
                                        <span class="text-slate-400 italic">RM{{ number_format($product->active_price, 2) }}</span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full font-bold {{ $var->stock <= 0 ? 'bg-red-100 text-red-800' : ($var->stock < 10 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                        {{ $var->stock }} unit
                                    </span>
                                </td>
                                <td class="p-4 text-right pr-6">
                                    <form action="{{ route('admin.variations.delete', $var->id) }}" method="POST" onsubmit="return confirm('Adakah anda pasti mahu memadamkan variasi ini?');">
                                        @csrf
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold transition-colors text-xs">
                                            Padam
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400 italic">Belum ada variasi ditambah untuk produk ini. Gunakan borang di bawah untuk menambah variasi baharu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Form Tambah Variasi Baharu -->
            <div class="bg-slate-50/80 border border-slate-200 rounded-2xl p-5 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">+ Tambah Variasi Baharu</h4>

                <form action="{{ route('admin.variations.store', $product->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    @csrf
                    <div class="md:col-span-3">
                        <label for="variation_name" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Variasi</label>
                        <input type="text" name="variation_name" id="variation_name" required placeholder="cth: Saiz, Berat"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                    </div>

                    <div class="md:col-span-3">
                        <label for="variation_value" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nilai Pilihan</label>
                        <input type="text" name="variation_value" id="variation_value" required placeholder="cth: 500g, 1kg"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                    </div>

                    <div class="md:col-span-3">
                        <label for="variation_price" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga (RM)</label>
                        <input type="number" name="variation_price" id="variation_price" step="0.01" min="0" placeholder="Warisi"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                    </div>

                    <div class="md:col-span-2">
                        <label for="variation_stock" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Stok (Unit)</label>
                        <input type="number" name="variation_stock" id="variation_stock" required min="0" value="10"
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 bg-white focus:ring-1 focus:ring-emerald-600">
                    </div>

                    <div class="md:col-span-1">
                        <button type="submit" 
                                class="w-full bg-emerald-800 hover:bg-emerald-950 text-white font-bold py-2 px-3 rounded-xl text-xs uppercase tracking-wider transition-colors shrink-0 h-[38px] flex items-center justify-center shadow-xs">
                            +
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <!-- STEP 4: STICKY ACTION FOOTER                                                  -->
        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 p-4 z-40 shadow-lg">
            <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
                <a href="{{ route('admin.products') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
                    Batal & Kembali
                </a>
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="bg-emerald-800 hover:bg-emerald-950 text-white font-bold px-6 py-2.5 rounded-xl text-xs uppercase tracking-wide shadow-md hover:shadow-lg transition-all">
                        Kemas Kini Produk 🚀
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('name');
        const charCount = document.getElementById('name-char-count');

        if (nameInput && charCount) {
            nameInput.addEventListener('input', function () {
                charCount.textContent = `${this.value.length} / 120 aksara`;
            });
        }
    });
</script>
@endpush
@endsection
