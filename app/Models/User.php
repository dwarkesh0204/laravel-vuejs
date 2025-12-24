<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
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
            'balance' => 'decimal:8',
        ];
    }

    /**
     * Get all assets owned by this user.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Get all orders placed by this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all trades where this user was the buyer.
     */
    public function buyTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'buyer_id');
    }

    /**
     * Get all trades where this user was the seller.
     */
    public function sellTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'seller_id');
    }

    /**
     * Get a specific asset by symbol.
     */
    public function getAsset(string $symbol): ?Asset
    {
        return $this->assets()->where('symbol', $symbol)->first();
    }

    /**
     * Get or create an asset for the given symbol.
     */
    public function getOrCreateAsset(string $symbol): Asset
    {
        return $this->assets()->firstOrCreate(
            ['symbol' => $symbol],
            ['amount' => 0, 'locked_amount' => 0]
        );
    }
}
