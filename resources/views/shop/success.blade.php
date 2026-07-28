@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto my-8 md:my-16 bg-white border border-emerald-100 rounded-3xl shadow-xl overflow-hidden p-5 md:p-12 space-y-6 md:space-y-8">
    <!-- Icon Header -->
    <div class="text-center space-y-4">
        <div class="w-20 h-20 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center mx-auto text-4xl animate-bounce">
            ✓
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 serif-font">Order Placed Successfully!</h1>
        <p class="text-sm text-slate-500 font-medium max-w-md mx-auto">
            Terima kasih kerana membeli-belah di AlfarhanWholesale. Pesanan anda telah diterima dan penyimpan stok kami sedang menyediakan penghantaran.
        </p>
    </div>

    <!-- Order Details Box -->
    <div class="bg-emerald-50/40 border border-emerald-100 rounded-2xl p-6 md:p-8 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs font-semibold uppercase tracking-wider text-slate-500">
            <div>
                <span>Order ID</span>
                <p class="text-slate-800 font-bold text-sm mt-1">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div>
                <span>Order Date</span>
                <p class="text-slate-800 font-bold text-sm mt-1">{{ $order->created_at->format('M d, Y') }}</p>
            </div>
            <div>
                <span>Payment</span>
                <p class="text-slate-800 font-bold text-sm mt-1">
                    {{ $order->payment_method === 'cod' ? 'Cash on Delivery (COD)' : 'Online Banking' }}
                </p>
            </div>
            <div>
                <span>Order Status</span>
                <span class="inline-block bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full mt-1">
                    {{ strtoupper($order->status) }}
                </span>
            </div>
        </div>

        <div class="border-t border-emerald-100 pt-6 space-y-3">
            <h4 class="text-sm font-bold text-emerald-950">Shipping Destination</h4>
            <div class="text-sm text-slate-600 leading-relaxed font-medium">
                <p class="font-bold text-slate-800">{{ $order->customer_name }}</p>
                <p>{{ $order->customer_phone }}</p>
                <p class="mt-1">{{ $order->delivery_address }}</p>
            </div>

            @if($order->shipping_courier)
                <div class="mt-4 border-t border-emerald-100/50 pt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-medium">
                    <div>
                        <span class="text-slate-500 block text-xs uppercase font-bold">Courier Option</span>
                        <p class="text-slate-800 font-bold mt-0.5">{{ $order->shipping_courier }} ({{ $order->shipping_service }})</p>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-xs uppercase font-bold">Tracking Code (EasyParcel)</span>
                        @if($order->tracking_code)
                            <a href="https://easyparcel.com/my/en/track/?noc=2&cno={{ $order->tracking_code }}" target="_blank" class="text-emerald-700 hover:text-emerald-900 font-bold flex items-center gap-1 mt-0.5 decoration-dotted underline">
                                {{ $order->tracking_code }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                        @else
                            <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-xs font-semibold inline-block mt-0.5 border border-amber-100">Proses pembungkusan (Kurier belum ditempah)</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Items Summary Table -->
    <div class="border border-slate-100 rounded-2xl overflow-hidden shadow-xs">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                    <th class="p-4 pl-6">Product Item</th>
                    <th class="p-4 text-center">Qty</th>
                    <th class="p-4 text-right pr-6">Line Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm font-medium">
                @foreach($order->items as $item)
                    <tr>
                        <td class="p-4 pl-6">
                            <span class="font-bold text-slate-800">{{ $item->product->name }}</span>
                            @if($item->product_variation_id && $item->variation)
                                <span class="block text-[10px] text-slate-400">Option: {{ $item->variation->value }}</span>
                            @endif
                        </td>
                        <td class="p-4 text-center text-slate-600">{{ $item->quantity }}</td>
                        <td class="p-4 text-right pr-6 text-slate-800 font-bold">RM{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-50/60 border-t border-slate-100 font-bold text-xs uppercase tracking-wider">
                    <td colspan="2" class="p-4 pl-6 text-slate-500 text-right">Subtotal</td>
                    <td class="p-4 text-right pr-6 text-slate-800">RM{{ number_format($order->total_amount, 2) }}</td>
                </tr>
                @if($order->discount_amount > 0)
                    <tr class="bg-slate-50/60 border-t border-slate-100 font-bold text-xs uppercase tracking-wider text-emerald-800">
                        <td colspan="2" class="p-4 pl-6 text-right">Coupon Discount</td>
                        <td class="p-4 text-right pr-6">-RM{{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                @endif
                @if($order->shipping_cost > 0)
                    <tr class="bg-slate-50/60 border-t border-slate-100 font-bold text-xs uppercase tracking-wider text-slate-600">
                        <td colspan="2" class="p-4 pl-6 text-right">Shipping Fee ({{ $order->shipping_courier }})</td>
                        <td class="p-4 text-right pr-6">RM{{ number_format($order->shipping_cost, 2) }}</td>
                    </tr>
                @endif
                <tr class="bg-emerald-50 border-t border-emerald-100 font-bold text-sm uppercase tracking-wider text-emerald-950">
                    <td colspan="2" class="p-4 pl-6 text-right">Final Total Paid</td>
                    <td class="p-4 text-right pr-6 text-emerald-800 text-base font-extrabold">RM{{ number_format($order->final_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 pt-4 border-t border-slate-100">
        <a href="{{ route('shop.index') }}" 
           class="w-full sm:w-auto bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-8 py-3 rounded-full shadow-md text-sm text-center transition-all">
            Continue Shopping
        </a>
        @auth
        <a href="{{ route('customer.orders') }}" 
           class="w-full sm:w-auto border border-emerald-200 text-emerald-800 hover:bg-emerald-50 font-bold px-8 py-3 rounded-full text-sm text-center transition-all">
            View Order History
        </a>
        @endauth
        @guest
        <a href="{{ route('register') }}"
           class="w-full sm:w-auto border border-emerald-200 text-emerald-800 hover:bg-emerald-50 font-bold px-8 py-3 rounded-full text-sm text-center transition-all">
            Daftar Akaun untuk Jejak Pesanan
        </a>
        @endguest
    </div>
</div>
@endsection
