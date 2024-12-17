<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPaid extends Model
{
    use HasFactory;

    protected $table = 'salary_paid';
    // public $timestamps = false;

    
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

    // public function deleted_by()
    // {
    //     return $this->hasOne(IsUser::class, 'id', "deleted_user");
    // }

    // public function req_deleted_by()
    // {
    //     return $this->hasOne(IsUser::class, 'id', "req_deleted_user");
    // }

    public function details()
    {
        return $this->hasMany(SalaryPaidDtl::class, 'salary_paid_id', 'id');
    }

    // public function standby_mst()
    // {
    //     return $this->belongsTo(StandbyMst::class, "standby_mst_id", 'id');
    // }
}
