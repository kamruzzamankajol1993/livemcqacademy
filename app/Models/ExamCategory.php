<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamCategory extends Model
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
     * Model Boot Method
     * অটোমেটিক স্লাগ তৈরি করার জন্য
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $name = $model->name_en ?: $model->name_bn;
            $model->slug = static::createUniqueSlug($name);
        });

        static::updating(function ($model) {
            if ($model->isDirty('name_en') || $model->isDirty('name_bn')) {
                $name = $model->name_en ?: $model->name_bn;
                $model->slug = static::createUniqueSlug($name, $model->id);
            }
        });
    }

    /**
     * ইউনিক স্লাগ জেনারেটর
     */
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

    /**
     * স্কোপ কুয়েরি: শুধুমাত্র একটিভ ক্যাটাগরি পাওয়ার জন্য
     * ব্যবহার: ExamCategory::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}