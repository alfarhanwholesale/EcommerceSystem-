@extends('layouts.admin')

@section('header_title')
    Order Management & Fulfillment
@endsection

@section('content')
<div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-xs space-y-6">
    <div>
        <h3 class="text-lg font-bold text-slate-800">All Orders</h3>
        <p class="text-xs text-slate-500 mt-1">Review orders placed online or logged by outdoor agents. Change status to transition fulfillment steps.</p>
    </div>

    <!-- Orders Table -->
    <div class="overflow-x-auto border border-slate-100 rounded-2xl">
        <table class="w-full text-left border-collapse text-sm font-medium text-slate-700">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                    <th class="p-4 pl-6">Order ID & Date</th>
                    <th class="p-4">Customer Details</th>
                    <th class="p-4">Type</th>
                    <th class="p-4">Items Summary</th>
                    <th class="p-4 text-right">Total Amount</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-right pr-6">Fulfillment Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-50/40 transition-colors">
                        <!-- ID & Date -->
                        <td class="p-4 pl-6">
                            <span class="font-bold text-slate-900 block">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                        </td>

                        <!-- Customer info -->
                        <td class="p-4">
                            <span class="font-bold text-slate-800 block">{{ $order->customer_name }}</span>
                            <span class="text-xs text-slate-500 block">{{ $order->customer_phone }}</span>
                            <span class="text-[10px] text-slate-400 block max-w-[150px] truncate" title="{{ $order->delivery_address }}">{{ $order->delivery_address }}</span>
                            @if($order->shipping_courier || $order->tracking_code)
                                @if($order->shipping_courier)
                                    <span class="text-[10px] text-slate-600 block mt-1 font-bold">
                                        🚚 {{ $order->shipping_courier }} @if($order->shipping_cost > 0)(RM{{ number_format($order->shipping_cost, 2) }})@endif
                                    </span>
                                @endif
                                @if($order->tracking_code)
                                    <span class="text-[9px] text-emerald-700 font-bold block mt-0.5">
                                        Tracking: {{ $order->tracking_code }}
                                    </span>
                                @endif
                            @endif
                        </td>

                        <!-- Type -->
                        <td class="p-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $order->order_type == 'online' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                {{ $order->order_type }}
                            </span>
                        </td>

                        <!-- Items details -->
                        <td class="p-4 text-xs font-semibold text-emerald-800 max-w-[200px] truncate" title="@foreach($order->items as $item){{ $item->product->name }} (x{{ $item->quantity }}){{ !$loop->last ? ', ' : '' }}@endforeach">
                            @foreach($order->items as $item)
                                {{ $item->product->name }} (x{{ $item->quantity }}){{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </td>

                        <!-- Totals -->
                        <td class="p-4 text-right font-bold text-slate-800">
                            RM{{ number_format($order->final_amount, 2) }}
                        </td>

                        <!-- Status Badge -->
                        <td class="p-4 text-center">
                            @switch($order->status)
                                @case('pending')
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Pending</span>
                                    @break
                                @case('processing')
                                    <span class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Processing</span>
                                    @break
                                @case('completed')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Completed</span>
                                    @break
                                @case('cancelled')
                                    <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Cancelled</span>
                                    @break
                                @default
                                    <span class="bg-slate-100 text-slate-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">{{ $order->status }}</span>
                            @endswitch
                        </td>

                        <!-- Action update dropdown -->
                        <td class="p-4 text-right pr-6">
                            <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="flex justify-end items-center gap-1.5">
                                @csrf
                                <select name="status" class="px-2 py-1.5 border border-slate-200 rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-emerald-600">
                                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <button type="submit" class="bg-emerald-800 hover:bg-emerald-950 text-white text-xs px-2.5 py-1.5 rounded-lg transition-colors font-bold shadow-xs">
                                    Update
                                </button>
                            </form>

                            {{-- Manual Shipping Info Toggle --}}
                            <div class="mt-2">
                                <button type="button"
                                    onclick="document.getElementById('ship-form-{{ $order->id }}').classList.toggle('hidden')"
                                    class="text-[10px] text-slate-500 hover:text-slate-800 font-semibold underline w-full text-right">
                                    {{ $order->tracking_code ? '✏️ Edit Tracking' : '📦 Isi No Tracking / Kurier' }}
                                </button>

                                <div id="ship-form-{{ $order->id }}" class="hidden mt-2 bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-2 text-left">
                                    <p class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Maklumat Penghantaran</p>
                                    <form action="{{ route('admin.orders.shipping', $order->id) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div>
                                            <label class="text-[10px] text-slate-500 font-semibold block mb-0.5">Nama Kurier</label>
                                            <input type="text" name="shipping_courier"
                                                value="{{ $order->shipping_courier }}"
                                                placeholder="cth: J&T, NinjaVan, DHL, Pos Malaysia..."
                                                class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        </div>
                                        <div>
                                            <label class="text-[10px] text-slate-500 font-semibold block mb-0.5">No Tracking</label>
                                            <input type="text" name="tracking_code"
                                                value="{{ $order->tracking_code }}"
                                                placeholder="cth: EP123456789MY"
                                                class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        </div>
                                        <button type="submit" class="w-full bg-indigo-700 hover:bg-indigo-900 text-white text-xs px-3 py-1.5 rounded-lg font-bold transition-colors">
                                            Simpan Maklumat
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if($order->order_type === 'online' && $order->shipping_courier && !$order->tracking_code)
                                <form action="{{ route('admin.orders.book_courier', $order->id) }}" method="POST" class="mt-2 flex justify-end">
                                    @csrf
                                    <button type="submit" class="bg-slate-600 hover:bg-slate-800 text-white text-[10px] px-2.5 py-1.5 rounded-lg transition-colors font-bold shadow-xs flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                        Auto EasyParcel
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500 font-medium">No orders recorded in database.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
