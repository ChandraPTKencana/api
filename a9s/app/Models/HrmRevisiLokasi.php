<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrmRevisiLokasi extends Model
{
    use HasFactory;

    protected $table = 'hrm_revisi_lokasi';

    // function the_user()
    // {
    //     return $this->belongsTo(IsUser::class, 'user_id', 'id_user');
    // }
}
