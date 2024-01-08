<?php

namespace App\Models\Stok;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'st_transaction_details';  
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo(Item::class, "st_item_id", 'id');
    }
}
