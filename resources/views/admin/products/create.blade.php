@extends('layouts.admin')

@section('header_title')
    Tambah Produk Baharu
@endsection

@section('content')
<div class="max-w-4xl space-y-8 pb-24">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800 font-serif">Pengurusan Produk (Shopee Flow)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Isi maklumat produk, set variasi, dan kemas kini harga & stok pukal.</p>
        </div>
        <a href="{{ route('admin.products') }}" class="text-slate-500 hover:text-slate-700 font-bold text-xs transition-colors flex items-center gap-1">
            ← Kembali ke Katalog
        </a>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form" class="space-y-8">
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
                    <span id="name-char-count" class="text-[11px] text-slate-400 font-medium">0 / 120 aksara</span>
                </div>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="120"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all placeholder:text-slate-400"
                       placeholder="cth: Premium Kurma Ajwa Madinah (Gred A High Quality)">
                <p class="text-[11px] text-slate-400 mt-1">💡 <strong>Panduan SEO:</strong> Masukkan Jenis Produk + Jenama + Spesifikasi Utama untuk memudahkan carian cth. <em>"Kurma Ajwa Alfarhan 500g Gred A"</em>.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kategori -->
                <div>
                    <label for="category" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="category" id="category" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" {{ old('category') == $cat->name ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Gambar Thumbnail -->
                <div>
                    <label for="image" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">
                        Gambar Utama (Cover)
                    </label>
                    <input type="file" name="image" id="image" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-800 hover:file:bg-emerald-100 transition-all">
                </div>
            </div>

            <!-- Deskripsi Produk -->
            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">
                    Deskripsi Produk
                </label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all placeholder:text-slate-400"
                          placeholder="Terangkan kelebihan, khasiat, spesifikasi dan panduan simpanan produk ini...">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <!-- STEP 2 & 3: MAKLUMAT JUALAN & VARIASI (SALES INFO & VARIATION MATRIX)          -->
        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">2</span>
                    <h3 class="text-base font-bold text-slate-800">Maklumat Jualan & Variasi</h3>
                </div>

                <!-- Toggle Suis Variasi -->
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-600">Aktifkan Variasi Produk:</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="variation-toggle" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-700"></div>
                    </label>
                </div>
            </div>

            <!-- PANELS: NO VARIATION vs HAS VARIATION -->

            <!-- Pilihan 1: Standard / Tiada Variasi -->
            <div id="standard-product-fields" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="base_price" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">Harga Asas (RM) <span class="text-red-500">*</span></label>
                    <input type="number" name="base_price" id="base_price" step="0.01" value="{{ old('base_price') }}" min="0" required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                           placeholder="0.00">
                </div>
                <div>
                    <label for="discount_price" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">Harga Diskaun (RM)</label>
                    <input type="number" name="discount_price" id="discount_price" step="0.01" value="{{ old('discount_price') }}" min="0"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                           placeholder="Kosongkan jika tiada diskaun">
                </div>
                <div>
                    <label for="stock" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">Stok Asas (Unit) <span class="text-red-500">*</span></label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', 10) }}" min="0" required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                </div>
            </div>

            <!-- Pilihan 2: Ada Variasi (Hidden by default until toggle ON) -->
            <div id="variation-product-fields" class="hidden space-y-6">

                <div class="flex justify-between items-center bg-emerald-50/60 p-4 rounded-2xl border border-emerald-100">
                    <div>
                        <h4 class="text-xs font-bold text-emerald-950 uppercase tracking-wide">Senarai Variasi Produk</h4>
                        <p class="text-[11px] text-emerald-700 mt-0.5">Masukkan pilihan variasi (cth: Saiz, Berat, Bauan, Pakej).</p>
                    </div>
                    <button type="button" id="add-variation-btn"
                            class="flex items-center gap-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition-all shadow-xs">
                        + Tambah Variasi
                    </button>
                </div>

                <!-- 🚀 BULK EDIT TOOLBAR (Ala Shopee) -->
                <div id="bulk-edit-bar" class="hidden bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            ⚡ Sunting Pukal (Bulk Edit Ala Shopee)
                        </span>
                        <span class="text-[10px] text-slate-400">Isi di bawah dan klik "Apply to All" untuk kemas kini semua baris sekaligus.</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga Pukal (RM)</label>
                            <input type="number" id="bulk-price" step="0.01" min="0" placeholder="cth: 25.00"
                                   class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Stok Pukal (Unit)</label>
                            <input type="number" id="bulk-stock" min="0" placeholder="cth: 50"
                                   class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Berat Pukal (kg)</label>
                            <input type="number" id="bulk-weight" step="0.01" min="0" placeholder="cth: 0.50"
                                   class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                        </div>
                        <div>
                            <button type="button" id="apply-bulk-btn"
                                    class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-3 rounded-lg text-xs transition-all shadow-xs">
                                Apply to All (Kemas Kini Semua)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Matrix Variation Table Rows Container -->
                <div id="variations-container" class="space-y-3">
                    <!-- Rows generated via JS -->
                </div>

                <div id="no-variation-notice" class="text-center py-6 border border-dashed border-slate-200 rounded-2xl">
                    <p class="text-xs text-slate-400">Klik butang <strong>"+ Tambah Variasi"</strong> untuk memasukkan pilihan variasi produk anda.</p>
                </div>
            </div>
        </div>

        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <!-- STEP 4: PENGHANTARAN & BERAT (SHIPPING)                                      -->
        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">3</span>
                <h3 class="text-base font-bold text-slate-800">Penghantaran (Shipping)</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="weight" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">
                        Berat Produk Siap Bungkus (kg) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="weight" id="weight" step="0.01" value="{{ old('weight', '0.50') }}" min="0.01" required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                           placeholder="0.50">
                    <span class="text-[11px] text-slate-400 mt-1 block">Digunakan untuk pengiraan kos kurier secara automatik.</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase">Dimensi Parcel (P x L x T cm)</label>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="number" placeholder="Panjang" class="px-3 py-2.5 border border-slate-200 rounded-xl text-xs">
                        <input type="number" placeholder="Lebar" class="px-3 py-2.5 border border-slate-200 rounded-xl text-xs">
                        <input type="number" placeholder="Tinggi" class="px-3 py-2.5 border border-slate-200 rounded-xl text-xs">
                    </div>
                </div>
            </div>
        </div>

        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <!-- STEP 5: STICKY ACTION FOOTER (FLOATING BAR ALA SHOPEE)                       -->
        <!-- ───────────────────────────────────────────────────────────────────────────── -->
        <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 p-4 z-40 shadow-lg">
            <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
                <a href="{{ route('admin.products') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
                    Batal & Kembali
                </a>
                <div class="flex items-center gap-3">
                    <button type="submit" name="status_action" value="draft"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-5 py-2.5 rounded-xl text-xs transition-all">
                        Simpan sebagai Draf
                    </button>
                    <button type="submit" name="status_action" value="publish"
                            class="bg-emerald-800 hover:bg-emerald-950 text-white font-bold px-6 py-2.5 rounded-xl text-xs uppercase tracking-wide shadow-md hover:shadow-lg transition-all">
                        Simpan & Terus Jual 🚀
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

        // Character count for Product Name
        if (nameInput && charCount) {
            nameInput.addEventListener('input', function () {
                charCount.textContent = `${this.value.length} / 120 aksara`;
            });
        }

        // Variation Toggle & Container references
        const toggle = document.getElementById('variation-toggle');
        const standardFields = document.getElementById('standard-product-fields');
        const variationFields = document.getElementById('variation-product-fields');
        const variationsContainer = document.getElementById('variations-container');
        const noNotice = document.getElementById('no-variation-notice');
        const bulkBar = document.getElementById('bulk-edit-bar');
        const addBtn = document.getElementById('add-variation-btn');
        const applyBulkBtn = document.getElementById('apply-bulk-btn');

        let varIdx = 0;

        function updateUIState() {
            const hasVariations = toggle.checked;

            if (hasVariations) {
                standardFields.classList.add('hidden');
                variationFields.classList.remove('hidden');

                // Disable base inputs so they don't block submit
                document.getElementById('stock').value = 0;
                document.getElementById('stock').readOnly = true;

                // Add 1 initial row if empty
                if (variationsContainer.children.length === 0) {
                    addVariationRow();
                }
            } else {
                standardFields.classList.remove('hidden');
                variationFields.classList.add('hidden');

                // Clear variations
                variationsContainer.innerHTML = '';
                document.getElementById('stock').readOnly = false;
            }

            updateBulkBarState();
        }

        function updateBulkBarState() {
            if (variationsContainer.children.length > 0 && toggle.checked) {
                noNotice.classList.add('hidden');
                bulkBar.classList.remove('hidden');
            } else {
                noNotice.classList.remove('hidden');
                bulkBar.classList.add('hidden');
            }
        }

        function addVariationRow(defaultName = '', defaultValue = '') {
            const idx = varIdx++;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-12 gap-2 items-end p-3 bg-slate-50 border border-slate-200 rounded-2xl variation-row transition-all';
            row.dataset.idx = idx;

            const basePriceVal = document.getElementById('base_price')?.value || '';
            const baseWeightVal = document.getElementById('weight')?.value || '0.50';

            row.innerHTML = `
                <div class="col-span-3">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nama Variasi <span class="text-red-500">*</span></label>
                    <input type="text" name="variations[${idx}][name]" required value="${defaultName}" placeholder="cth: Saiz, Warna"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                </div>
                <div class="col-span-3">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nilai Pilihan <span class="text-red-500">*</span></label>
                    <input type="text" name="variations[${idx}][value]" required value="${defaultValue}" placeholder="cth: Merah, 500g, XL"
                           class="w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Harga (RM)</label>
                    <input type="number" name="variations[${idx}][price]" step="0.01" min="0" value="${basePriceVal}" placeholder="RM 0.00"
                           class="var-price-input w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Stok (Unit) <span class="text-red-500">*</span></label>
                    <input type="number" name="variations[${idx}][stock]" required min="0" value="10"
                           class="var-stock-input w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Berat (kg)</label>
                    <input type="number" name="variations[${idx}][weight]" step="0.01" min="0" value="${baseWeightVal}" placeholder="kg"
                           class="var-weight-input w-full px-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:ring-1 focus:ring-emerald-600">
                </div>
                <div class="col-span-1 flex justify-end">
                    <button type="button" class="remove-var-btn p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all" title="Padam variasi ini">
                        ✕
                    </button>
                </div>
            `;

            row.querySelector('.remove-var-btn').addEventListener('click', function () {
                row.remove();
                if (variationsContainer.children.length === 0) {
                    toggle.checked = false;
                    updateUIState();
                } else {
                    updateBulkBarState();
                }
            });

            variationsContainer.appendChild(row);
            updateBulkBarState();
        }

        // Apply Bulk Edit to all variation rows in 1 click
        applyBulkBtn.addEventListener('click', function () {
            const bulkPrice = document.getElementById('bulk-price').value;
            const bulkStock = document.getElementById('bulk-stock').value;
            const bulkWeight = document.getElementById('bulk-weight').value;

            document.querySelectorAll('.var-price-input').forEach(input => {
                if (bulkPrice !== '') input.value = bulkPrice;
            });
            document.querySelectorAll('.var-stock-input').forEach(input => {
                if (bulkStock !== '') input.value = bulkStock;
            });
            document.querySelectorAll('.var-weight-input').forEach(input => {
                if (bulkWeight !== '') input.value = bulkWeight;
            });
        });

        toggle.addEventListener('change', updateUIState);
        addBtn.addEventListener('click', () => addVariationRow());
    });
</script>
@endpush
@endsection
