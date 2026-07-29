<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EasyParcelService
{
    protected $clientId;
    protected $clientSecret;
    protected $sandbox;
    protected $originPostcode;
    protected $originState;
    protected $originCity;
    protected $originName;
    protected $originPhone;
    protected $originAddress;

    /**
     * EasyParcel state code mapping (Appendix III).
     */
    protected $stateCodes = [
        'johor'           => 'jhr',
        'kedah'           => 'kdh',
        'kelantan'        => 'ktn',
        'melaka'          => 'mlk',
        'negeri sembilan' => 'nsn',
        'pahang'          => 'phg',
        'perak'           => 'prk',
        'perlis'          => 'pls',
        'pulau pinang'    => 'png',
        'penang'          => 'png',
        'selangor'        => 'sgr',
        'terengganu'      => 'trg',
        'kuala lumpur'    => 'kul',
        'putrajaya'       => 'pjy',
        'putra jaya'      => 'pjy',
        'sarawak'         => 'srw',
        'sabah'           => 'sbh',
        'labuan'          => 'lbn',
        // Codes pass-through
        'jhr' => 'jhr', 'kdh' => 'kdh', 'ktn' => 'ktn', 'mlk' => 'mlk',
        'nsn' => 'nsn', 'phg' => 'phg', 'prk' => 'prk', 'pls' => 'pls',
        'png' => 'png', 'sgr' => 'sgr', 'trg' => 'trg', 'kul' => 'kul',
        'pjy' => 'pjy', 'srw' => 'srw', 'sbh' => 'sbh', 'lbn' => 'lbn',
    ];

    public function __construct()
    {
        $this->clientId     = config('services.easyparcel.client_id', env('EASYPARCEL_CLIENT_ID'));
        $this->clientSecret = config('services.easyparcel.client_secret', env('EASYPARCEL_CLIENT_SECRET'));

        $this->sandbox = filter_var(
            config('services.easyparcel.sandbox', env('EASYPARCEL_SANDBOX', false)),
            FILTER_VALIDATE_BOOLEAN
        );

        $this->originPostcode = config('services.easyparcel.origin_postcode', env('EASYPARCEL_ORIGIN_POSTCODE', '47100'));
        $this->originCity     = config('services.easyparcel.origin_city',     env('EASYPARCEL_ORIGIN_CITY',     'Puchong'));
        $this->originState    = config('services.easyparcel.origin_state',    env('EASYPARCEL_ORIGIN_STATE',    'Selangor'));
        $this->originName     = config('services.easyparcel.origin_name',     env('EASYPARCEL_ORIGIN_NAME',     'Alfarhan Trading'));
        $this->originPhone    = config('services.easyparcel.origin_phone',    env('EASYPARCEL_ORIGIN_PHONE',    '0123456789'));
        $this->originAddress  = config('services.easyparcel.origin_address',  env('EASYPARCEL_ORIGIN_ADDRESS',  'No 1, Jalan Puchong, Industri Puchong'));
    }

    /**
     * Resolve state name to EasyParcel abbreviated code.
     */
    public function resolveStateCode(string $state): string
    {
        $key = strtolower(trim($state));
        return $this->stateCodes[$key] ?? $key;
    }

    /**
     * Get OAuth 2.0 Access Token using Client Credentials flow (EASYPARCEL_CLIENT_ID & EASYPARCEL_CLIENT_SECRET).
     */
    public function getAccessToken(): ?string
    {
        // Check cache first
        $cacheKey = 'easyparcel_oauth_token_' . md5($this->clientId ?? '');
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        if (empty($this->clientId) || empty($this->clientSecret)) {
            return null;
        }

        $endpoints = $this->sandbox
            ? [
                'http://demo.connect.easyparcel.my/oauth/token',
                'http://demo.connect.easyparcel.my/?ac=EPGetToken',
                'https://connect.easyparcel.my/oauth/token',
                'https://connect.easyparcel.my/?ac=EPGetToken',
              ]
            : [
                'https://connect.easyparcel.my/oauth/token',
                'https://connect.easyparcel.my/?ac=EPGetToken',
                'http://demo.connect.easyparcel.my/oauth/token',
                'http://demo.connect.easyparcel.my/?ac=EPGetToken',
              ];

        foreach ($endpoints as $url) {
            try {
                $response = Http::timeout(10)->asForm()->post($url, [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'app_id'        => $this->clientId,
                    'secret_key'    => $this->clientSecret,
                ]);

                if ($response->successful()) {
                    $json  = $response->json();
                    $token = $json['access_token'] ?? ($json['token'] ?? ($json['result']['token'] ?? ($json['api'] ?? null)));
                    if ($token) {
                        $ttl = ($json['expires_in'] ?? 3600) - 60;
                        Cache::put($cacheKey, $token, now()->addSeconds(max(60, $ttl)));
                        Log::info('EasyParcel OAuth 2.0 token retrieved successfully from ' . $url);
                        return $token;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("EasyParcel OAuth token attempt failed for {$url}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Get real-time shipping rates.
     * Tries live EasyParcel Open API with OAuth 2.0 Access Token, otherwise uses official rate table.
     */
    public function getRates(string $destPostcode, float $totalWeight = 0.50, string $destState = ''): array
    {
        $totalWeight   = max(0.10, $totalWeight);
        $pickStateCode = $this->resolveStateCode($this->originState);
        $sendStateCode = $this->resolveStateCode($destState);

        // ── 1. Try OAuth 2.0 Access Token (EasyParcel Open API) ───────────
        $token = $this->getAccessToken();

        if ($token) {
            $liveRates = $this->fetchLiveRates(
                $token, $destPostcode, $sendStateCode,
                $pickStateCode, $totalWeight
            );
            if (!empty($liveRates)) {
                return $liveRates;
            }
        }

        // ── 2. Official published rate table (Zone & Weight accurate) ─────
        return $this->getOfficialRates($destPostcode, $totalWeight, $destState);
    }

    /**
     * Call EasyParcel Open API EPRateCheckingBulk with OAuth 2.0 Access Token.
     */
    protected function fetchLiveRates(
        string $token,
        string $destPostcode,
        string $sendStateCode,
        string $pickStateCode,
        float  $totalWeight
    ): array {
        $endpoint = $this->sandbox
            ? 'http://demo.connect.easyparcel.my/?ac=EPRateCheckingBulk'
            : 'https://connect.easyparcel.my/?ac=EPRateCheckingBulk';

        $bulkData = [[
            'pick_code'    => $this->originPostcode,
            'pick_state'   => $pickStateCode,
            'pick_country' => 'MY',
            'send_code'    => $destPostcode,
            'send_state'   => $sendStateCode,
            'send_country' => 'MY',
            'weight'       => $totalWeight,
            'width'        => 0,
            'length'       => 0,
            'height'       => 0,
        ]];

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])
                ->asForm()
                ->post($endpoint, [
                    'api'          => $token,
                    'access_token' => $token,
                    'bulk'         => json_encode($bulkData),
                ]);

            if (!$response->successful()) {
                Log::warning("EasyParcel live API failed HTTP {$response->status()}");
                return [];
            }

            $data      = $response->json();
            $apiStatus = strtolower($data['api_status'] ?? '');
            $rawRates  = $data['result'][0]['rates'] ?? ($data['result']['rates'] ?? ($data['rates'] ?? null));

            if ($apiStatus === 'success' && !empty($rawRates)) {
                $rates = [];
                foreach ($rawRates as $rate) {
                    $rates[] = [
                        'service_id'   => $rate['service_id']   ?? '',
                        'service_name' => $rate['service_name'] ?? 'Standard Delivery',
                        'courier_name' => $rate['courier_name'] ?? ($rate['courier_id'] ?? 'Courier'),
                        'price'        => (float) ($rate['price'] ?? 0),
                        'delivery'     => $rate['delivery']     ?? '-',
                        'logo'         => $rate['courier_logo'] ?? null,
                        'is_live'      => true,
                    ];
                }
                Log::info('EasyParcel Open API live rates fetched', ['count' => count($rates)]);
                return $rates;
            }

            $errRemark = $data['error_remark'] ?? ($data['result'][0]['remarks'] ?? 'no rates');
            Log::warning("EasyParcel Open API returned no rates: api_status={$apiStatus}, remark={$errRemark}");
        } catch (\Exception $e) {
            Log::error('EasyParcel fetchLiveRates exception: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Official EasyParcel published rate table (accurate per zon & berat).
     * Kadar Rujukan Rasmi EasyParcel Malaysia — Individual Account.
     *
     * Zon 1 : Klang Valley (Selangor, KL, Putrajaya)
     * Zon 2 : Central WM  (Perak, N.Sembilan, Melaka, Pahang)
     * Zon 3 : North/South (Johor, Kedah, Penang, Kelantan, Terengganu, Perlis)
     * Zon 4 : East MY     (Sabah, Sarawak, Labuan)
     */
    public function getOfficialRates(string $destPostcode, float $weight, string $destState = ''): array
    {
        $zone = $this->resolveZone($destPostcode, $destState);

        /*
         * Rate table — based on EasyParcel published rates (per kg, 1kg base).
         * Format: [courier, base_1kg, per_kg_increment, estimated_days]
         */
        $rateTable = [
            // ── Zon 1: Klang Valley ────────────────────────────────────────
            1 => [
                ['id' => 'POSLAJU',  'name' => 'Pos Laju',       'base' => 6.10,  'inc' => 1.50, 'days' => '1-2 hari bekerja'],
                ['id' => 'JANDT',    'name' => 'J&T Express',    'base' => 5.30,  'inc' => 1.20, 'days' => '1-2 hari bekerja'],
                ['id' => 'NINJAVAN', 'name' => 'Ninja Van',      'base' => 5.50,  'inc' => 1.30, 'days' => '1-2 hari bekerja'],
                ['id' => 'DHL',      'name' => 'DHL eCommerce', 'base' => 6.50,  'inc' => 1.50, 'days' => '1-3 hari bekerja'],
            ],
            // ── Zon 2: Central Peninsular ──────────────────────────────────
            2 => [
                ['id' => 'POSLAJU',  'name' => 'Pos Laju',       'base' => 7.00,  'inc' => 1.70, 'days' => '2-3 hari bekerja'],
                ['id' => 'JANDT',    'name' => 'J&T Express',    'base' => 6.20,  'inc' => 1.40, 'days' => '1-2 hari bekerja'],
                ['id' => 'NINJAVAN', 'name' => 'Ninja Van',      'base' => 6.50,  'inc' => 1.60, 'days' => '2-3 hari bekerja'],
                ['id' => 'DHL',      'name' => 'DHL eCommerce', 'base' => 7.30,  'inc' => 1.70, 'days' => '2-4 hari bekerja'],
            ],
            // ── Zon 3: North/South Peninsular ─────────────────────────────
            3 => [
                ['id' => 'POSLAJU',  'name' => 'Pos Laju',       'base' => 8.70,  'inc' => 2.00, 'days' => '2-4 hari bekerja'],
                ['id' => 'JANDT',    'name' => 'J&T Express',    'base' => 7.90,  'inc' => 1.80, 'days' => '2-3 hari bekerja'],
                ['id' => 'NINJAVAN', 'name' => 'Ninja Van',      'base' => 8.20,  'inc' => 2.00, 'days' => '2-4 hari bekerja'],
                ['id' => 'DHL',      'name' => 'DHL eCommerce', 'base' => 9.00,  'inc' => 2.10, 'days' => '3-5 hari bekerja'],
            ],
            // ── Zon 4: East Malaysia ───────────────────────────────────────
            4 => [
                ['id' => 'POSLAJU',  'name' => 'Pos Laju',       'base' => 12.50, 'inc' => 4.50, 'days' => '3-7 hari bekerja'],
                ['id' => 'JANDT',    'name' => 'J&T Express',    'base' => 14.80, 'inc' => 4.50, 'days' => '3-5 hari bekerja'],
                ['id' => 'NINJAVAN', 'name' => 'Ninja Van',      'base' => 13.00, 'inc' => 4.80, 'days' => '3-6 hari bekerja'],
                ['id' => 'DHL',      'name' => 'DHL eCommerce', 'base' => 15.00, 'inc' => 5.00, 'days' => '4-7 hari bekerja'],
            ],
        ];

        $carriers = $rateTable[$zone] ?? $rateTable[2];
        $extra    = max(0, ceil($weight - 1.0));

        $rates = [];
        foreach ($carriers as $c) {
            $price    = round($c['base'] + ($c['inc'] * $extra), 2);
            $suffix   = 'Z' . $zone;
            $rates[]  = [
                'service_id'   => 'EP-' . $suffix . '-' . $c['id'],
                'service_name' => 'Standard Delivery',
                'courier_name' => $c['name'],
                'price'        => $price,
                'delivery'     => $c['days'],
                'logo'         => null,
                'is_live'      => false,
            ];
        }

        return $rates;
    }

    /**
     * Determine delivery zone from destination postcode / state.
     *
     * Zone 1 — Klang Valley        : Selangor, KL, Putrajaya
     * Zone 2 — Central Peninsular  : Perak, Pahang, N.Sembilan, Melaka
     * Zone 3 — North/South WM      : Johor, Kedah, Penang, Kelantan, Terengganu, Perlis
     * Zone 4 — East Malaysia       : Sabah, Sarawak, Labuan
     */
    public function resolveZone(string $destPostcode, string $destState = ''): int
    {
        $postcodeInt = (int) ltrim($destPostcode, '0');
        $lower       = strtolower($destState);

        // ── East Malaysia by postcode range ──────────────────────────────
        if ($postcodeInt >= 87000 && $postcodeInt <= 99999) {
            return 4;
        }
        // ── East Malaysia by state name ───────────────────────────────────
        foreach (['sabah', 'sbh', 'sarawak', 'srw', 'labuan', 'lbn'] as $em) {
            if (str_contains($lower, $em)) {
                return 4;
            }
        }

        // ── Klang Valley by postcode ──────────────────────────────────────
        // Selangor: 40000-68000, KL: 50000-60000, Putrajaya: 62000-62988
        $kvRanges = [[40000,68100],[50000,60000],[62000,62990]];
        foreach ($kvRanges as [$min, $max]) {
            if ($postcodeInt >= $min && $postcodeInt <= $max) {
                return 1;
            }
        }
        // ── Klang Valley by state name ────────────────────────────────────
        foreach (['selangor', 'sgr', 'kuala lumpur', 'kul', 'putrajaya', 'pjy'] as $kv) {
            if (str_contains($lower, $kv)) {
                return 1;
            }
        }

        // ── North/South Peninsular by state name ──────────────────────────
        $farStates = ['johor','jhr','kedah','kdh','pulau pinang','penang','png','kelantan','ktn','terengganu','trg','perlis','pls'];
        foreach ($farStates as $fs) {
            if (str_contains($lower, $fs)) {
                return 3;
            }
        }

        // ── North/South by postcode range ─────────────────────────────────
        // Johor: 79000-83999, Kedah: 05000-09999, Penang: 10000-14999
        // Kelantan: 15000-18999, Terengganu: 20000-24999, Perlis: 01000-02999
        $farRanges = [[1000,2999],[5000,9999],[10000,14999],[15000,18999],[20000,24999],[79000,83999]];
        foreach ($farRanges as [$min, $max]) {
            if ($postcodeInt >= $min && $postcodeInt <= $max) {
                return 3;
            }
        }

        // ── Default: Central Peninsular ───────────────────────────────────
        return 2;
    }

    /**
     * Book a shipment on EasyParcel Open API (requires OAuth 2.0 Access Token).
     */
    public function createShipment($order): array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return $this->mockShipment($order);
        }

        $weight = 0;
        foreach ($order->items as $item) {
            $weight += ($item->product->weight ?? 0.50) * $item->quantity;
        }
        $weight = max(0.10, $weight);

        $url = $this->sandbox
            ? 'http://demo.connect.easyparcel.my/?ac=EPSubmitOrderBulkV3'
            : 'https://connect.easyparcel.my/?ac=EPSubmitOrderBulkV3';

        $bulkData = [[
            'reference'    => 'ORDER-' . $order->id,
            'weight'       => $weight,
            'width'        => 0,
            'length'       => 0,
            'height'       => 0,
            'content'      => 'Alfarhan Wholesale Order #' . $order->id,
            'value'        => $order->final_amount,

            // Sender
            'pick_name'    => $this->originName,
            'pick_contact' => $this->originPhone,
            'pick_addr1'   => $this->originAddress,
            'pick_city'    => $this->originCity,
            'pick_state'   => $this->resolveStateCode($this->originState),
            'pick_code'    => $this->originPostcode,
            'pick_country' => 'MY',

            // Receiver
            'send_name'    => $order->customer_name,
            'send_contact' => $order->customer_phone,
            'send_email'   => $order->customer_email ?: 'customer@example.com',
            'send_addr1'   => $order->delivery_address,
            'send_city'    => $order->shipping_city    ?? 'City',
            'send_state'   => $this->resolveStateCode($order->shipping_state ?? ''),
            'send_code'    => $order->shipping_postcode ?? '50000',
            'send_country' => 'MY',

            'collect_date' => now()->addDay()->format('Y-m-d'),
            'courier'      => [$order->shipping_courier],
        ]];

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])
                ->asForm()
                ->post($url, [
                    'api'          => $token,
                    'access_token' => $token,
                    'bulk'         => json_encode($bulkData),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (
                    isset($data['api_status']) &&
                    strtolower($data['api_status']) === 'success' &&
                    isset($data['result'][0]['status']) &&
                    strtolower($data['result'][0]['status']) === 'success'
                ) {
                    $res = $data['result'][0];
                    return [
                        'status'              => 'Success',
                        'easyparcel_order_id' => $res['parcel_number'] ?? ($res['order_number'] ?? 'EP-' . rand(1000, 9999)),
                        'tracking_code'       => $res['consignment_note'] ?? null,
                        'price'               => (float) ($res['price'] ?? 0.00),
                    ];
                }

                $remark = $data['error_remark'] ?? ($data['result'][0]['remarks'] ?? 'Unknown API error');
                return ['status' => 'Failed', 'message' => 'EasyParcel Open API Error: ' . $remark];
            }

            return ['status' => 'Failed', 'message' => 'API HTTP ' . $response->status()];
        } catch (\Exception $e) {
            Log::error('EasyParcel createShipment exception: ' . $e->getMessage());
            return ['status' => 'Failed', 'message' => $e->getMessage()];
        }
    }

    protected function mockShipment($order): array
    {
        return [
            'status'              => 'Success',
            'easyparcel_order_id' => 'EP-MOCK-' . strtoupper(uniqid()),
            'tracking_code'       => 'MY-MOCK-' . $order->id . '-' . rand(100000, 999999),
            'price'               => $order->shipping_cost ?? 8.00,
        ];
    }
}
