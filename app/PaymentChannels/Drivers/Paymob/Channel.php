<?php

namespace App\PaymentChannels\Drivers\Paymob;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;

class Channel extends BasePaymentChannel implements IChannel
{
    // بدون typed properties علشان توافق PHP أقدم من 7.4
    protected $base;
    protected $publicKey;
    protected $secretKey;
    protected $currency;
    protected $integrationIds = [];

    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->base      = rtrim((string) env('PAYMOB_KSA_BASE', 'https://ksa.paymob.com'), '/');
        $this->publicKey = (string) env('PAYMOB_PUBLIC_KEY', '');
        $this->secretKey = (string) env('PAYMOB_SECRET_KEY', '');
        $this->currency  = (string) env('PAYMOB_CURRENCY', 'SAR');

        $ids = (string) env('PAYMOB_INTEGRATION_ID', '');

        // استبدال fn Arrow Function بـ function عادية
        $this->integrationIds = array_values(
            array_filter(
                array_map('intval', array_map('trim', explode(',', $ids))),
                function ($v) { return $v > 0; }
            )
        );
    }

    // كمان هنشيل Return Type للتوافق (لو حابة سيبيه، بس لو فيه مشكلة شيليه)
    public function paymentRequest(Order $order)
    {
        if ($this->publicKey === '' || $this->secretKey === '') {
            throw new \RuntimeException('PAYMOB_PUBLIC_KEY / PAYMOB_SECRET_KEY missing.');
        }
        if (empty($this->integrationIds)) {
            throw new \RuntimeException('PAYMOB_INTEGRATION_ID is missing.');
        }

        $items = [];
        foreach ($order->orderItems as $oi) {
            $amount = $this->toCents($oi->total_amount ?? $oi->amount ?? 0);
            $items[] = [
                'name'        => 'Item #'.$oi->id,
                'amount'      => $amount,
                'description' => 'Order Item '.$oi->id,
                'quantity'    => 1,
            ];
        }

        $user = $order->user;
        list($first, $last) = $this->splitName((string) ($user->full_name ?? ''));

     $amountCents = $this->toCents($order->total_amount);

// عنصر واحد = إجمالي الطلب
$items = [[
    'name'        => 'Order #'.$order->id,
    'amount'      => $amountCents,
    'description' => 'Order Total',
    'quantity'    => 1,
]];

$billing = [
  'apartment'    => '1',
  'first_name'   => $first ?: 'Test',
  'last_name'    => $last ?: 'User',
  'street'       => 'King Fahd Rd',
  'building'     => '10',
  'phone_number' => $user->mobile ?: '+966500000000',
  'city'         => 'Riyadh',
  'country'      => 'SA',
  'email'        => $user->email ?: 'customer@test.com',
  'floor'        => '2',
  'state'        => 'Riyadh',
];


// استخدمي ID واحد بس مؤقتًا (بطاقات)
$cardIntegrationId = $this->integrationIds[0];

$payload = [
    'amount'            => $amountCents,
    'currency'          => $this->currency,
    'payment_methods'   => [$cardIntegrationId],
    'items'             => $items,
    'billing_data'      => $billing,
    'special_reference' => (string) $order->id,
];

$redirectUrl = trim((string) env('PAYMOB_KSA_REDIRECT_URL', ''));
$notifyUrl   = trim((string) env('PAYMOB_KSA_NOTIFICATION_URL', ''));
if ($redirectUrl !== '')  { $payload['redirection_url']  = $redirectUrl; }
if ($notifyUrl !== '')    { $payload['notification_url'] = $notifyUrl; }

        $redirectUrl = trim((string) env('PAYMOB_KSA_REDIRECT_URL', ''));
        $notifyUrl   = trim((string) env('PAYMOB_KSA_NOTIFICATION_URL', ''));

        if ($redirectUrl !== '')  { $payload['redirection_url']  = $redirectUrl; }
        if ($notifyUrl !== '')    { $payload['notification_url'] = $notifyUrl; }

        $resp = Http::withHeaders([
            'Authorization' => 'Token '.$this->secretKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ])->post($this->base.'/v1/intention/', $payload);

        if (!$resp->successful()) {
            throw new \RuntimeException('Paymob intention failed: '.$resp->body());
        }

        $data = $resp->json();
        $clientSecret = (string) ($data['client_secret'] ?? '');
        if ($clientSecret === '') {
            throw new \RuntimeException('Missing client_secret from Paymob.');
        }

        $checkout = $this->base.'/unifiedcheckout/?publicKey='
            .urlencode($this->publicKey)
            .'&clientSecret='.urlencode($clientSecret);

        return Redirect::away($checkout);
    }

    public function verify(Request $request)
    {
        // لو بتستخدمي التحقق في الكنترولر، سيبيه فاضي/يرجع null
        return null;
    }

    private function toCents($amount)
    {
        return (int) round(((float) $amount) * 100);
    }

    private function splitName($name)
    {
        $name = trim((string) $name);
        if ($name === '') return [null, null];
        $p = preg_split('/\s+/', $name);
        $first = isset($p[0]) ? $p[0] : null;
        $last  = count($p) > 1 ? $p[count($p) - 1] : null;
        return [$first, $last];
    }

    /**
     * نفس ترتيب الحقول المعتمد من Paymob في الـ redirect (sha512).
     * ندعم الشكلين: source_data.type و source_data_type
     */
    private function validateRedirectHmac(array $data, string $secret): bool
    {
        $fields = [
            "amount_cents",
            "created_at",
            "currency",
            "error_occured",
            "has_parent_transaction",
            "id",
            "integration_id",
            "is_3d_secure",
            "is_auth",
            "is_capture",
            "is_refunded",
            "is_standalone_payment",
            "is_voided",
            "order",
            "owner",
            "pending",
            "source_data.pan",
            "source_data.sub_type",
            "source_data.type",
            "success",
        ];

        // نطبّع المفاتيح عشان ندعم source_data.type و source_data_type
        $normalized = $data;
        foreach ($data as $k => $v) {
            if (strpos($k, 'source_data.') === 0) {
                [$prefix, $sub] = explode('.', $k, 2);
                $normalized[$prefix][$sub] = $v;
            } elseif (strpos($k, 'source_data_') === 0) {
                $sub = substr($k, strlen('source_data_'));
                $normalized['source_data'][$sub] = $v;
            }
        }

        $get = function (array $arr, string $path): string {
            if (strpos($path, '.') === false) {
                return array_key_exists($path, $arr) ? (string) $arr[$path] : '';
            }
            $parts = explode('.', $path);
            $val = $arr;
            foreach ($parts as $p) {
                if (!is_array($val) || !array_key_exists($p, $val)) return '';
                $val = $val[$p];
            }
            return (string) $val;
        };

        $concat = '';
        foreach ($fields as $f) {
            $concat .= $get($normalized, $f);
        }

        $calc = hash_hmac('sha512', $concat, $secret);
        // Paymob عادةً يقارنوا lowercase، فنطبع الاتنين
        return hash_equals(strtolower($calc), strtolower((string) ($data['hmac'] ?? '')));
    }
}
