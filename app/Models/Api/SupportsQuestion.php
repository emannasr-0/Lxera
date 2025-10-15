<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportsQuestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'question',
        'answer',
        'status',
    ];
}
