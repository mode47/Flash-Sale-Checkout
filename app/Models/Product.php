<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image'
    ];
    protected $appends = ['image'];

    public function getImageAttribute($value)
    {
         return $value ? url($value) : null;
    }


    public function holds()
    {
        return $this->belongsToMany(Hold::class, 'hold_product')
            ->withPivot('quantity')
            ->withTimestamps();
    }

}
