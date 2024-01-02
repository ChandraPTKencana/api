<?php

namespace App\Models\Stok;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'st_items';  

    public function unit()
    {
        return $this->belongsTo(Unit::class, "st_unit_id", 'id');
    }

    public function updator()
    {
        return $this->hasOne(\App\Models\IsUser::class, 'id_user', "updated_by");
    }

    public function creator()
    {
        return $this->hasOne(\App\Models\IsUser::class, 'id_user', "created_by");
    }
}
