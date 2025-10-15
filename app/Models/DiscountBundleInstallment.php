<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountBundleInstallment extends Model
{
    protected $table = 'discount_bundle_installment';
    public $timestamps = false;

    protected $guarded = ['id'];

    public function discount()
    {
        return $this->belongsTo('App\Models\Discount', 'discount_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo('App\Models\Bundle', 'bundle_id', 'id');
    }
}
