<?php

namespace App\Models\Stok;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'st_transactions';  
    public $timestamps = false;

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_id", 'id');
    }

    public function warehouse_source()
    {
        return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_source_id", 'id');
    }

    public function warehouse_target()
    {
        return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_target_id", 'id');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'st_transaction_id', 'id');
    }

    public function requested_by()
    {
        return $this->hasOne(\App\Models\IsUser::class, 'id_user', "requested_user");
    }

    public function confirmed_by()
    {
        return $this->hasOne(\App\Models\IsUser::class, 'id_user', "confirmed_user");
    }
}
