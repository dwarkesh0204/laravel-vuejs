<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * Order status constants.
     */
    public const STATUS_OPEN = 1;
    public const STATUS_FILLED = 2;
    public const STATUS_CANCELLED = 3;

    /**
     * Order side constants.
     */
    public const SIDE_BUY = 'buy';
    public const SIDE_SELL = 'sell';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'symbol',
        'side',
        'price',
        'amount',
        'filled_amount',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:8',
        'amount' => 'decimal:8',
        'filled_amount' => 'decimal:8',
        'status' => 'integer',
    ];

    /**
     * Get the user that owns this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the trades where this order was the buy order.
     */
    public function buyTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'buy_order_id');
    }

    /**
     * Get the trades where this order was the sell order.
     */
    public function sellTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'sell_order_id');
    }

    /**
     * Get the remaining amount to be filled.
     */
    public function getRemainingAmountAttribute(): string
    {
        return bcsub($this->amount, $this->filled_amount, 8);
    }

    /**
     * Check if the order is open.
     */
    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Check if the order is filled.
     */
    public function isFilled(): bool
    {
        return $this->status === self::STATUS_FILLED;
    }

    /**
     * Check if the order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if this is a buy order.
     */
    public function isBuyOrder(): bool
    {
        return $this->side === self::SIDE_BUY;
    }

    /**
     * Check if this is a sell order.
     */
    public function isSellOrder(): bool
    {
        return $this->side === self::SIDE_SELL;
    }

    /**
     * Scope a query to only include open orders.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope a query to only include buy orders.
     */
    public function scopeBuy($query)
    {
        return $query->where('side', self::SIDE_BUY);
    }

    /**
     * Scope a query to only include sell orders.
     */
    public function scopeSell($query)
    {
        return $query->where('side', self::SIDE_SELL);
    }
}

