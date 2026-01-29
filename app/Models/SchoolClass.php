<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SchoolClass extends Model
{
    use HasFactory;
    
    // Table Name নির্দিষ্ট করে দেওয়া হলো
    protected $table = 'school_classes';

    protected $fillable = [
        'name_bn',
        'name_en',
        'slug',
        'image',
        'color',
        'serial',
        'status',
    ];

    // Slug Generator
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // English না থাকলে Bangla
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
    // Relation with Category (Many-to-Many)
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'assign_category_to_class', 'class_id', 'category_id');
    }


    public function departments()
{
    return $this->belongsToMany(ClassDepartment::class, 'assign_class_to_department', 'class_id', 'class_department_id');
}
}