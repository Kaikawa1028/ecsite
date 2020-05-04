<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use BulkInsertOrUpdateTrait;

    protected function getUpdateColumnsOnDuplicate(): array
    {
        return [
            'amount',
            'updated_at'
        ];
    }

    public function cartItems()
    {
        return $this->hasMany('App\CartItem');
    }
}
