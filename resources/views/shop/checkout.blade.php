@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-extrabold text-slate-900 mb-4 serif-font">Checkout</h1>

    @guest
    {{-- Guest notice banner --}}
    <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
        <span class="text-xl shrink-0 mt-0.5">👤</span>
        <div class="text-sm">
            <p class="font-bold text-amber-900">Anda membeli sebagai Tetamu (Guest)</p>
            <p class="text-amber-700 mt-0.5">Tiada akaun diperlukan. Atau <a href="{{ route('login') }}" class="underline font-bold hover:text-amber-900">log masuk</a> / <a href="{{ route('register') }}" class="underline font-bold hover:text-amber-900">daftar</a> untuk nikmati kelebihan ahli (kupon, sejarah pesanan).</p>
        </div>
    </div>
    @endguest

    <form action="{{ route('checkout.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        @csrf

        <!-- Shipping & Payment Form (Left Column) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Shipping Info -->
            <div class="bg-white border border-emerald-100 rounded-2xl shadow-xs p-6 md:p-8 space-y-6">
                <h3 class="text-lg font-bold text-emerald-950 border-b border-emerald-50 pb-2">Delivery Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="customer_name" class="block text-sm font-semibold text-slate-700">Full Name</label>
                        <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" required
                               class="w-full mt-1.5 px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="customer_phone" class="block text-sm font-semibold text-slate-700">Phone Number</label>
                        <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}" required
                               class="w-full mt-1.5 px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                               placeholder="e.g. 01X XXXXXXXX">
                    </div>
                </div>

                @guest
                {{-- Guest email field --}}
                <div>
                    <label for="customer_email" class="block text-sm font-semibold text-slate-700">Email Address <span class="text-xs text-slate-400 font-normal">(untuk penerimaan pesanan)</span></label>
                    <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email') }}" required
                           class="w-full mt-1.5 px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                           placeholder="contoh@email.com">
                </div>
                @endguest

                <div>
                    <label for="street_address" class="block text-sm font-semibold text-slate-700">Street Address</label>
                    <input type="text" name="street_address" id="street_address" value="{{ old('street_address') }}" required
                           class="w-full mt-1.5 px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                           placeholder="No, Jalan, Taman/Seksyen">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="postcode" class="block text-sm font-semibold text-slate-700">Postcode</label>
                        <input type="text" name="postcode" id="postcode" value="{{ old('postcode') }}" required
                               class="w-full mt-1.5 px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                               placeholder="e.g. 47100" maxlength="5">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-semibold text-slate-700">City</label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}" required
                               class="w-full mt-1.5 px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all"
                               placeholder="e.g. Puchong">
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-semibold text-slate-700">State</label>
                        <select name="state" id="state" required
                                class="w-full mt-1.5 px-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all">
                            <option value="">Pilih Negeri</option>
                            <option value="Johor">Johor</option>
                            <option value="Kedah">Kedah</option>
                            <option value="Kelantan">Kelantan</option>
                            <option value="Melaka">Melaka</option>
                            <option value="Negeri Sembilan">Negeri Sembilan</option>
                            <option value="Pahang">Pahang</option>
                            <option value="Perak">Perak</option>
                            <option value="Perlis">Perlis</option>
                            <option value="Pulau Pinang">Pulau Pinang</option>
                            <option value="Selangor">Selangor</option>
                            <option value="Terengganu">Terengganu</option>
                            <option value="Kuala Lumpur">Kuala Lumpur</option>
                            <option value="Putra Jaya">Putra Jaya</option>
                            <option value="Sarawak">Sarawak</option>
                            <option value="Sabah">Sabah</option>
                            <option value="Labuan">Labuan</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ======================================================= --}}
            {{-- SHIPPING METHOD — Self Pickup (always shown) + Courier   --}}
            {{-- ======================================================= --}}
            <div class="bg-white border border-emerald-100 rounded-2xl shadow-xs p-6 md:p-8 space-y-4">
                <h3 class="text-lg font-bold text-emerald-950 border-b border-emerald-50 pb-2">Kaedah Penghantaran</h3>

                {{-- Hidden field to pass shipping method --}}
                <input type="hidden" name="shipping_method" id="shipping_method" value="delivery">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Option A: Courier Delivery --}}
                    <label id="shipping-method-delivery-label"
                           class="border-2 border-emerald-500 bg-emerald-50 rounded-xl p-4 flex items-start gap-3 cursor-pointer hover:bg-emerald-50/80 transition-all">
                        <input type="radio" name="shipping_method_radio" id="shipping-method-delivery" value="delivery" checked
                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 mt-0.5">
                        <div>
                            <span class="block font-bold text-sm text-slate-800">🚚 Penghantaran Kurier</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Hantar ke alamat anda. Kadar dikira berdasarkan poskod.</span>
                        </div>
                    </label>

                    {{-- Option B: Self Pickup --}}
                    <label id="shipping-method-pickup-label"
                           class="border-2 border-slate-200 rounded-xl p-4 flex items-start gap-3 cursor-pointer hover:bg-slate-50 transition-all">
                        <input type="radio" name="shipping_method_radio" id="shipping-method-pickup" value="self_pickup"
                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 mt-0.5">
                        <div>
                            <span class="block font-bold text-sm text-slate-800">🏪 Ambil Sendiri (Self Pickup)</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Ambil di kedai kami. Percuma, tiada kos penghantaran.</span>
                            <span class="inline-block mt-1.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">RM0.00</span>
                        </div>
                    </label>
                </div>

                {{-- Self Pickup Address Info Box (hidden by default) --}}
                <div id="self-pickup-info" class="hidden">
                    <div class="mt-3 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3">
                        <div class="shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-bold text-emerald-900">📍 Alamat Pengambilan</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $pickupAddress['name'] }}</p>
                            <p class="text-sm text-slate-600">{{ $pickupAddress['address'] }}</p>
                            <p class="text-sm text-slate-600">{{ $pickupAddress['postcode'] }} {{ $pickupAddress['city'] }}, {{ $pickupAddress['state'] }}</p>
                            <p class="text-sm text-slate-600">📞 {{ $pickupAddress['phone'] }}</p>
                            <div class="mt-2 pt-2 border-t border-emerald-200">
                                <p class="text-[11px] text-emerald-700 font-semibold">⏰ Sila hubungi kami terlebih dahulu untuk sahkan waktu pengambilan sebelum datang.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Courier Rate Selection (shown for delivery mode) --}}
            <div id="courier-card" class="bg-white border border-emerald-100 rounded-2xl shadow-xs p-6 md:p-8 space-y-4">
                <div class="flex items-center justify-between border-b border-emerald-50 pb-2">
                    <h3 class="text-lg font-bold text-emerald-950">🚚 Pilihan Kurier</h3>
                    <span id="courier-live-badge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 uppercase tracking-wide">● Live</span>
                    <span id="courier-estimate-badge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 uppercase tracking-wide">⚠ Taksiran</span>
                </div>
                <p class="text-xs text-slate-500" id="courier-hint">Sila lengkapkan poskod (5 digit) dan negeri — kadar kurier akan dikira secara automatik.</p>

                <input type="hidden" name="shipping_courier" id="shipping_courier">
                <input type="hidden" name="shipping_service" id="shipping_service">
                <input type="hidden" name="shipping_cost" id="shipping_cost" value="0.00">

                <div id="courier-loading" class="hidden text-center py-6">
                    <svg class="animate-spin h-8 w-8 text-emerald-700 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-xs text-slate-500 mt-2 font-medium">Sedang mengira kadar kurier terbaik...</p>
                </div>

                <div id="courier-list" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Dynamic couriers loaded via JS --}}
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white border border-emerald-100 rounded-2xl shadow-xs p-6 md:p-8 space-y-6">
                <h3 class="text-lg font-bold text-emerald-950 border-b border-emerald-50 pb-2">Payment Method</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- COD -->
                    <label id="label-cod" class="border-2 border-emerald-200 bg-emerald-50/10 rounded-xl p-4 flex items-center gap-3 cursor-pointer hover:bg-emerald-50/30 transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="payment_method" value="cod" checked 
                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="block font-bold text-sm text-slate-800">Cash on Delivery (COD)</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Bayar tunai semasa terima barang</span>
                        </div>
                    </label>

                    <!-- Online Banking / eWallet via ToyyibPay -->
                    <label id="label-online" class="border-2 border-slate-200 rounded-xl p-4 flex items-center gap-3 cursor-pointer hover:bg-slate-50 transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="payment_method" value="online"
                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="block font-bold text-sm text-slate-800">Online Banking / eWallet</span>
                            <span class="inline-block mt-1.5 bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">Dikuasakan ToyyibPay</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Order Items & Price Summary (Right Column) -->
        <div class="space-y-6">
            <div class="bg-white border border-emerald-100 rounded-2xl shadow-xs p-6 space-y-6">
                <h3 class="text-lg font-bold text-emerald-950 border-b border-emerald-50 pb-2">Your Order</h3>

                <!-- Cart Items List Summary -->
                <div class="divide-y divide-slate-100 max-h-60 overflow-y-auto pr-2">
                    @foreach($cartItems as $item)
                        <div class="py-3 flex justify-between items-center gap-4 text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="bg-slate-100 text-slate-700 font-bold px-1.5 py-0.5 rounded-sm">
                                    {{ $item->quantity }}x
                                </span>
                                <div>
                                    <span class="font-bold text-slate-800 block">{{ $item->product->name }}</span>
                                    @if($item->product_variation_id && $item->variation)
                                        <span class="text-[10px] text-slate-500 font-medium">Option: {{ $item->variation->value }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="font-bold text-slate-800">RM{{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Price breakdown -->
                <div class="border-t border-slate-100 pt-4 space-y-3 text-sm font-medium">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span class="text-slate-800 font-bold">RM{{ number_format($subtotal, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-600">
                        <span>Jumlah Berat (Total Weight)</span>
                        <span class="text-slate-800 font-bold" id="checkout-total-weight">{{ number_format($totalWeight, 2) }} kg</span>
                    </div>

                    @if($couponCode)
                        <div class="flex justify-between text-emerald-800 bg-emerald-50 px-2.5 py-1.5 rounded-lg text-xs">
                            <span class="font-semibold">Applied Coupon ({{ $couponCode }})</span>
                            <span class="font-bold">-RM{{ number_format($discount, 2) }}</span>
                        </div>
                    @endif

                    <!-- Shipping Row -->
                    <div id="shipping-fee-row" class="flex justify-between text-slate-600 hidden">
                        <span>Penghantaran (<span id="shipping-courier-name">-</span>)</span>
                        <span id="shipping-fee-amount" class="text-slate-800 font-bold">RM0.00</span>
                    </div>

                    <!-- ToyyibPay Processing Fee (RM1) -->
                    <div id="toyyibpay-fee-row" style="display:none" class="justify-between text-blue-700 bg-blue-50 px-2.5 py-1.5 rounded-lg text-xs">
                        <span class="font-semibold">Caj Pemprosesan ToyyibPay</span>
                        <span class="font-bold">+ RM1.00</span>
                    </div>

                    <div class="border-t border-slate-100 pt-4 flex justify-between text-base font-extrabold text-slate-900">
                        <span>Grand Total</span>
                        <span class="text-emerald-800 text-lg" id="grand-total-display">RM{{ number_format($total, 2) }}</span>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" id="checkout-submit-btn"
                            class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-3.5 px-6 rounded-lg shadow-md hover:shadow-lg transition-all text-center">
                        Teruskan Pembayaran (RM{{ number_format($total, 2) }})
                    </button>
                    <input type="hidden" name="toyyibpay_fee" id="toyyibpay_fee_input" value="0">
                </div>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const postcodeField    = document.getElementById('postcode');
        const stateField       = document.getElementById('state');
        const courierCard      = document.getElementById('courier-card');
        const courierList      = document.getElementById('courier-list');
        const courierLoading   = document.getElementById('courier-loading');
        const courierHint      = document.getElementById('courier-hint');
        const liveBadge        = document.getElementById('courier-live-badge');
        const estimateBadge    = document.getElementById('courier-estimate-badge');

        const shippingCourierInput    = document.getElementById('shipping_courier');
        const shippingServiceInput    = document.getElementById('shipping_service');
        const shippingCostInput       = document.getElementById('shipping_cost');
        const shippingMethodInput     = document.getElementById('shipping_method');
        const shippingFeeRow          = document.getElementById('shipping-fee-row');
        const shippingCourierNameSpan = document.getElementById('shipping-courier-name');
        const shippingFeeAmountSpan   = document.getElementById('shipping-fee-amount');
        const grandTotalTextSpan      = document.getElementById('grand-total-display');
        const checkoutSubmitBtn       = document.getElementById('checkout-submit-btn');
        const toyyibpayFeeRow         = document.getElementById('toyyibpay-fee-row');
        const toyyibpayFeeInput       = document.getElementById('toyyibpay_fee_input');

        const radioDelivery  = document.getElementById('shipping-method-delivery');
        const radioPickup    = document.getElementById('shipping-method-pickup');
        const labelDelivery  = document.getElementById('shipping-method-delivery-label');
        const labelPickup    = document.getElementById('shipping-method-pickup-label');
        const selfPickupInfo = document.getElementById('self-pickup-info');

        const subtotal    = {{ $subtotal }};
        const discount    = {{ $discount }};
        const totalWeight = {{ $totalWeight }};
        const TOYYIBPAY_FEE = 1.00;

        let fetchTimeout  = null;
        let lastFetched   = null;
        let currentShippingFee = 0;

        // ── Update grand total display ─────────────────────────────────────
        function updateTotals(shippingFee) {
            currentShippingFee = shippingFee;
            const isOnline = document.querySelector('input[name="payment_method"]:checked')?.value === 'online';
            const processingFee = isOnline ? TOYYIBPAY_FEE : 0;
            const grandTotal = Math.max(0, subtotal - discount + shippingFee + processingFee);
            if (grandTotalTextSpan) grandTotalTextSpan.textContent = 'RM' + grandTotal.toFixed(2);
            if (checkoutSubmitBtn)  checkoutSubmitBtn.textContent  = 'Teruskan Pembayaran (RM' + grandTotal.toFixed(2) + ')';
        }

        // ── Toggle ToyyibPay fee on payment method change ──────────────────
        function updatePaymentMethodFee() {
            const checkedPayment = document.querySelector('input[name="payment_method"]:checked');
            const isOnline = checkedPayment && checkedPayment.value === 'online';
            if (toyyibpayFeeRow) {
                toyyibpayFeeRow.style.display = isOnline ? 'flex' : 'none';
            }
            if (toyyibpayFeeInput) toyyibpayFeeInput.value = isOnline ? '1' : '0';
            updateTotals(currentShippingFee);
        }

        document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
            radio.addEventListener('change', updatePaymentMethodFee);
        });

        // Run on load
        updatePaymentMethodFee();

        // ── Render courier cards from API response ─────────────────────────
        function renderCouriers(rates, isLive) {
            courierList.innerHTML = '';

            // Toggle live vs estimate badge
            if (liveBadge)      liveBadge.classList.toggle('hidden', !isLive);
            if (estimateBadge)  estimateBadge.classList.toggle('hidden', isLive);

            if (!rates || rates.length === 0) {
                courierList.innerHTML = '<p class="text-xs text-orange-600 col-span-2 text-center font-medium py-4">Tiada kurier tersedia untuk poskod/negeri ini. Sila semak semula maklumat alamat.</p>';
                return;
            }

            rates.forEach(function(rate, i) {
                const card = document.createElement('label');
                card.className = 'border-2 border-slate-200 rounded-xl p-4 flex items-center justify-between cursor-pointer hover:bg-emerald-50/40 transition-all bg-white gap-3';

                const logoHtml = rate.logo
                    ? '<img src="' + rate.logo + '" alt="' + rate.courier_name + '" class="h-8 w-auto object-contain rounded" onerror="this.style.display=\'none\'" />'
                    : '<div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-xs">' + rate.courier_name.charAt(0) + '</div>';

                card.innerHTML = [
                    '<div class="flex items-center gap-3 flex-1 min-w-0">',
                        '<input type="radio" name="selected_courier"',
                        ' id="courier_option_' + i + '"',
                        ' value="' + rate.service_id + '"',
                        ' data-courier="' + rate.courier_name.replace(/"/g, '&quot;') + '"',
                        ' data-service="' + rate.service_name.replace(/"/g, '&quot;') + '"',
                        ' data-price="' + rate.price + '"',
                        ' class="h-4 w-4 shrink-0 text-emerald-600 focus:ring-emerald-500">',
                        logoHtml,
                        '<div class="min-w-0">',
                            '<span class="block font-bold text-sm text-slate-800 truncate">' + rate.courier_name + '</span>',
                            '<span class="block text-[10px] text-slate-500 mt-0.5">' + rate.service_name + ' &bull; ' + rate.delivery + '</span>',
                        '</div>',
                    '</div>',
                    '<span class="font-extrabold text-sm text-emerald-700 shrink-0 ml-2">RM' + parseFloat(rate.price).toFixed(2) + '</span>'
                ].join('');

                // Selection handler
                card.querySelector('input').addEventListener('change', function () {
                    document.querySelectorAll('#courier-list label').forEach(function(l) {
                        l.classList.remove('border-emerald-500', 'bg-emerald-50');
                        l.classList.add('border-slate-200', 'bg-white');
                    });
                    card.classList.remove('border-slate-200', 'bg-white');
                    card.classList.add('border-emerald-500', 'bg-emerald-50');

                    const price = parseFloat(this.dataset.price);
                    shippingCourierInput.value         = this.dataset.courier;
                    shippingServiceInput.value         = this.dataset.service;
                    shippingCostInput.value            = price.toFixed(2);
                    shippingCourierNameSpan.textContent = this.dataset.courier;
                    shippingFeeAmountSpan.textContent   = 'RM' + price.toFixed(2);
                    shippingFeeRow.classList.remove('hidden');
                    updateTotals(price);
                });

                courierList.appendChild(card);
            });

            // Auto-select cheapest
            const firstRadio = courierList.querySelector('input[type=radio]');
            if (firstRadio) firstRadio.click();
        }

        // ── Fetch live courier rates ───────────────────────────────────────
        function fetchRates() {
            if (shippingMethodInput.value === 'self_pickup') return;

            const postcode = postcodeField.value.trim();
            const state    = stateField.value.trim();
            const cacheKey = postcode + '|' + state;

            if (postcode.length !== 5 || !state) {
                if (courierHint) courierHint.textContent = 'Sila lengkapkan poskod (5 digit) dan negeri — kadar kurier akan dikira secara automatik.';
                return;
            }

            // Don't re-fetch same postcode+state
            if (lastFetched === cacheKey) return;
            lastFetched = cacheKey;

            if (courierHint) courierHint.textContent = '';

            // Show loading, hide old list
            courierLoading.classList.remove('hidden');
            courierList.classList.add('hidden');
            courierList.innerHTML = '';
            if (liveBadge)      liveBadge.classList.add('hidden');
            if (estimateBadge)  estimateBadge.classList.add('hidden');

            // Reset selections
            shippingCourierInput.value = '';
            shippingServiceInput.value = '';
            shippingCostInput.value    = '0.00';
            shippingFeeRow.classList.add('hidden');
            updateTotals(0);

            const url = '/checkout/shipping-rates?postcode=' + encodeURIComponent(postcode)
                      + '&state=' + encodeURIComponent(state)
                      + '&weight=' + encodeURIComponent(totalWeight);

            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    courierLoading.classList.add('hidden');
                    courierList.classList.remove('hidden');

                    if (data.success) {
                        renderCouriers(data.rates, data.is_live === true);
                    } else {
                        courierList.innerHTML = '<p class="text-xs text-red-600 col-span-2 text-center font-medium py-4">Ralat: ' + (data.message || 'Gagal mendapatkan kadar kurier.') + '</p>';
                    }
                })
                .catch(function(err) {
                    console.error('Courier fetch error:', err);
                    courierLoading.classList.add('hidden');
                    courierList.classList.remove('hidden');
                    courierList.innerHTML = '<p class="text-xs text-red-600 col-span-2 text-center font-medium py-4">Gagal menghubungi pelayan. Sila muat semula halaman dan cuba lagi.</p>';
                });
        }

        // ── Debounced postcode listener ────────────────────────────────────
        postcodeField.addEventListener('input', function() {
            clearTimeout(fetchTimeout);
            lastFetched = null; // reset cache on new input
            if (this.value.trim().length === 5 && stateField.value.trim()) {
                fetchTimeout = setTimeout(fetchRates, 500);
            }
        });

        stateField.addEventListener('change', function() {
            clearTimeout(fetchTimeout);
            lastFetched = null;
            if (postcodeField.value.trim().length === 5 && this.value.trim()) {
                fetchTimeout = setTimeout(fetchRates, 200);
            }
        });

        // ── Shipping method toggle ─────────────────────────────────────────
        function setShippingMethod(method) {
            shippingMethodInput.value = method;

            if (method === 'self_pickup') {
                selfPickupInfo.classList.remove('hidden');
                courierCard.classList.add('hidden');

                labelPickup.classList.add('border-emerald-500', 'bg-emerald-50');
                labelPickup.classList.remove('border-slate-200');
                labelDelivery.classList.remove('border-emerald-500', 'bg-emerald-50');
                labelDelivery.classList.add('border-slate-200');

                document.querySelectorAll('#street_address,#postcode,#city,#state').forEach(function(f) {
                    if (f) f.removeAttribute('required');
                });

                shippingCourierInput.value = '';
                shippingServiceInput.value = '';
                shippingCostInput.value    = '0.00';
                shippingFeeRow.classList.add('hidden');
                updateTotals(0);

            } else {
                selfPickupInfo.classList.add('hidden');
                courierCard.classList.remove('hidden');

                labelDelivery.classList.add('border-emerald-500', 'bg-emerald-50');
                labelDelivery.classList.remove('border-slate-200');
                labelPickup.classList.remove('border-emerald-500', 'bg-emerald-50');
                labelPickup.classList.add('border-slate-200');

                document.querySelectorAll('#street_address,#city').forEach(function(f) {
                    if (f) f.setAttribute('required', 'required');
                });
                document.querySelectorAll('#postcode,#state').forEach(function(f) {
                    if (f) f.setAttribute('required', 'required');
                });

                // Auto-fetch if fields already filled
                lastFetched = null;
                if (postcodeField.value.trim().length === 5 && stateField.value.trim()) {
                    fetchRates();
                }
            }
        }

        radioDelivery.addEventListener('change', function() { setShippingMethod('delivery'); });
        radioPickup.addEventListener('change',   function() { setShippingMethod('self_pickup'); });

        // ── Auto-fetch on page load if postcode already present ────────────
        if (postcodeField.value.trim().length === 5 && stateField.value.trim()) {
            fetchRates();
        }
    });
</script>
@endpush
@endsection
