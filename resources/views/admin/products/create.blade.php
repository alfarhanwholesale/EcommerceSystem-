@extends('layouts.admin')

@section('header_title')
    Create New Product
@endsection

@section('content')
<div class="max-w-3xl space-y-8">

    {{-- 1. PRODUCT CORE DETAILS --}}
    <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs">
        <div class="border-b border-slate-100 pb-4 mb-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Product Details</h3>
                <p class="text-xs text-slate-500 mt-1">Isi nama, harga, kategori, stok, dan gambar produk.</p>
            </div>
            <a href="{{ route('admin.products') }}" class="text-slate-500 hover:text-slate-700 font-bold text-xs transition-colors">
                ← Senarai Produk
            </a>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="create-product-form">
            @csrf

            {{-- Product Name --}}
            <div>
                <label for="name" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Nama Produk</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                       placeholder="cth. Premium Ajwa Dates (Al-Madinah)">
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Penerangan</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                          placeholder="Tulis penerangan terperinci tentang produk...">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Category Dropdown (Fixed) --}}
                <div>
                    <label for="category" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Kategori</label>
                    <select name="category" id="category" required
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ old('category') == $cat->name ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Initial Stock --}}
                <div>
                    <label for="stock" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Stok Asas (Unit)</label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}" min="0" required
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                    <span id="base-stock-notice" class="text-[10px] text-slate-400 font-semibold mt-1 block">Letak 0 jika stok diurus melalui variation sahaja.</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Base Price --}}
                <div>
                    <label for="base_price" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Harga Asas (RM)</label>
                    <input type="number" name="base_price" id="base_price" step="0.01" value="{{ old('base_price') }}" required min="0"
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                           placeholder="0.00">
                </div>

                {{-- Discount Price --}}
                <div>
                    <label for="discount_price" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Harga Diskaun (RM)</label>
                    <input type="number" name="discount_price" id="discount_price" step="0.01" value="{{ old('discount_price') }}" min="0"
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                           placeholder="Pilihan">
                </div>

                {{-- Weight --}}
                <div>
                    <label for="weight" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Berat Produk (kg)</label>
                    <input type="number" name="weight" id="weight" step="0.01" value="{{ old('weight', '0.50') }}" min="0.01" required
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                           placeholder="0.50">
                    <span class="text-[10px] text-slate-400 font-semibold mt-1 block">Dalam kilogram (kg).</span>
                </div>
            </div>

            {{-- Image Upload --}}
            <div>
                <label for="image" class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Gambar Produk (Thumbnail)</label>
                <input type="file" name="image" id="image" accept="image/*"
                       class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>

            {{-- ============================================================ --}}
            {{-- SECTION: PRODUCT VARIATIONS (OPTIONAL)                       --}}
            {{-- ============================================================ --}}
            <div class="border-t border-slate-100 pt-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Variation Produk <span class="text-slate-400 font-normal">(Pilihan)</span></h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Tambah variation seperti saiz, berat, gred. Jika tiada variation, stok asas di atas akan digunakan.</p>
                    </div>
                    <button type="button" id="add-variation-btn"
                            class="flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs px-3 py-2 rounded-lg border border-emerald-200 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Variation
                    </button>
                </div>

                {{-- Variation Rows Container --}}
                <div id="variations-container" class="space-y-3">
                    {{-- Rows inserted by JS --}}
                </div>

                <div id="no-variation-notice" class="text-center py-4 border border-dashed border-slate-200 rounded-xl">
                    <p class="text-xs text-slate-400">Belum ada variation. Klik "Tambah Variation" untuk mula, atau simpan tanpa variation.</p>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-emerald-800 hover:bg-emerald-950 text-white font-bold py-3 px-6 rounded-lg shadow-sm transition-all text-sm uppercase tracking-wide">
                Simpan Produk ke Katalog
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let variationIndex = 0;
    const container = document.getElementById('variations-container');
    const noVariationNotice = document.getElementById('no-variation-notice');
    const addBtn = document.getElementById('add-variation-btn');

    function updateNoticeVisibility() {
        const stockInput = document.getElementById('stock');
        const stockNotice = document.getElementById('base-stock-notice');

        if (container.children.length === 0) {
            noVariationNotice.classList.remove('hidden');
            if (stockInput) {
                stockInput.readOnly = false;
                stockInput.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
            }
            if (stockNotice) {
                stockNotice.textContent = 'Letak 0 jika stok diurus melalui variation sahaja.';
                stockNotice.className = 'text-[10px] text-slate-400 font-semibold mt-1 block';
            }
        } else {
            noVariationNotice.classList.add('hidden');
            if (stockInput) {
                stockInput.value = 0;
                stockInput.readOnly = true;
                stockInput.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
            }
            if (stockNotice) {
                stockNotice.textContent = '🔒 Stok asas dikunci (0) kerana variasi digunakan. Stok diurus pada variasi di bawah.';
                stockNotice.className = 'text-[10px] text-amber-600 font-bold mt-1 block';
            }
        }
    }

    function addVariationRow() {
        const idx = variationIndex++;
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-2 items-end p-3 bg-slate-50 border border-slate-200 rounded-xl variation-row';
        row.dataset.idx = idx;
        row.innerHTML = `
            <div class="col-span-3">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="variations[${idx}][name]" required placeholder="cth. Saiz, Berat"
                       class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>
            <div class="col-span-3">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nilai <span class="text-red-500">*</span></label>
                <input type="text" name="variations[${idx}][value]" required placeholder="cth. 500g, 1kg"
                       class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>
            <div class="col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Berat kg (Pilihan)</label>
                <input type="number" name="variations[${idx}][weight]" step="0.01" min="0" placeholder="Auto"
                       class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>
            <div class="col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga (RM)</label>
                <input type="number" name="variations[${idx}][price]" step="0.01" min="0" placeholder="Warisi"
                       class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>
            <div class="col-span-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Stok <span class="text-red-500">*</span></label>
                <input type="number" name="variations[${idx}][stock]" required min="0" value="0"
                       class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600">
            </div>
            <div class="col-span-1 flex justify-end">
                <button type="button" onclick="removeVariationRow(this)"
                        class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all" title="Buang variation ini">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;
        container.appendChild(row);
        updateNoticeVisibility();
        row.querySelector('input').focus();
    }

    function removeVariationRow(btn) {
        btn.closest('.variation-row').remove();
        updateNoticeVisibility();
    }

    addBtn.addEventListener('click', addVariationRow);
</script>
@endpush
@endsection
