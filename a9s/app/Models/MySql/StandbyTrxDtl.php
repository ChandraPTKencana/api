<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class StandbyTrxDtl extends Model
{
    use HasFactory;
    use Excludable;

    protected $table = 'standby_trx_dtl';
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

    // public function details()
    // {
    //     return $this->hasMany(TransactionDetail::class, 'st_transaction_id', 'id');
    // }

    // public function requester()
    // {
    //     return $this->hasOne(\App\Models\IsUser::class, 'id_user', "requested_by");
    // }

    public function standby_trx()
    {
        return $this->belongsTo(\App\Models\MySql\StandbyTrx::class, 'standby_trx_id', "id");
    }
}
