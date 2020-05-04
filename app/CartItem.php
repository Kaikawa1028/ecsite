<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'item_id', 'quantity'];

    public function item()
    {
        return $this->belongsTo('App\Item', 'item_id', 'id');
    }
}
