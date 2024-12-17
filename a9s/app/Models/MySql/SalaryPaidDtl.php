<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryPaidDtl extends Model
{
    use HasFactory;

    protected $table = 'salary_paid_dtl';

    protected $primaryKey = null;
    public $incrementing = false;

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id", 'id')->exclude(['attachment_1','attachment_2']);
    }
}
