<?php

namespace App\Models\Stok;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'st_units';  

    public function updated_by()
    {
        return $this->hasOne(\App\Models\MySql\IsUser::class, 'id_user', "updated_user");
    }

    public function created_by()
    {
        return $this->hasOne(\App\Models\MySql\IsUser::class, 'id_user', "created_user");
    }
}
