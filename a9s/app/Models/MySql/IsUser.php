<?php

namespace App\Models\MySql;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Helpers\MyLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Str;
class IsUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'is_users';
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


    public function generateToken()
    {
        $token      = Str::random(100).$this->id."/#".round(microtime(true) * 1000);
        $session = new \App\Models\MySql\Session();
        $session->user_id = $this->id;
        $session->token = $token;
        $session->created_at = date("Y-m-d h:i:s");
        $session->updated_at = date("Y-m-d h:i:s");
        $session->save();
        return $token;
    }

    // public function hrm_revisi_lokasis()
    // {
    //     $locs=explode(",",$this->loc);
    //     return $this->from("hrm_revisi_lokasi")->select('*')->whereIn("id",$locs)->get()->pluck("id")->toArray();
    //     // return $this->belongsTo(HrmRevisiLokasi::class, 'loc', 'id');
    // }


    public function details()
    {
        return $this->hasMany(PermissionUserDetail::class, 'user_id', 'id_user');
    }

    public function permission_group_users()
    {
        return $this->hasMany(PermissionGroupUser::class, 'user_id', 'id_user');
    }

    public function listPermissions()
    {
        $id = $this->id_user;
        $group_permissions = PermissionGroupDetail::whereIn('st_permission_group_id',function($q)use($id) {
            $q->select('st_permission_group_id');
            $q->from('st_permission_group_user');
            $q->where('user_id',$id);
        })->get()->pluck('st_permission_list_name')->toArray();
        $permissions = $this->details()->pluck('st_permission_list_name')->toArray();
        return array_merge($group_permissions,$permissions);
    }

}
