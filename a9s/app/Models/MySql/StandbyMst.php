<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandbyMst extends Model
{
    use HasFactory;

    protected $table = 'standby_mst';  
    // public $timestamps = false;

    // public function warehouse()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_id", 'id');
    // }

    // public function warehouse_source()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_source_id", 'id');
    // }

    // public function warehouse_target()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_target_id", 'id');
    // }

    public function details()
    {
        return $this->hasMany(StandbyDtl::class, 'standby_mst_id', 'id');
    }

    public function val_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val_user");
    }

    public function val1_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val1_user");
    }

    public function deleted_by()
    {
        return $this->hasOne(IsUser::class, 'id', "deleted_user");
    }
    // public function requester()
    // {
    //     return $this->hasOne(\App\Models\IsUser::class, 'id_user', "requested_by");
    // }

    // public function confirmer()
    // {
    //     return $this->hasOne(\App\Models\IsUser::class, 'id_user', "confirmed_by");
    // }
}
