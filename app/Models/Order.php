<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const OPEN_ORDER      = 1;
    const FILLED_ORDER    = 2;
    const CANCELLED_ORDER = 3;

    protected $fillable = [
        'user_id',
        'symbol',
        'side', // buy || sell    
        'price',
        'amount',
        'status',   
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen()
    {
        return $this->status === 1;
    }

    public function isFilled()
    {
        return $this->status === 2;
    }

    public function isCancelled()
    {
        return $this->status === 3;
    }

    public function getTotalValueAttribute()
    {
        return bcmul($this->price, $this->amount, 8);
    }
}
