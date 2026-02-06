<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BookCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en', 
        'name_bn', 
        'slug', 
        'serial', 
        'status'
    ];

    /**
     * Boot function to handle slug generation during creating and updating.
     */
    protected static function boot()
    {
        parent::boot();

        // ডাটা ক্রিয়েট করার সময় স্লাগ জেনারেশন
        static::creating(function ($model) {
            $name = $model->name_en ?: $model->name_bn;
            $model->slug = static::createUniqueSlug($name);
        });

        // ডাটা আপডেট করার সময় যদি নাম পরিবর্তন হয় তবে স্লাগ আপডেট
        static::updating(function ($model) {
            if ($model->isDirty('name_en') || $model->isDirty('name_bn')) {
                $name = $model->name_en ?: $model->name_bn;
                $model->slug = static::createUniqueSlug($name, $model->id);
            }
        });
    }

    /**
     * Unique Slug জেনারেট করার কাস্টম ফাংশন।
     * যদি স্লাগটি ডাটাবেসে অলরেডি থাকে, তবে শেষে সংখ্যা (-1, -2) যোগ করবে।
     */
    private static function createUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        // স্লাগটি ইউনিক কিনা তা লুপের মাধ্যমে চেক করা হচ্ছে
        while (static::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        return $slug;
    }
}