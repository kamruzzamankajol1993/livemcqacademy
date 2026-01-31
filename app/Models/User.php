<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens,HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'image',
        'branch_id',
        'customer_id',
        'designation_id',
        'is_shareholder',
        'status',
        'old_id',
        'phone',
        'secondary_phone',
        'address',
        'email',
        'type',
        'user_type',
        'password',
        'viewpassword',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function customer()
{
   return $this->hasOne(Customer::class, 'user_id', 'id');
}

// ১. পেমেন্ট হিস্ট্রি (সব পেমেন্ট)
    public function payments()
    {
        return $this->hasMany(Payment::class)->orderBy('created_at', 'desc');
    }

    // ২. সাবস্ক্রিপশন হিস্ট্রি (সব প্যাকেজ যা সে নিয়েছে)
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class)->orderBy('created_at', 'desc');
    }

    // ৩. বর্তমান অ্যাক্টিভ সাবস্ক্রিপশন (যদি থাকে)
    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)
                    ->where('status', 'active')
                    ->where('end_date', '>', now())
                    ->latest();
    }
    
    // ৪. চেক করা ইউজার প্রিমিয়াম কিনা
    public function isPremium()
    {
        return $this->activeSubscription()->exists();
    }
}
