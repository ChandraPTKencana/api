<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_method';  
    public $timestamps = false;

    // public function warehouse()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_id", 'id');
    // }

    // public function warehouse_source()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_source_id", 'id');
    // }

    // public function supir()
    // {
    //     return $this->belongsTo(Employee::class, "supir_id", 'id');
    // }

    // public function trxs()
    // {
    //     return $this->hasMany(PotonganTrx::class, 'potongan_mst_id', 'id');
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
