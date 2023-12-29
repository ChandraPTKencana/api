<?php

namespace App\Models\Stok;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'st_warehouses';  

    public function updator()
    {
        return $this->hasOne(\App\Models\IsUser::class, 'id_user', "updated_by");
    }

    public function creator()
    {
        return $this->hasOne(\App\Models\IsUser::class, 'id_user', "created_by");
    }
}
