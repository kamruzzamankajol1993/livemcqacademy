<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // রিলেশনশিপ
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // স্কোপ: শুধুমাত্র অ্যাক্টিভ সাবস্ক্রিপশন বের করার জন্য
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('end_date', '>', Carbon::now());
    }
}