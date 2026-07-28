<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\UserCoupon;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    //  HELPERS — session cart (guest) abstraction
    // ─────────────────────────────────────────────────────────────────

    /** Return the guest cart array from the session */
    private function getGuestCart(): array
    {
        return session('guest_cart', []);
    }

    /** Save the guest cart array back to the session */
    private function saveGuestCart(array $cart): void
    {
        session(['guest_cart' => $cart]);
    }

    /**
     * Get cart items as a unified collection/array with computed attributes.
     * For logged-in users: Eloquent CartItem collection (existing behaviour).
     * For guests: array of stdClass objects mimicking CartItem attributes.
     */
    private function buildGuestCartObjects(array $cart): array
    {
        $items = [];
        foreach ($cart as $entry) {
            $product   = Product::with('variations')->find($entry['product_id']);
            if (! $product) continue;

            $variation = null;
            if (! empty($entry['product_variation_id'])) {
                $variation = ProductVariation::find($entry['product_variation_id']);
            }

            $unitPrice = $variation ? $variation->active_price : $product->active_price;
            $unitWeight = $variation
                ? ($variation->active_weight ?? ($product->weight ?? 0.50))
                : (float)($product->weight ?? 0.50);

            $obj = (object) [
                'id'                   => $entry['id'],
                'product_id'           => $entry['product_id'],
                'product_variation_id' => $entry['product_variation_id'] ?? null,
                'quantity'             => $entry['quantity'],
                'is_selected'          => $entry['is_selected'] ?? true,
                'product'              => $product,
                'variation'            => $variation,
                'unit_price'           => $unitPrice,
                'subtotal'             => $unitPrice * $entry['quantity'],
                'unit_weight'          => $unitWeight,
                'item_weight'          => $unitWeight * $entry['quantity'],
            ];

            $items[] = $obj;
        }
        return $items;
    }

    // ─────────────────────────────────────────────────────────────────
    //  VIEW CART
    // ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $user = Auth::user();

        if ($user) {
            // ── Logged-in: DB cart ────────────────────────────────────
            $cartItems = CartItem::with(['product', 'variation'])
                ->where('user_id', $user->id)
                ->get();

            // Default all to selected if none selected
            if ($cartItems->isNotEmpty() && !$cartItems->contains('is_selected', true)) {
                CartItem::where('user_id', $user->id)->update(['is_selected' => true]);
                $cartItems = CartItem::with(['product', 'variation'])
                    ->where('user_id', $user->id)
                    ->get();
            }

            $subtotal    = 0;
            $totalWeight = 0;
            foreach ($cartItems as $item) {
                if ($item->is_selected) {
                    $subtotal    += $item->subtotal;
                    $totalWeight += $item->item_weight;
                }
            }

            // Fetch available coupons the user can claim
            $allCoupons       = Coupon::where('is_active', true)->get();
            $claimedCouponIds = UserCoupon::where('user_id', $user->id)->pluck('coupon_id')->toArray();
            $usedCouponIds    = UserCoupon::where('user_id', $user->id)->whereNotNull('used_at')->pluck('coupon_id')->toArray();

            // Active coupon applied in the session
            $appliedCoupon = null;
            $discount      = 0.00;
            $couponCode    = session('applied_coupon_code');

            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();
                if ($coupon && $coupon->isValidForUser($user, $subtotal)) {
                    $appliedCoupon = $coupon;
                    $discount      = $coupon->calculateDiscount($subtotal);
                } else {
                    session()->forget('applied_coupon_code');
                }
            }

            $total = max(0.00, $subtotal - $discount);

            return view('shop.cart', compact(
                'cartItems', 'subtotal', 'totalWeight', 'discount', 'total',
                'allCoupons', 'claimedCouponIds', 'usedCouponIds', 'appliedCoupon'
            ));
        }

        // ── Guest: session cart ───────────────────────────────────────
        $guestCart   = $this->getGuestCart();
        $cartItems   = collect($this->buildGuestCartObjects($guestCart));
        $subtotal    = 0;
        $totalWeight = 0;
        foreach ($cartItems as $item) {
            if ($item->is_selected) {
                $subtotal    += $item->subtotal;
                $totalWeight += $item->item_weight;
            }
        }

        $allCoupons = collect();
        $claimedCouponIds = [];
        $usedCouponIds    = [];
        $appliedCoupon    = null;
        $discount         = 0.00;
        $total            = $subtotal;

        return view('shop.cart', compact(
            'cartItems', 'subtotal', 'totalWeight', 'discount', 'total',
            'allCoupons', 'claimedCouponIds', 'usedCouponIds', 'appliedCoupon'
        ));
    }

    // ─────────────────────────────────────────────────────────────────
    //  ADD ITEM TO CART
    // ─────────────────────────────────────────────────────────────────

    public function add(Request $request)
    {
        $request->validate([
            'product_id'           => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'quantity'             => 'required|integer|min:1',
        ]);

        $productId   = $request->product_id;
        $variationId = $request->product_variation_id;
        $quantity    = $request->quantity;

        // Enforce: products with variations MUST select a variation
        $product = Product::with('variations')->findOrFail($productId);
        if ($product->variations->isNotEmpty() && ! $variationId) {
            return back()->with('error', 'Sila pilih variasi produk terlebih dahulu.');
        }

        // Verify stock
        if ($variationId) {
            $variation = ProductVariation::findOrFail($variationId);
            if ($variation->stock < $quantity) {
                return back()->with('error', 'Stok tidak mencukupi. Hanya ' . $variation->stock . ' unit tersedia.');
            }
        } else {
            if ($product->stock < $quantity) {
                return back()->with('error', 'Stok tidak mencukupi. Hanya ' . $product->stock . ' unit tersedia.');
            }
        }

        $user = Auth::user();

        if ($user) {
            // ── Logged-in: DB cart ────────────────────────────────────
            $cartItem = CartItem::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->where('product_variation_id', $variationId)
                ->first();

            if ($cartItem) {
                $newQty = $cartItem->quantity + $quantity;
                if ($variationId && $variation->stock < $newQty) {
                    return back()->with('error', 'Tidak boleh tambah lagi. Jumlah stok adalah ' . $variation->stock . '.');
                }
                if (! $variationId && $product->stock < $newQty) {
                    return back()->with('error', 'Tidak boleh tambah lagi. Jumlah stok adalah ' . $product->stock . '.');
                }
                $cartItem->quantity = $newQty;
                $cartItem->save();
            } else {
                CartItem::create([
                    'user_id'              => $user->id,
                    'product_id'           => $productId,
                    'product_variation_id' => $variationId,
                    'quantity'             => $quantity,
                    'is_selected'          => true,
                ]);
            }
        } else {
            // ── Guest: session cart ───────────────────────────────────
            $cart    = $this->getGuestCart();
            $key     = $productId . '_' . ($variationId ?? '0');
            $matched = false;

            foreach ($cart as &$entry) {
                if ($entry['product_id'] == $productId && ($entry['product_variation_id'] ?? null) == $variationId) {
                    $newQty = $entry['quantity'] + $quantity;
                    if ($variationId && $variation->stock < $newQty) {
                        return back()->with('error', 'Tidak boleh tambah lagi. Jumlah stok adalah ' . $variation->stock . '.');
                    }
                    if (! $variationId && $product->stock < $newQty) {
                        return back()->with('error', 'Tidak boleh tambah lagi. Jumlah stok adalah ' . $product->stock . '.');
                    }
                    $entry['quantity'] = $newQty;
                    $matched = true;
                    break;
                }
            }
            unset($entry);

            if (! $matched) {
                $cart[] = [
                    'id'                   => $key . '_' . time(),
                    'product_id'           => $productId,
                    'product_variation_id' => $variationId,
                    'quantity'             => $quantity,
                    'is_selected'          => true,
                ];
            }

            $this->saveGuestCart($cart);
        }

        return redirect()->route('shop.cart')->with('success', 'Produk berjaya ditambah ke troli!');
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE QUANTITY
    // ─────────────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $quantity = $request->quantity;
        $user     = Auth::user();

        if ($user) {
            $cartItem = CartItem::where('user_id', $user->id)->findOrFail($id);

            if ($cartItem->product_variation_id) {
                if ($cartItem->variation->stock < $quantity) {
                    return back()->with('error', 'Hanya ' . $cartItem->variation->stock . ' unit tersedia.');
                }
            } else {
                if ($cartItem->product->stock < $quantity) {
                    return back()->with('error', 'Hanya ' . $cartItem->product->stock . ' unit tersedia.');
                }
            }

            $cartItem->quantity = $quantity;
            $cartItem->save();
        } else {
            $cart    = $this->getGuestCart();
            $updated = false;

            foreach ($cart as &$entry) {
                if ($entry['id'] == $id) {
                    // Stock check
                    if (! empty($entry['product_variation_id'])) {
                        $v = ProductVariation::find($entry['product_variation_id']);
                        if ($v && $v->stock < $quantity) {
                            return back()->with('error', 'Hanya ' . $v->stock . ' unit tersedia.');
                        }
                    } else {
                        $p = Product::find($entry['product_id']);
                        if ($p && $p->stock < $quantity) {
                            return back()->with('error', 'Hanya ' . $p->stock . ' unit tersedia.');
                        }
                    }
                    $entry['quantity'] = $quantity;
                    $updated = true;
                    break;
                }
            }
            unset($entry);

            if ($updated) $this->saveGuestCart($cart);
        }

        return redirect()->route('shop.cart')->with('success', 'Troli dikemas kini.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  REMOVE ITEM
    // ─────────────────────────────────────────────────────────────────

    public function remove($id)
    {
        $user = Auth::user();

        if ($user) {
            $cartItem = CartItem::where('user_id', $user->id)->findOrFail($id);
            $cartItem->delete();
        } else {
            $cart = array_filter($this->getGuestCart(), fn($e) => $e['id'] != $id);
            $this->saveGuestCart(array_values($cart));
        }

        return redirect()->route('shop.cart')->with('success', 'Item dikeluarkan dari troli.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  UPDATE SELECTION
    // ─────────────────────────────────────────────────────────────────

    public function updateSelection(Request $request)
    {
        $checkedIds = $request->checked_ids ?? [];
        $user       = Auth::user();

        if ($user) {
            CartItem::where('user_id', $user->id)->update(['is_selected' => false]);
            if (! empty($checkedIds)) {
                CartItem::where('user_id', $user->id)->whereIn('id', $checkedIds)->update(['is_selected' => true]);
            }

            $cartItems   = CartItem::with(['product', 'variation'])->where('user_id', $user->id)->get();
            $subtotal    = 0;
            $totalWeight = 0;
            foreach ($cartItems as $item) {
                if ($item->is_selected) {
                    $subtotal    += $item->subtotal;
                    $totalWeight += $item->item_weight;
                }
            }

            $discount   = 0.00;
            $couponCode = session('applied_coupon_code');
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();
                if ($coupon && $coupon->isValidForUser($user, $subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                } else {
                    session()->forget('applied_coupon_code');
                }
            }

            $total = max(0.00, $subtotal - $discount);

            return response()->json([
                'success'             => true,
                'subtotal'            => number_format($subtotal, 2),
                'total_weight'        => (float) $totalWeight,
                'total_weight_formatted' => number_format($totalWeight, 2) . ' kg',
                'discount'            => number_format($discount, 2),
                'total'               => number_format($total, 2),
                'coupon_removed'      => $couponCode && ! session('applied_coupon_code'),
            ]);
        }

        // Guest: toggle is_selected in session
        $cart = $this->getGuestCart();
        foreach ($cart as &$entry) {
            $entry['is_selected'] = in_array($entry['id'], $checkedIds);
        }
        unset($entry);
        $this->saveGuestCart($cart);

        $items       = $this->buildGuestCartObjects($cart);
        $subtotal    = 0;
        $totalWeight = 0;
        foreach ($items as $item) {
            if ($item->is_selected) {
                $subtotal    += $item->subtotal;
                $totalWeight += $item->item_weight;
            }
        }

        return response()->json([
            'success'                => true,
            'subtotal'               => number_format($subtotal, 2),
            'total_weight'           => (float) $totalWeight,
            'total_weight_formatted' => number_format($totalWeight, 2) . ' kg',
            'discount'               => '0.00',
            'total'                  => number_format($subtotal, 2),
            'coupon_removed'         => false,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  COUPON ACTIONS (logged-in only)
    // ─────────────────────────────────────────────────────────────────

    public function claimCoupon(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Sila log masuk untuk menuntut kupon.');
        }

        $request->validate(['coupon_id' => 'required|exists:coupons,id']);
        $couponId = $request->coupon_id;

        $alreadyClaimed = UserCoupon::where('user_id', $user->id)->where('coupon_id', $couponId)->exists();
        if ($alreadyClaimed) {
            return back()->with('error', 'Anda sudah menuntut kupon ini.');
        }

        UserCoupon::create(['user_id' => $user->id, 'coupon_id' => $couponId]);
        return back()->with('success', 'Kupon berjaya dituntut! Anda boleh menggunakannya sekarang.');
    }

    public function applyCoupon(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Sila log masuk untuk menggunakan kupon.');
        }

        $request->validate(['coupon_code' => 'required|string']);
        $coupon = Coupon::where('code', $request->coupon_code)->where('is_active', true)->first();

        if (! $coupon) {
            return back()->with('error', 'Kod kupon tidak sah atau telah tamat tempoh.');
        }

        $cartItems = CartItem::where('user_id', $user->id)->get();
        $subtotal  = 0;
        foreach ($cartItems as $item) {
            if ($item->is_selected) $subtotal += $item->subtotal;
        }

        if (! $coupon->isValidForUser($user, $subtotal)) {
            if ($subtotal < $coupon->min_spend) {
                return back()->with('error', 'Minimum pembelian sebanyak RM' . number_format($coupon->min_spend, 2) . ' diperlukan untuk menggunakan kupon ini.');
            }
            return back()->with('error', 'Kupon ini tidak sah untuk anda.');
        }

        session(['applied_coupon_code' => $coupon->code]);
        return back()->with('success', 'Kod kupon berjaya digunakan!');
    }

    public function removeCoupon()
    {
        session()->forget('applied_coupon_code');
        return back()->with('success', 'Kupon dikeluarkan.');
    }

    // ─────────────────────────────────────────────────────────────────
    //  MERGE GUEST CART → DB (called on login)
    // ─────────────────────────────────────────────────────────────────

    public static function mergeGuestCart(int $userId): void
    {
        $guestCart = session('guest_cart', []);
        if (empty($guestCart)) return;

        foreach ($guestCart as $entry) {
            $existing = CartItem::where('user_id', $userId)
                ->where('product_id', $entry['product_id'])
                ->where('product_variation_id', $entry['product_variation_id'] ?? null)
                ->first();

            if ($existing) {
                $existing->quantity += $entry['quantity'];
                $existing->save();
            } else {
                CartItem::create([
                    'user_id'              => $userId,
                    'product_id'           => $entry['product_id'],
                    'product_variation_id' => $entry['product_variation_id'] ?? null,
                    'quantity'             => $entry['quantity'],
                    'is_selected'          => true,
                ]);
            }
        }

        session()->forget('guest_cart');
    }
}
