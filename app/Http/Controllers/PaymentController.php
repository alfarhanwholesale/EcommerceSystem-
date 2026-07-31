<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Initiate FPX / eWallet payment for an existing order.
     */
    /**
     * Initiate FPX / eWallet payment for an existing order.
     */
    public function checkout(Request $request, $order_id): RedirectResponse
    {
        $user  = Auth::user();
        $token = $request->query('token');

        if ($user) {
            $order = Order::where('user_id', $user->id)->findOrFail($order_id);
        } elseif ($token) {
            $order = Order::where('id', $order_id)->where('guest_token', $token)->firstOrFail();
        } else {
            $order = Order::findOrFail($order_id);
        }

        if ($order->status === 'paid') {
            $params = ['id' => $order->id];
            if ($order->guest_token) $params['token'] = $order->guest_token;
            return redirect()
                ->route('checkout.success', $params)
                ->with('success', 'Pesanan ini telah dibayar.');
        }

        if (! in_array($order->status, ['pending'], true)) {
            if ($user) {
                return redirect()->route('customer.orders')->with('error', 'Pesanan ini tidak boleh dibayar pada masa ini.');
            }
            return redirect()->route('shop.index')->with('error', 'Pesanan ini tidak boleh dibayar pada masa ini.');
        }

        return match (config('payment.gateway')) {
            'billplz' => $this->redirectToBillplz($order),
            default   => $this->redirectToToyyibPay($order),
        };
    }

    /**
     * Browser return URL after the customer finishes on the gateway page.
     */
    public function status(Request $request): RedirectResponse
    {
        $orderId = $request->integer('order_id') ?: $request->integer('id');
        $token   = $request->query('token');
        $user    = Auth::user();

        $order = null;
        if ($orderId) {
            if ($user) {
                $order = Order::where('user_id', $user->id)->find($orderId);
            }
            if (! $order && $token) {
                $order = Order::where('id', $orderId)->where('guest_token', $token)->first();
            }
            if (! $order) {
                $order = Order::find($orderId);
            }
        }
        if (! $order && $request->input('billcode')) {
            $order = Order::where('payment_bill_code', $request->input('billcode'))->first();
        }

        if (! $order) {
            return redirect()->route('shop.index')->with('error', 'Pesanan tidak dijumpai.');
        }

        $isPaid = match (config('payment.gateway')) {
            'billplz' => $request->boolean('paid'),
            default   => (string) $request->input('status_id') === '1',
        };

        $successParams = ['id' => $order->id];
        if ($order->guest_token) {
            $successParams['token'] = $order->guest_token;
        }

        if ($isPaid || $order->status === 'paid') {
            // Mark as paid if webhook hasn't already done so
            if ($order->status !== 'paid') {
                $this->markOrderAsPaid($order, $request->input('transaction_id') ?? $request->input('billcode'));
            }

            return redirect()
                ->route('checkout.success', $successParams)
                ->with('success', 'Bayaran berjaya diterima. Terima kasih!');
        }

        if ((string) $request->input('status_id') === '2') {
            return redirect()
                ->route('checkout.success', $successParams)
                ->with('info', 'Pembayaran masih dalam proses (Pending).');
        }

        return redirect()
            ->route('checkout.success', $successParams)
            ->with('error', 'Pembayaran tidak dilengkapkan atau dibatalkan.');
    }

    /**
     * Server-to-server webhook/callback from ToyyibPay / Billplz / Payment gateway.
     */
    public function webhook(Request $request): Response
    {
        Log::info('Payment webhook received', $request->all());

        return match (config('payment.gateway')) {
            'billplz' => $this->handleBillplzWebhook($request),
            default   => $this->handleToyyibPayWebhook($request),
        };
    }

    /**
     * Alias method for webhook handler.
     */
    public function handleWebhook(Request $request): Response
    {
        return $this->webhook($request);
    }

    // =========================================================================
    // ToyyibPay Logic
    // =========================================================================

    private function redirectToToyyibPay(Order $order): RedirectResponse
    {
        $config      = config('payment.toyyibpay');
        $amountInSen = $this->toSen($order->final_amount);
        $isSandbox   = (bool) $config['sandbox'];

        Log::info('ToyyibPay createBill attempt', [
            'order_id'   => $order->id,
            'amount_sen' => $amountInSen,
            'base_url'   => $config['base_url'],
            'sandbox'    => $isSandbox,
        ]);

        try {
            $returnParams = ['order_id' => $order->id];
            if ($order->guest_token) {
                $returnParams['token'] = $order->guest_token;
            }

            $response = Http::timeout(15)
                ->asForm()
                ->post("{$config['base_url']}/index.php/api/createBill", [
                    'userSecretKey'          => $config['secret_key'],
                    'categoryCode'           => $config['category_code'],
                    'billName'               => 'Order #' . $order->id,
                    'billDescription'        => 'Alfarhan Wholesale order payment',
                    'billPriceSetting'       => 1,
                    'billPayorInfo'          => 1,
                    'billAmount'             => $amountInSen,
                    'billReturnUrl'          => route('checkout.payment.status', $returnParams),
                    'billCallbackUrl'        => route('webhook.payment'),
                    'billExternalReferenceNo'=> (string) $order->id,
                    'billTo'                 => $order->customer_name ?? 'Customer',
                    'billEmail'              => $order->customer_email ?? 'noemail@example.com',
                    'billPhone'              => $order->customer_phone ?? '0123456789',
                    'billSplitPayment'       => 0,
                    'billSplitPaymentArgs'   => '',
                    'billPaymentChannel'     => 0,
                    'billDisplayMerchant'    => 1,
                    'billContentEmail'       => 'Thank you for your order at Alfarhan Wholesale!',
                ]);

            Log::info('ToyyibPay createBill response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->successful()) {
                $payload  = $response->json();
                $billCode = null;
                if (is_array($payload)) {
                    $billCode = $payload[0]['BillCode'] ?? $payload['BillCode'] ?? null;
                }

                if ($billCode) {
                    $order->update(['payment_bill_code' => $billCode]);
                    $paymentUrl = "{$config['base_url']}/{$billCode}";
                    Log::info('Redirecting to ToyyibPay', ['url' => $paymentUrl]);
                    return redirect()->away($paymentUrl);
                }

                Log::error('ToyyibPay createBill returned no BillCode', ['payload' => $payload]);
            } else {
                Log::warning('ToyyibPay createBill HTTP error', ['status' => $response->status(), 'body' => $response->body()]);
            }

        } catch (\Exception $e) {
            Log::warning('ToyyibPay API unreachable: ' . $e->getMessage(), ['order_id' => $order->id]);
        }

        // ── Fallback: Simulasi pembayaran (SANDBOX ONLY) ─────────────────────
        if ($isSandbox) {
            Log::info('ToyyibPay sandbox: simulating successful payment', ['order_id' => $order->id]);

            // Mark order as paid with simulation reference
            $this->markOrderAsPaid($order, 'SANDBOX-SIM-' . strtoupper(substr(md5($order->id . time()), 0, 8)));

            $params = ['id' => $order->id];
            if ($order->guest_token) {
                $params['token'] = $order->guest_token;
            }

            return redirect()
                ->route('checkout.success', $params)
                ->with('success', '✅ Simulasi bayaran berjaya! (Mode Ujian — Tiada bayaran sebenar diproses)');
        }

        // ── Production: tunjuk error ─────────────────────────────────────────
        return back()->with('error', 'Tidak dapat menghubungi gateway pembayaran. Sila cuba lagi atau pilih COD.');
    }

    private function handleToyyibPayWebhook(Request $request): Response
    {
        // ToyyibPay POST fields: status, billcode, order_id, amount, transaction_id, msg
        $status      = (string) $request->input('status');
        $billCode    = (string) $request->input('billcode');
        $orderId     = (int)    $request->input('order_id');
        $amountInSen = (int)    $request->input('amount');
        $transactionId = (string) $request->input('transaction_id', '');

        Log::info('ToyyibPay webhook parsed', compact('status', 'billCode', 'orderId', 'amountInSen'));

        // status '1' = success, '2' = pending, '3' = failed
        if ($status !== '1') {
            return response('Ignored: status ' . $status, 200);
        }

        // Find order by ID first, fallback to bill code
        $order = $orderId
            ? Order::find($orderId)
            : Order::where('payment_bill_code', $billCode)->first();

        if (! $order) {
            $order = Order::where('payment_bill_code', $billCode)->first();
        }

        if (! $order) {
            Log::warning('ToyyibPay webhook: order not found', compact('orderId', 'billCode'));
            return response('Order not found', 404);
        }

        // Amount verification (in sen)
        if ($amountInSen !== $this->toSen($order->final_amount)) {
            Log::warning('ToyyibPay webhook: amount mismatch', [
                'expected' => $this->toSen($order->final_amount),
                'received' => $amountInSen,
            ]);
            return response('Amount mismatch', 422);
        }

        $this->markOrderAsPaid($order, $transactionId ?: $billCode);

        return response('OK', 200);
    }

    // =========================================================================
    // Billplz Logic
    // =========================================================================

    private function redirectToBillplz(Order $order): RedirectResponse
    {
        $config = config('payment.billplz', []);

        $host         = $config['base_url'] ?? null;
        $collectionId = $config['collection_id'] ?? null;
        $apiKey       = $config['api_key'] ?? null;

        if (! $host || ! $collectionId || ! $apiKey) {
            return back()->with('error', 'Billplz is not fully configured.');
        }

        $url = rtrim($host, '/') . '/bills?collection_id=' . urlencode($collectionId)
             . '&amount=' . $this->toSen($order->final_amount);

        return redirect()->away($url);
    }

    private function handleBillplzWebhook(Request $request): Response
    {
        Log::info('Billplz webhook received', $request->all());

        $reference = (string) $request->input('reference');
        $paid      = $request->boolean('paid');

        $order = $reference
            ? Order::where('payment_ref', $reference)->first()
            : null;

        if (! $order) {
            $orderId = $request->integer('order_id');
            $order   = $orderId ? Order::find($orderId) : null;
        }

        if (! $order) {
            return response('Order not found', 404);
        }

        if ($paid) {
            $this->markOrderAsPaid($order, $reference ?: null);
            return response('OK', 200);
        }

        return response('Ignored', 200);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Convert RM to sen (e.g. 12.50 → 1250).
     */
    private function toSen(float|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function markOrderAsPaid(Order $order, ?string $paymentRef = null): void
    {
        if ($order->status === 'paid') {
            return;
        }

        $order->update([
            'status'      => 'paid',
            'payment_ref' => $paymentRef,
            'paid_at'     => now(),
        ]);

        Log::info('Order marked as paid', [
            'order_id'    => $order->id,
            'payment_ref' => $paymentRef,
        ]);
    }
}
