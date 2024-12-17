<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Excludable;

class SalaryBonus extends Model
{
    use HasFactory;
    use Excludable;

    protected $table = 'salary_bonus';
    // public $timestamps = false;

    
    public function val1_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val1_user");
    }
    
    public function val2_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val2_user");
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id", 'id')->exclude(['attachment_1','attachment_2']);
    }
}
