<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandbyTrx extends Model
{
    use HasFactory;

    protected $table = 'standby_trx';
    // public $timestamps = false;

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

    public function deleted_by()
    {
        return $this->hasOne(IsUser::class, 'id', "deleted_user");
    }

    public function req_deleted_by()
    {
        return $this->hasOne(IsUser::class, 'id', "req_deleted_user");
    }

    public function details()
    {
        return $this->hasMany(StandbyTrxDtl::class, 'standby_trx_id', 'id');
    }

    public function standby_mst()
    {
        return $this->belongsTo(StandbyMst::class, "standby_mst_id", 'id');
    }

    public function salary_paid()
    {
        return $this->belongsTo(SalaryPaid::class, "salary_paid_id", 'id');
    }
}
