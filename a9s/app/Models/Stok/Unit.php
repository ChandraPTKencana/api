<?php

namespace App\Models\Stok;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'st_units';  

    public function updator()
    {
        return $this->hasOne(\App\Models\IsUser::class, 'id_user', "updated_by");
    }

    public function creator()
    {
        return $this->hasOne(\App\Models\IsUser::class, 'id_user', "created_by");
    }
}
