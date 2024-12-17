<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrxTrp extends Model
{
    use HasFactory;

    protected $table = 'trx_trp';
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
    //     return $this->hasMany(UjalanDetail::class, 'id_uj', 'id');
    // }

    // public function requester()
    // {
    //     return $this->hasOne(\App\Models\IsUser::class, 'id_user', "requested_by");
    // }

    public function val_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val_user");
    }

    public function val1_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val1_user");
    }

    public function val2_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val2_user");
    }

    public function val3_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val3_user");
    }

    public function val4_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val4_user");
    }

    public function val5_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val5_user");
    }

    public function val6_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val6_user");
    }

    public function ritase_val_by()
    {
        return $this->hasOne(IsUser::class, 'id', "ritase_val_user");
    }

    public function ritase_val1_by()
    {
        return $this->hasOne(IsUser::class, 'id', "ritase_val1_user");
    }

    public function ritase_val2_by()
    {
        return $this->hasOne(IsUser::class, 'id', "ritase_val2_user");
    }


    public function val_ticket_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val_ticket_user");
    }

    public function deleted_by()
    {
        return $this->hasOne(IsUser::class, 'id', "deleted_user");
    }

    public function req_deleted_by()
    {
        return $this->hasOne(IsUser::class, 'id', "req_deleted_user");
    }

    public function trx_absens()
    {
        return $this->hasMany(TrxAbsen::class, 'trx_trp_id', 'id');
    }

    public function uj()
    {
        return $this->belongsTo(Ujalan::class, 'id_uj', 'id');
    }

    public function uj_details()
    {
        return $this->hasMany(UjalanDetail::class, 'id_uj', 'id_uj');
    }

    public function uj_details2()
    {
        return $this->hasMany(UjalanDetail2::class, 'id_uj', 'id_uj');
    }

    public function potongan()
    {
        return $this->hasMany(PotonganTrx::class, 'trx_trp_id', 'id')->with('potongan_mst');
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, "payment_method_id", 'id');
    }

    public function employee_s()
    {
        return $this->belongsTo(Employee::class, "supir_id", 'id')->exclude(['attachment_1','attachment_2']);
    }

    public function employee_k()
    {
        return $this->belongsTo(Employee::class, "kernet_id", 'id')->exclude(['attachment_1','attachment_2']);
    }


    public function extra_money_trxs()
    {
        return $this->hasMany(ExtraMoneyTrx::class, "trx_trp_id", 'id')->exclude(['attachment_1_loc']);
    }

}
