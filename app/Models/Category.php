<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'feature_id', // New
        'name',       // আগের name কলাম (English Name হিসেবেও ব্যবহার হতে পারে)
        'english_name', // New
        'bangla_name', // New
        'slug',
        'image',
        'color',      // New
        'serial',     // New
        'status',
    ];

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            // Priority: English Name > Name > Bangla Name
            $name = $category->english_name ?: ($category->name ?: $category->bangla_name);
            $category->slug = static::createUniqueSlug($name);
        });

        static::updating(function ($category) {
            if ($category->isDirty('english_name') || $category->isDirty('name') || $category->isDirty('bangla_name')) {
                 $name = $category->english_name ?: ($category->name ?: $category->bangla_name);
                 $category->slug = static::createUniqueSlug($name, $category->id);
            }
        });
    }

    private static function createUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        return $slug;
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    
    // Feature রিলেশন
    public function feature()
    {
        return $this->belongsTo(Feature::class, 'feature_id');
    }
}