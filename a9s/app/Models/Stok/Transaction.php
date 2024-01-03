<?php

namespace App\Models\Stok;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'st_transactions';  

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

    public function item()
    {
        return $this->belongsTo(Item::class, "st_item_id", 'id');
    }

    public function requester()
    {
        return $this->hasOne(\App\Models\IsUser::class, 'id_user', "requested_by");
    }

    public function approver()
    {
        return $this->hasOne(\App\Models\IsUser::class, 'id_user', "approved_by");
    }
}
