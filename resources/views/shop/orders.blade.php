@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-12">
    <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-6 md:mb-8 serif-font">My Orders</h1>

    @if($orders->isEmpty())
        <div class="bg-white border border-emerald-100 rounded-3xl p-12 md:p-16 text-center shadow-xs space-y-4">
            <span class="text-5xl">📦</span>
            <h3 class="text-xl font-bold text-slate-800">No orders placed yet</h3>
            <p class="text-slate-500 text-sm max-w-sm mx-auto font-medium">Browse our selection of dates, honey, perfume, bakhoor, and more — and place your first order!</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-8 py-3 rounded-full text-sm shadow-md transition-all">
                Start Shopping
            </a>
        </div>
    @else
        {{-- Desktop Table --}}
        <div class="hidden md:block bg-white border border-emerald-100 rounded-2xl shadow-xs overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-emerald-50/60 border-b border-emerald-100 text-slate-600 font-bold text-xs uppercase tracking-wider">
                        <th class="p-5 pl-6">Order ID</th>
                        <th class="p-5">Date</th>
                        <th class="p-5">Type</th>
                        <th class="p-5">Items Purchased</th>
                        <th class="p-5">Total Amount</th>
                        <th class="p-5 text-center">Status</th>
                        <th class="p-5 text-right pr-6">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                    @foreach($orders as $order)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="p-5 pl-6 font-bold text-slate-950">
                                #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="p-5">
                                {{ $order->created_at->format('M d, Y') }}
                            </td>
                            <td class="p-5">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $order->order_type == 'online' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $order->order_type }}
                                </span>
                            </td>
                            <td class="p-5 text-xs text-slate-500 max-w-xs truncate">
                                @foreach($order->items as $item)
                                    {{ $item->product->name }} (x{{ $item->quantity }}){{ !$loop->last ? ', ' : '' }}
                                @endforeach
                                @if($order->order_type !== 'pickup')
                                    @if($order->shipping_courier && $order->shipping_courier !== 'Self Pickup')
                                        <span class="block text-[10px] text-emerald-700 font-bold mt-1">
                                            🚚 {{ $order->shipping_courier }}
                                            @if($order->tracking_code) | Tracking: {{ $order->tracking_code }}@endif
                                        </span>
                                    @elseif($order->tracking_code)
                                        <span class="block text-[10px] text-emerald-700 font-bold mt-1">📦 Tracking: {{ $order->tracking_code }}</span>
                                    @else
                                        <span class="block text-[9px] text-amber-600 mt-1">Kurier akan ditentukan oleh admin</span>
                                    @endif
                                @endif
                            </td>
                            <td class="p-5 font-bold text-emerald-800">
                                RM{{ number_format($order->final_amount, 2) }}
                            </td>
                            <td class="p-5 text-center">
                                @switch($order->status)
                                    @case('pending')
                                        <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Pending</span>
                                        @break
                                    @case('processing')
                                        <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Processing</span>
                                        @break
                                    @case('completed')
                                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Completed</span>
                                        @break
                                    @case('cancelled')
                                        <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Cancelled</span>
                                        @break
                                    @default
                                        <span class="bg-slate-100 text-slate-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">{{ $order->status }}</span>
                                @endswitch
                            </td>
                            <td class="p-5 text-right pr-6">
                                <a href="{{ route('checkout.success', $order->id) }}" class="text-emerald-700 hover:text-emerald-950 font-bold transition-colors">
                                    View Invoice →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden space-y-4">
            @foreach($orders as $order)
                <div class="bg-white border border-emerald-100 rounded-2xl shadow-xs p-5 space-y-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-extrabold text-slate-900 text-base">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $order->order_type == 'online' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $order->order_type }}
                            </span>
                            @switch($order->status)
                                @case('pending')
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Pending</span>
                                    @break
                                @case('processing')
                                    <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Processing</span>
                                    @break
                                @case('completed')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Completed</span>
                                    @break
                                @case('cancelled')
                                    <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Cancelled</span>
                                    @break
                                @default
                                    <span class="bg-slate-100 text-slate-800 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">{{ $order->status }}</span>
                            @endswitch
                        </div>
                    </div>

                    <div class="text-xs text-slate-500 space-y-1 border-t border-slate-100 pt-3">
                        <p class="font-semibold text-slate-700 mb-1">Items:</p>
                        @foreach($order->items as $item)
                            <p>{{ $item->product->name }} <span class="text-emerald-700 font-bold">x{{ $item->quantity }}</span></p>
                        @endforeach

                        @if($order->order_type !== 'pickup')
                            @if($order->shipping_courier && $order->shipping_courier !== 'Self Pickup')
                                <div class="mt-2 text-[10px] text-emerald-700 bg-emerald-50 p-2 rounded-md border border-emerald-100 font-bold">
                                    🚚 Kurier: {{ $order->shipping_courier }}
                                    @if($order->tracking_code)<br>📦 Tracking: {{ $order->tracking_code }}@endif
                                </div>
                            @elseif($order->tracking_code)
                                <div class="mt-2 text-[10px] text-emerald-700 bg-emerald-50 p-2 rounded-md border border-emerald-100 font-bold">
                                    📦 Tracking: {{ $order->tracking_code }}
                                </div>
                            @else
                                <div class="mt-2 text-[9px] text-amber-700 bg-amber-50 p-2 rounded-md border border-amber-100 font-semibold">
                                    ⏳ Kurier akan ditentukan oleh admin
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-100 pt-3">
                        <div>
                            <p class="text-xs text-slate-500">Jumlah Bayaran</p>
                            <p class="text-emerald-800 font-extrabold text-lg">RM{{ number_format($order->final_amount, 2) }}</p>
                        </div>
                        <a href="{{ route('checkout.success', $order->id) }}" 
                           class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-5 py-2.5 rounded-xl text-xs transition-all shadow-sm">
                            Lihat Invois →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
