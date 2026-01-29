<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'name_en', 'name_bn', 'slug', 'color', 'icon', 'serial', 'status'
    ];

    // Auto Slug Generation
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

    // Relation: Parent Subject
    public function parent()
    {
        return $this->belongsTo(Subject::class, 'parent_id');
    }

    // Relation: Classes (Many-to-Many)
    // Note: 'school_classes' টেবিলটি আগের ধাপে তৈরি করা হয়েছিল, মডেল নাম SchoolClass
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'assign_class_to_subject', 'subject_id', 'class_id');
    }

    public function departments()
{
    return $this->belongsToMany(ClassDepartment::class, 'assign_subject_to_department', 'subject_id', 'class_department_id');
}
}