<?php

namespace App\PaymentChannels\Drivers\Stripe;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $api_key;
    protected $api_secret;
    protected $order_session_key;

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency();

        $this->api_key = env('STRIPE_KEY');
        $this->api_secret = env('STRIPE_SECRET');

        $this->order_session_key = 'strip.payments.order_id';
    }

    public function paymentRequest(Order $order)
    {
        $price = $this->makeAmountByCurrency($order->total_amount, $this->currency);
        $generalSettings = getGeneralSettings();
        $currency = currency();

        Stripe::setApiKey($this->api_secret);
        $checkout = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' =>$currency,
                    'unit_amount_decimal' => $price * 100,
                    'product_data' => [
                        'name' => $generalSettings['site_name'] . ' payment',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => auth()->user()->email, // Pass the email address here
            'success_url' => $this->makeCallbackUrl('success',$order->id),
            'cancel_url' => $this->makeCallbackUrl('cancel',$order->id),
        ]);
        // dd($checkout);
                /*$order->update([
                    'reference_id' => $checkout->id,
                ]);*/

        // session()->put($this->order_session_key, $order->id);

        $Html = '<script src="https://js.stripe.com/v3/"></script>';
        $Html .= '<script type="text/javascript">let stripe = Stripe("' . $this->api_key . '");';
        $Html .= 'stripe.redirectToCheckout({ sessionId: "' . $checkout->id . '" }); </script>';

        echo $Html;
    }

    private function makeCallbackUrl($status,$order_id)
    {
        return url("/payments/verify/Stripe?status=$status&order_id=$order_id&session_id={CHECKOUT_SESSION_ID}");
    }

    public function verify(Request $request)
    {
        $data = $request->all();
        $status = $data['status'];
        $order_id = $data['order_id'] ?? null;

        // $order_id = session()->get($this->order_session_key, null);
        // session()->forget($this->order_session_key);

        $user = auth()->user();

        $order = Order::where('id', $order_id)
            ->where('user_id', $user->id)
            ->first();
        // dd($request->session_id);
        if ($status == 'success' and !empty($request->session_id) and !empty($order)) {
            Stripe::setApiKey($this->api_secret);

            $session = Session::retrieve($request->session_id);
            // dd($session);
            // dd($session);
            if (!empty($session) and $session->payment_status == 'paid') {
                $order->update([
                    'status' => Order::$paying,
                    'payment_email' => $session->customer_details->email ?? $session->customer_email
                ]);

                return $order;
            }
        }

        // is fail

        if (!empty($order)) {
            $order->update(['status' => Order::$fail]);
        }

        return $order;
    }
}
