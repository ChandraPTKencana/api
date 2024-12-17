<?php

namespace App\Models\MySql;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Str;
class Vehicle extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'vehicle_mst';
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
    //     'password',
    //     'remember_token',
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

    public function deleted_by()
    {
        return $this->hasOne(IsUser::class, 'id', "deleted_user");
    }
}
