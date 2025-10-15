<?php

namespace App\PaymentChannels\Drivers\Mada;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Paytabscom\Laravel_paytabs\Facades\paypage;

class Channel extends BasePaymentChannel implements IChannel
{

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
       
    }

    public function paymentRequest(Order $order)
    {
          
        try{
          $pay= paypage::sendPaymentCode('all')
                 ->sendTransaction('sale','ecom')
                  ->sendCart(10,1000,'test')
                 ->sendCustomerDetails('Walaa Elsaeed', 'w.elsaeed@paytabs.com', '0101111111', 'test', 'Nasr City', 'Cairo', 'EG', '1234','100.279.20.10')
                 ->sendShippingDetails('Walaa Elsaeed', 'w.elsaeed@paytabs.com', '0101111111', 'test', 'Nasr City', 'Cairo', 'EG', '1234','100.279.20.10')
                 ->sendURLs('', '')
                 ->sendLanguage('en')
                 ->create_pay_page();
          return $pay;
        }
        catch(Exception $e){
            dd($e);
        }
    }

    private function makeCallbackUrl($status)
    {
        
    }

    public function verify(Request $request)
    {
       
    }
}
