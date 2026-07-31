<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\UserCoupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────────

    /** Resolve cart items from DB (auth) or session (guest) */
    private function resolveCartItems(?object $user, array $selectedIds = []): \Illuminate\Support\Collection
    {
        if ($user) {
            $query = CartItem::with(['product', 'variation'])->where('user_id', $user->id);
            if (! empty($selectedIds)) {
                $query->whereIn('id', $selectedIds);
            } else {
                $query->where('is_selected', true);
            }
            return $query->get();
        }

        // Guest
        $guestCart = session('guest_cart', []);
        $cartCtrl  = new CartController();
        $ref       = new \ReflectionMethod(CartController::class, 'buildGuestCartObjects');
        $ref->setAccessible(true);
        $objects   = $ref->invoke($cartCtrl, $guestCart);

        // Filter by selected IDs if provided
        if (! empty($selectedIds)) {
            $objects = array_filter($objects, fn($o) => in_array($o->id, $selectedIds));
        } else {
            $objects = array_filter($objects, fn($o) => $o->is_selected);
        }

        return collect(array_values($objects));
    }

    // ─────────────────────────────────────────────────────────────────
    //  CHECKOUT PAGE
    // ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = Auth::user();

        $selectedItems = $request->input('selected_items', []);

        // For logged-in users, handle session-based item selection
        if ($user) {
            if (! empty($selectedItems)) {
                session(['checkout_items' => $selectedItems]);
            } else {
                $selectedItems = session('checkout_items', []);
            }
        }

        $cartItems = $this->resolveCartItems($user, $selectedItems);

        if ($cartItems->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Troli anda kosong.');
        }

        $subtotal    = 0;
        $totalWeight = 0;
        foreach ($cartItems as $item) {
            $subtotal    += $item->subtotal;
            $totalWeight += $item->item_weight;
        }

        // Apply coupon if valid (logged-in only)
        $discount   = 0.00;
        $couponCode = null;
        $coupon     = null;

        if ($user) {
            $couponCode = session('applied_coupon_code');
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();
                if ($coupon && $coupon->isValidForUser($user, $subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                } else {
                    session()->forget('applied_coupon_code');
                    $couponCode = null;
                }
            }
        }

        $total       = max(0.00, $subtotal - $discount);
        $sandboxMode = config('payment.toyyibpay.sandbox', false);

        $pickupAddress = [
            'name'     => env('EASYPARCEL_ORIGIN_NAME', 'Alfarhan Trading'),
            'address'  => env('EASYPARCEL_ORIGIN_ADDRESS', '-'),
            'city'     => env('EASYPARCEL_ORIGIN_CITY', 'Puchong'),
            'state'    => env('EASYPARCEL_ORIGIN_STATE', 'Selangor'),
            'postcode' => env('EASYPARCEL_ORIGIN_POSTCODE', '47100'),
            'phone'    => env('EASYPARCEL_ORIGIN_PHONE', '-'),
        ];

        return view('shop.checkout', compact(
            'cartItems', 'subtotal', 'discount', 'total', 'couponCode',
            'totalWeight', 'sandboxMode', 'pickupAddress'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  SHIPPING RATES (AJAX)
    // ─────────────────────────────────────────────────────────────────

    public function getShippingRates(Request $request)
    {
        $postcode = trim($request->get('postcode', ''));
        $state    = trim($request->get('state', ''));
        $weight   = (float) $request->get('weight', 0.50);

        if (! $postcode || strlen($postcode) < 5) {
            return response()->json(['success' => false, 'message' => 'Poskod tidak sah (mesti 5 digit).']);
        }

        $easyParcel = new \App\Services\EasyParcelService();
        $rates      = $easyParcel->getRates($postcode, max(0.10, $weight), $state);

        if (empty($rates)) {
            return response()->json(['success' => false, 'message' => 'Tiada kadar penghantaran tersedia.']);
        }

        // is_live = true jika API Key live digunakan (flag dalam setiap rate)
        $isLive = !empty($rates) && ($rates[0]['is_live'] ?? false);

        return response()->json([
            'success' => true,
            'rates'   => $rates,
            'is_live' => $isLive,
            'zone'    => $easyParcel->resolveZone($postcode, $state),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  PLACE ORDER
    // ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $user         = Auth::user();
        $isSelfPickup = $request->input('shipping_method') === 'self_pickup';
        $isGuest      = ! $user;

        $rules = [
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'street_address'   => $isSelfPickup ? 'nullable|string' : 'required|string',
            'postcode'         => $isSelfPickup ? 'nullable|string|max:10' : 'required|string|max:10',
            'city'             => $isSelfPickup ? 'nullable|string|max:255' : 'required|string|max:255',
            'state'            => $isSelfPickup ? 'nullable|string|max:255' : 'required|string|max:255',
            'payment_method'   => 'required|in:cod,online',
            'shipping_method'  => 'nullable|string',
            'shipping_courier' => 'nullable|string',
            'shipping_service' => 'nullable|string',
            'shipping_cost'    => 'nullable|numeric',
        ];

        // Guest must provide email
        if ($isGuest) {
            $rules['customer_email'] = 'required|email|max:255';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Resolve cart items
        $selectedItems = $user ? session('checkout_items', []) : [];
        $cartItems     = $this->resolveCartItems($user, $selectedItems);

        if ($cartItems->isEmpty()) {
            return redirect()->route('shop.cart')->with('error', 'Troli anda kosong.');
        }

        // Re-verify stock before proceeding
        foreach ($cartItems as $item) {
            $variationId = $item->product_variation_id ?? null;
            if ($variationId) {
                $stock = $item->variation->stock ?? 0;
                if ($stock < $item->quantity) {
                    return redirect()->route('shop.cart')->with('error',
                        'Stok tidak mencukupi! Hanya ' . $stock . ' unit ' . $item->product->name . ' (' . ($item->variation->value ?? '') . ') tersedia.'
                    );
                }
            } else {
                if ($item->product->stock < $item->quantity) {
                    return redirect()->route('shop.cart')->with('error',
                        'Stok tidak mencukupi! Hanya ' . $item->product->stock . ' unit ' . $item->product->name . ' tersedia.'
                    );
                }
            }
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->subtotal;
        }

        // Coupon (logged-in users only)
        $discount   = 0.00;
        $couponCode = null;
        $coupon     = null;

        if ($user) {
            $couponCode = session('applied_coupon_code');
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();
                if ($coupon && $coupon->isValidForUser($user, $subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                }
            }
        }

        // Shipping
        if ($isSelfPickup) {
            $shippingCost    = 0.00;
            $deliveryAddress = 'SELF PICKUP — ' .
                               env('EASYPARCEL_ORIGIN_ADDRESS', '-') . ', ' .
                               env('EASYPARCEL_ORIGIN_POSTCODE', '') . ' ' .
                               env('EASYPARCEL_ORIGIN_CITY', '') . ', ' .
                               env('EASYPARCEL_ORIGIN_STATE', '');
        } else {
            $shippingCost    = (float) $request->input('shipping_cost', 0.00);
            $deliveryAddress = $request->input('street_address') . ', ' .
                               $request->input('postcode') . ' ' .
                               $request->input('city') . ', ' .
                               $request->input('state');
        }

        // ToyyibPay processing fee (RM1 for online payment only)
        $toyyibpayFee = ($request->input('payment_method') === 'online')
                        ? 1.00
                        : 0.00;

        $total = max(0.00, $subtotal - $discount + $shippingCost + $toyyibpayFee);

        DB::beginTransaction();
        try {
            $guestToken = $isGuest ? Str::random(48) : null;

            $order = Order::create([
                'user_id'          => $user ? $user->id : null,
                'order_type'       => 'online',
                'customer_name'    => $request->customer_name,
                'customer_email'   => $isGuest ? $request->customer_email : $user->email,
                'customer_phone'   => $request->customer_phone,
                'delivery_address' => $deliveryAddress,
                'total_amount'     => $subtotal,
                'discount_amount'  => $discount,
                'shipping_cost'    => $shippingCost,
                'final_amount'     => $total,
                'coupon_code'      => $couponCode,
                'status'           => 'pending',
                'shipping_courier' => $isSelfPickup ? 'Self Pickup' : $request->input('shipping_courier'),
                'shipping_service' => $isSelfPickup ? 'Self Collection' : $request->input('shipping_service'),
                'shipping_postcode'=> $isSelfPickup ? env('EASYPARCEL_ORIGIN_POSTCODE') : $request->input('postcode'),
                'shipping_city'    => $isSelfPickup ? env('EASYPARCEL_ORIGIN_CITY') : $request->input('city'),
                'shipping_state'   => $isSelfPickup ? env('EASYPARCEL_ORIGIN_STATE') : $request->input('state'),
                'guest_token'      => $guestToken,
            ]);

            // Create order items and decrement stock
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'             => $order->id,
                    'product_id'           => $item->product_id,
                    'product_variation_id' => $item->product_variation_id ?? null,
                    'price'                => $item->unit_price,
                    'quantity'             => $item->quantity,
                ]);

                // Decrement stock
                if (! empty($item->product_variation_id)) {
                    $variation = ProductVariation::lockForUpdate()->find($item->product_variation_id);
                    $variation->stock -= $item->quantity;
                    $variation->save();
                } else {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    $product->stock -= $item->quantity;
                    $product->save();
                }
            }

            // Mark coupon as used (logged-in only)
            if ($coupon && $user) {
                $userCoupon = UserCoupon::where('user_id', $user->id)->where('coupon_id', $coupon->id)->first();
                if ($userCoupon) {
                    $userCoupon->used_at = now();
                    $userCoupon->save();
                }
            }

            // Clear cart
            if ($user) {
                if (! empty($selectedItems)) {
                    CartItem::where('user_id', $user->id)->whereIn('id', $selectedItems)->delete();
                } else {
                    CartItem::where('user_id', $user->id)->delete();
                }
                session()->forget('applied_coupon_code');
                session()->forget('checkout_items');
            } else {
                session()->forget('guest_cart');
            }

            DB::commit();

            if ($request->payment_method === 'online') {
                if ($isGuest) {
                    // Online payment for guest — redirect to payment with token
                    return redirect()
                        ->route('checkout.payment', ['order_id' => $order->id, 'token' => $guestToken])
                        ->with('info', 'Sila lengkapkan pembayaran anda.');
                }
                return redirect()
                    ->route('checkout.payment', ['order_id' => $order->id])
                    ->with('info', 'Sila lengkapkan pembayaran anda.');
            }

            // COD — redirect to success
            if ($isGuest) {
                return redirect()
                    ->route('checkout.success', ['id' => $order->id, 'token' => $guestToken])
                    ->with('success', 'Terima kasih! Pesanan anda telah diterima.');
            }

            return redirect()
                ->route('checkout.success', $order->id)
                ->with('success', 'Terima kasih! Pesanan anda telah diterima.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ralat berlaku: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────
    //  SUCCESS PAGE
    // ─────────────────────────────────────────────────────────────────

    public function success(Request $request, $id = null)
    {
        $user  = Auth::user();
        $token = $request->query('token');

        if (!$id) {
            if ($user) {
                return redirect()->route('customer.orders')->with('info', 'Sila lihat sejarah pesanan anda.');
            }
            return redirect()->route('shop.home');
        }

        if ($user) {
            $order = Order::with('items.product', 'items.variation')
                ->where('user_id', $user->id)
                ->findOrFail($id);
        } elseif ($token) {
            $order = Order::with('items.product', 'items.variation')
                ->where('id', $id)
                ->where('guest_token', $token)
                ->firstOrFail();
        } else {
            abort(403, 'Akses tidak dibenarkan.');
        }

        return view('shop.success', compact('order'));
    }

    // ─────────────────────────────────────────────────────────────────
    //  CANCEL PAGE / FALLBACK
    // ─────────────────────────────────────────────────────────────────

    public function cancel(Request $request)
    {
        return redirect()->route('shop.cart')->with('info', 'Pembayaran / Pembelian anda telah dibatalkan.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  CUSTOMER ORDER HISTORY (logged-in only)
    // ─────────────────────────────────────────────────────────────────

    public function orders()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $orders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('shop.orders', compact('orders'));
    }
}
