<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name','email','phone','password','profile_image_url',
        'balance','available_for_withdrawal','pending_withdrawals',
        'promo_code','affiliate_link','total_affiliates','monthly_affiliates','referred_by','total_commissions',
        'free_quizzes_left','total_quizzes_taken','is_active','is_premium','metadata','settings'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'metadata'=>'array',
        'settings'=>'array',
        'is_active'=>'boolean',
        'is_premium'=>'boolean',
    ];

    // relations
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function affiliateLink()
    {
        return $this->hasOne(AffiliateLink::class);
    }
}
