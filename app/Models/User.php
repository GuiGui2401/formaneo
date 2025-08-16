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
        'name', 'email', 'phone', 'password', 'profile_image_url',
        'balance', 'available_for_withdrawal', 'pending_withdrawals',
        'promo_code', 'affiliate_link', 'total_affiliates', 'monthly_affiliates', 
        'referred_by', 'total_commissions', 'free_quizzes_left', 'total_quizzes_taken',
        'passed_quizzes', 'is_active', 'is_premium', 'metadata', 'settings', 'last_login_at'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    protected $casts = [
        'metadata' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
        'last_login_at' => 'datetime',
        'balance' => 'decimal:2',
        'available_for_withdrawal' => 'decimal:2',
        'pending_withdrawals' => 'decimal:2',
        'total_commissions' => 'decimal:2',
    ];

    // Relations
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function affiliateLink()
    {
        return $this->hasOne(AffiliateLink::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function formationProgress()
    {
        return $this->hasMany(FormationProgress::class);
    }

    public function formationNotes()
    {
        return $this->hasMany(FormationNote::class);
    }

    public function userPacks()
    {
        return $this->hasMany(UserPack::class);
    }

    public function ownedPacks()
    {
        return $this->belongsToMany(FormationPack::class, 'user_packs', 'user_id', 'pack_id')
                    ->withTimestamps();
    }

    public function quizResults()
    {
        return $this->hasMany(QuizResult::class);
    }

    // Accesseurs
    public function getFormattedBalanceAttribute()
    {
        return number_format($this->balance, 2) . ' FCFA';
    }

    // Méthodes utilitaires
    public function hasPackAccess($packId)
    {
        return $this->userPacks()->where('pack_id', $packId)->exists();
    }

    public function getTotalEarnings()
    {
        return $this->transactions()
            ->whereIn('type', ['commission', 'bonus', 'cashback', 'quiz_reward'])
            ->sum('amount');
    }

    public function getMonthlyEarnings($month = null, $year = null)
    {
        $query = $this->transactions()
            ->whereIn('type', ['commission', 'bonus', 'cashback', 'quiz_reward']);
            
        if ($month) {
            $query->whereMonth('created_at', $month);
        }
        
        if ($year) {
            $query->whereYear('created_at', $year);
        }
        
        return $query->sum('amount');
    }

    public function getActiveReferrals()
    {
        return $this->referrals()
            ->where('last_login_at', '>', now()->subDays(30))
            ->count();
    }

    public function canWithdraw($amount)
    {
        $availableBalance = max(0, $this->balance - 1000); // Garder 1000 FCFA minimum
        return $amount <= $availableBalance;
    }

    public function hasCompletedFormation($formationId)
    {
        return $this->formationProgress()
            ->where('formation_id', $formationId)
            ->where('progress', 100)
            ->exists();
    }

    public function getAverageQuizScore()
    {
        return $this->quizResults()->avg('score') ?: 0;
    }
}