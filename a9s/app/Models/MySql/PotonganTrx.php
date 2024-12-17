<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PotonganTrx extends Model
{
    use HasFactory;

    protected $table = 'potongan_trx';  
    // public $timestamps = false;

    // public function warehouse()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_id", 'id');
    // }

    // public function warehouse_source()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_source_id", 'id');
    // }

    public function potongan_mst()
    {
        return $this->belongsTo(PotonganMst::class, "potongan_mst_id", 'id')->exclude(['attachment_1','attachment_2']);
    }

    // public function details()
    // {
    //     return $this->hasMany(TransactionDetail::class, 'st_transaction_id', 'id');
    // }

    public function deleted_by()
    {
        return $this->belongsTo(IsUser::class, 'deleted_user', "id");
    }

    // public function confirmer()
    // {
    //     return $this->hasOne(\App\Models\IsUser::class, 'id_user', "confirmed_by");
    // }

    public function val_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val_user");
    }

    public function val1_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val1_user");
    }
}
