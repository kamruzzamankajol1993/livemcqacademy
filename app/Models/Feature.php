<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'bangla_name',
        'english_name',
        'slug',
        'color',
        'image',
        'short_description',
        'status',
    ];

    // Automatically generate slug from English Name
   protected static function boot()
    {
        parent::boot();

        static::creating(function ($feature) {
            // ইংলিশ নাম না থাকলে বাংলা নাম ব্যবহার হবে
            $name = $feature->english_name ?: $feature->bangla_name;
            $feature->slug = static::createUniqueSlug($name);
        });

        static::updating(function ($feature) {
            if ($feature->isDirty('english_name') || $feature->isDirty('bangla_name')) {
                 $name = $feature->english_name ?: $feature->bangla_name;
                 $feature->slug = static::createUniqueSlug($name, $feature->id);
            }
        });
    }

    // ইউনিক স্লাগ জেনারেটর মেথড
    private static function createUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        // চেক করবে এই স্লাগ ডাটাবেজে আছে কিনা (এডিট করার সময় নিজের ID বাদ দিবে)
        while (static::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function parent()
    {
        return $this->belongsTo(Feature::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Feature::class, 'parent_id');
    }
}