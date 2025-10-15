<?php

namespace App\Models\Translation;

use App\Models\Traits\FilterBySearchTrait;
use Illuminate\Database\Eloquent\Model;

class BundleTranslation extends Model
{
    use FilterBySearchTrait;

    protected $table = 'bundle_translations';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = ['id'];
}
