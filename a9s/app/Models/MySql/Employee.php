<?php

namespace App\Models\MySql;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Str;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\Excludable;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use Excludable;
    protected $table = 'employee_mst';
    // protected $primaryKey = "id_user";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    // /**
    //  * The attributes that should be hidden for serialization.
    //  *
    //  * @var array<int, string>
    //  */
    // protected $hidden = [
    //     'attachment_1',
    // ];

    // /**
    //  * The attributes that should be cast.
    //  *
    //  * @var array<string, string>
    //  */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];


    // public function generateToken()
    // {
    //     $token      = Str::random(100).$this->id."/#".round(microtime(true) * 1000);
    //     $session = new \App\Models\MySql\Session();
    //     $session->user_id = $this->id;
    //     $session->token = $token;
    //     $session->created_at = date("Y-m-d h:i:s");
    //     $session->updated_at = date("Y-m-d h:i:s");
    //     $session->save();
    //     return $token;
    // }

    // public function hrm_revisi_lokasis()
    // {
    //     $locs=explode(",",$this->loc);
    //     return $this->from("hrm_revisi_lokasi")->select('*')->whereIn("id",$locs)->get()->pluck("id")->toArray();
    //     // return $this->belongsTo(HrmRevisiLokasi::class, 'loc', 'id');
    // }

    public function scopeVerified(Builder $builder){
        // $builder->where('val',1)->orWhere('val',0); // TEMP
        $builder->where('val',1); //Right
    }

    public function scopeAvailable(Builder $builder){
        $builder->where('deleted',0);
    }

    // public function scopeExclude($query, $columns)
    // {
    //     return $query->select(array_diff([
    //         "id","name","role","created_user","updated_user","created_at","updated_at",
    //         "deleted","deleted_user","deleted_at","deleted_reason",
    //         "ktp_no","sim_no","rek_no","rek_name","phone_number",
    //         "val","val_user","val_at",
    //         "attachment_1","attachment_1_type",
    //         "attachment_2","attachment_2_type",
    //         "bank_id"
    //     ], $columns));
    // }

    public function potongan(){
        return $this->hasOne(PotonganMst::class,"employee_id","id")->exclude(['attachment_1','attachment_2'])
        ->where('val1',1)->where('deleted',0)->where('status','Open')->where('remaining_cut',">",0)->orderBy('created_at','asc');
    }

    public function bank(){
        return $this->belongsTo(Bank::class,"bank_id","id");
    }
    
    public function val_by()
    {
        return $this->hasOne(IsUser::class, 'id', "val_user");
    }

    public function deleted_by()
    {
        return $this->hasOne(IsUser::class, 'id', "deleted_user");
    }
    // public function scopeOfRole(Builder $query, string $role): void
    // {
    //     $query->where('role', $role);
    // }
}
