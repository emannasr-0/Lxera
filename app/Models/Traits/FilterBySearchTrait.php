<?php

namespace App\Models\Traits;

trait FilterBySearchTrait
{
    public function scopeFilterBySearch($query, $filters)
    {
        return $query->where(function ($q) use ($filters) {
            foreach ($filters as $field => $value) {
                if (!empty($value)) {
                    $q->where($field, 'like', "%{$value}%");
                }
            }
        });
    }
}
