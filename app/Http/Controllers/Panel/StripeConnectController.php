<?php

namespace App\Http\Controllers\panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Transfer;
use Stripe\Balance;

class StripeConnectController extends Controller
{
    public function createConnectAccount(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $account = Account::create([
            'type' => 'custom',
            'country' => 'US',
            'email' => $request->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'business_type' => 'individual',
            'individual' => [
                'first_name' => $request->first_name,
                'last_name' =>  $request->last_name,
                'email' => $request->email,
                'dob' => [
                    'day' => $request->day,
                    'month' => $request->month,
                    'year' => $request->year
                ],
                'address' => [
                    'line1' => '123 Main Street',
                    'city' => 'New York',
                    'state' => 'NY',
                    'postal_code' => '10001',
                    'country' => 'US',
                ],
            ],
            'tos_acceptance' => [
                'date' => time(),
                'ip' => request()->ip(),
            ],
        ]);

        return back()->with(['account_id' => $account->id]);
    }

    public function transferToConnectedAccount(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $connectedAccountId = $request->input('account_id');
        $amount = $request->input('amount');
        // dd($amount);

        try {
            $transfer = Transfer::create([
                'amount' => $amount,
                'currency' => 'usd',
                'destination' => $connectedAccountId,
                'description' => 'Payout to connected account',
            ]);

            return back()->with(['success' => 'Transfer successful' . ' - Transfer ID: ' . $transfer->id]);
        } catch (\Exception $e) {
            return back()->with(['success' => $e->getMessage()], 400);
        }
    }


    public function index()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $accounts = \Stripe\Account::all(['limit' => 100])->data;

        $balance = Balance::retrieve();
        $transfers = Transfer::all()->data;
        $available = $balance->available[0]->amount ?? 0;
        $pending = $balance->pending[0]->amount ?? 0;
        $currency = $balance->available[0]->currency ?? 'usd';

        $accountData = [];

        foreach ($accounts as $account) {
            $balance = null;

            if ($account->charges_enabled && $account->payouts_enabled) {
                try {
                    $balance = Balance::retrieve([], [
                        'stripe_account' => $account->id,
                    ]);
                } catch (\Exception $e) {
                    $balance = ['error' => $e->getMessage()];
                }
            }

            $accountData[] = [
                'id' => $account->id,
                'email' => $account->email,
                'first_name' => $account->individual->first_name ?? null,
                'last_name' => $account->individual->last_name ?? null,
                'dob' => $account->individual->dob ?? null,
                'available' => $balance?->available[0]->amount ?? 0,
                'currency' => $balance?->available[0]->currency ?? 'usd',
                'transfers'=>$transfers
            ];
        }

        return view('stripe_connect.index', [
            'accounts' => $accountData,
            'available' => $available,
            'pending' => $pending,
            'currency' => $currency,
        ]);
    }
}
