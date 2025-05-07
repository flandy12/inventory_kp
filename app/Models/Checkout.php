<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Checkout extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'total_price',
        'payment_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'checkout_product')
            ->withPivot('quantity', 'price');
    }

    public function items()
    {
        return $this->hasMany(CheckoutItem::class);
    }

    public function getTotalPriceAttribute()
    {
        return $this->products->sum(function ($product) {
            return $product->pivot->quantity * $product->pivot->price;
        });
    }

    public function getPaymentStatusAttribute($value)
    {
        return $value === 'pending' ? 'Pending' : 'Paid';
    }

    public function setPaymentStatusAttribute($value)
    {
        $this->attributes['payment_status'] = $value === 'Pending' ? 'pending' : 'paid';
    }

    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = auth()->id();
    }

    public function setTotalPriceAttribute($value)
    {
        $this->attributes['total_price'] = $value;
    }

    protected static function booted()
    {
        static::creating(function ($checkout) {
            $checkout->order_id = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        });
    }

}
