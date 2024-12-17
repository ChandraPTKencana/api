<?php

namespace App\Models\MySql;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PotonganMst extends Model
{
    use HasFactory;

    protected $table = 'potongan_mst';  

    // protected $hidden = [
    //     'attachment_1',
    // ];
    // public $timestamps = false;

    // public function warehouse()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_id", 'id');
    // }

    // public function warehouse_source()
    // {
    //     return $this->belongsTo(\App\Models\HrmRevisiLokasi::class, "hrm_revisi_lokasi_source_id", 'id');
    // }
    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff([
            "id","kejadian","employee_id","no_pol","nominal","nominal_cut","remaining_cut",
            "created_at","updated_at","val","val_user","val_at","val1","val1_user","val1_at","status",
            "deleted","deleted_user","deleted_at","deleted_reason",
            "attachment_1","attachment_1_type",
            "attachment_2","attachment_2_type"
        ], $columns));
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id", 'id')->exclude(['attachment_1','attachment_2']);
    }

    public function trxs()
    {
        return $this->hasMany(PotonganTrx::class, 'potongan_mst_id', 'id');
    }
    
    public function val_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val_user");
    }

    public function val1_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val1_user");
    }

    // public function requester()
    // {
    //     return $this->hasOne(\App\Models\IsUser::class, 'id_user', "requested_by");
    // }

    // public function confirmer()
    // {
    //     return $this->hasOne(\App\Models\IsUser::class, 'id_user', "confirmed_by");
    // }
}
