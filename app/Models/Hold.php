<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hold extends Model
{
    
    protected $fillable = [
        'user_id',
        'status',
        'expires_at'
    ];
     protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'hold_product')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
