<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCharge extends Model
{
    use HasFactory;
   protected $table = "account_charge";
    public static $addiction = 'addiction';
    public static $deduction = 'deduction';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    public static function charge($order)
    {
        AccountCharge::create([
            'user_id' => $order->user_id,
            'creator_id' => auth()->user()->id,
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'type' => AccountCharge::$addiction,
            'description' => trans('public.charge_account'),
        ]);

        $accountChargeReward = RewardAccounting::calculateScore(Reward::ACCOUNT_CHARGE, $order->total_amount);
        RewardAccounting::makeRewardAccounting($order->user_id, $accountChargeReward, Reward::ACCOUNT_CHARGE);

        $chargeWalletReward = RewardAccounting::calculateScore(Reward::CHARGE_WALLET, $order->total_amount);
        RewardAccounting::makeRewardAccounting($order->user_id, $chargeWalletReward, Reward::CHARGE_WALLET);

        $notifyOptions = [
            '[u.name]' => $order->user->full_name,
            '[amount]' => handlePrice($order->total_amount),
        ];
        $financialUsers = User::where(['status' => 'active'])
        ->whereIn('role_id', Role::$financialRoles)->get();

        sendNotification('wallet_charge', $notifyOptions, user_id: $order->user_id);
        foreach ($financialUsers as $financialUser) {
            sendNotification('user_wallet_charge', $notifyOptions, $financialUser->id);
        }


        return true;
    }
}
