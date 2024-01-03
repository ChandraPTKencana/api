<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Str;
class IsUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'is_users';
    protected $primaryKey = "id_user";

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


    public function generateToken()
    {
        $token      = Str::random(100).$this->id_user."/#".round(microtime(true) * 1000);
        $session = new \App\Models\Session();
        $session->user_id = $this->id_user;
        $session->token = $token;
        $session->created_at = date("Y-m-d h:i:s");
        $session->updated_at = date("Y-m-d h:i:s");
        $session->save();
        return $token;
    }

    public function hrm_revisi_lokasis()
    {
        $locs=explode(",",$this->loc);
        return $this->from("hrm_revisi_lokasi")->select('*')->whereIn("id",$locs)->get()->toArray();
        // return $this->belongsTo(HrmRevisiLokasi::class, 'loc', 'id');
    }
}
