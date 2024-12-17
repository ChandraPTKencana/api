<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class ExtraMoneyTrx extends Model
{
    use HasFactory;
    use Excludable;

    protected $table = 'extra_money_trx';  
    // public $timestamps = false;

        // public function details()
        // {
        //     return $this->hasMany(ExtraMoneyTrxDtl::class, 'extra_money_trx_id', 'id');
        // }

    public function extra_money()
    {
        return $this->belongsTo(ExtraMoney::class, 'extra_money_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id')->exclude(['attachment_1','attachment_2']);
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

    public function deleted_by()
    {
        return $this->hasOne(IsUser::class, 'id', "deleted_user");
    }

    public function req_deleted_by()
    {
        return $this->hasOne(IsUser::class, 'id', "req_deleted_user");
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, "payment_method_id", 'id');
    }

}
