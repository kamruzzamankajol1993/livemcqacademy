<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureList extends Model
{
    protected $fillable = ['name', 'code', 'status'];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'feature_list_package', 'feature_list_id', 'package_id')
                    ->withPivot('value')
                    ->withTimestamps();
    }
}