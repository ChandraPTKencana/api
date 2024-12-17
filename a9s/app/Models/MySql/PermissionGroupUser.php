<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroupUser extends Model
{
    use HasFactory;

    protected $table = 'st_permission_group_user';
    // public $timestamps = false;
    // protected $primaryKey = null;

    // public function warehouse()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_id", 'id');
    // }

    // public function warehouse_source()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_source_id", 'id');
    // }

    public function user()
    {
        return $this->belongsTo(IsUser::class, "user_id", 'id_user');
    }

    public function permission_group()
    {
        return $this->belongsTo(PermissionGroup::class, "st_permission_group_id", 'id');
    }

    // public function details()
    // {
    //     return $this->hasMany(FinPaymentReqDtl::class, 'fin_payment_req_id', 'id');
    // }

    // public function val_by()
    // {
    //     return $this->hasOne(IsUser::class, 'id', "val_user");
    // }

    // public function val1_by()
    // {
    //     return $this->hasOne(IsUser::class, 'id', "val1_user");
    // }

    // public function requester()
    // {
    //     return $this->hasOne(\App\Models\IsUser::class, 'id_user', "requested_by");
    // }

    // public function confirmer()
    // {
    //     return $this->hasOne(\App\Models\IsUser::class, 'id_user', "confirmed_by");
    // }
}
