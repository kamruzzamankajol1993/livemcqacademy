<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id','subject_id', 'section_id', 'name_en', 'name_bn', 'slug', 'serial', 'status'
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

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}