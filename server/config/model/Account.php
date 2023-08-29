<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $dateFormat = 'U';

    public function followers(): HasMany
    {
      return $this->belongsToMany(Account::class, 'followers', 'subject_id', 'follower_id');
    }
    public function follows(): BelongsToMany
    {
      return $this->belongsToMany(Account::class, 'followers', 'follower_id', 'subject_id');
    }
    public function subscribers(): HasMany
    {
      return $this->belongsToMany(Account::class, 'subscribers', 'subscription_id', 'subscriber_id');
    }
    public function subscriptions(): HasMany
    {
      return $this->belongsToMany(Account::class, 'subscribers', 'subscriber_id', 'subscription_id');
    }
    public function donations(): HasMany
    {
      return $this->belongsToMany(Account::class, 'donations', 'donor_id', 'fund_id');
    }
    public function donors(): HasMany
    {
      return $this->belongsToMany(Account::class, 'donations', 'fund_id', 'donor_id');
    }
    public function merch_sales(): HasMany
    {
      return $this->belongsToMany(Account::class, 'merchant_id', 'customer_id');
    }
    public function purchases(): HasMany
    {
      return $this->belongsToMany(Account::class, 'customer_id', 'merchant_id');
    }
}
