<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name', 'slug', 'type', 'original_price', 
        'price', 'is_popular', 'status'
    ];

    public function features()
    {
        return $this->belongsToMany(FeatureList::class, 'feature_list_package', 'package_id', 'feature_list_id')
                    ->withPivot('value')
                    ->withTimestamps();
    }
}