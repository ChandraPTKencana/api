<?php
//app/Helpers/Envato/User.php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use File;
use Illuminate\Support\Facades\Request;
use App\Exceptions\MyException;
use App\Model\User;

class MyAdmin
{
  public static function user()
  {
    $token = Request::bearerToken();
    if ($token == "") {
      throw new MyException(["message" => "Get user info cannot complete, please restart the apps"], 400);
    }
    $model_query = \App\Models\MySql\Session::where("token", $token)->first();
    if (!$model_query) {
      throw new MyException(["message" => "Unauthenticate"], 401);
    }
    if ($model_query->the_user->status == "blokir") {
      throw new MyException(["message" => "Izin Masuk Tidak Diberikan"], 400);
    }

    return $model_query;
  }


  public static function checkRole($role, $allowed_roles = [], $msg = null, $return = false)
  {
    $msg = is_null($msg) ? 'Forbidden' : $msg;
    
    $has_value = in_array($role,$allowed_roles);
    if ($return) {
      return $has_value;
    }

    if ($has_value == 0) {
      throw new MyException(["message" => $msg], 403);
    }
  }
  // public static function checkRole($role, $allowed_roles = [], $msg = "Forbidden", $return = false)
  // {
  //   $has_value = in_array($role,$allowed_roles);
  //   if ($return) {
  //     return $has_value;
  //   }

  //   if ($has_value == 0) {
  //     throw new MyException(["message" => $msg], 403);
  //   }
  // }



  public static function checkReturnOrFailLocation($user,$loc, $msg = "Forbidden")
  {
    $has_value = in_array($loc,$user->hrm_revisi_lokasis());
    if ($has_value == 0) {
      throw new MyException(["message" => $msg], 403);
    }
    return $loc;
  }

  public static function checkScope($scopes, $allowed_scopes = '',$return=false,$localException=false)
  {
    if ($return) {
      return in_array($allowed_scopes,$scopes);
    }

    if (!in_array($allowed_scopes,$scopes) && $localException==false) {
      throw new MyException(["message" => "Need ".$allowed_scopes." Permission"], 403);
    }elseif(!in_array($allowed_scopes,$scopes) && $localException){
      throw new \Exception("Need ".$allowed_scopes." Permission",1);
    }
  }

  public static function checkMultiScope($scopes, $allowed_scopes = [],$return=false)
  {
    $has_value = count(array_intersect($allowed_scopes, $scopes));
    if ($return) {
      return $has_value;
    }
    if ($has_value == 0) {
      throw new MyException(["message" => "Need ".implode(" or ",$allowed_scopes)." Permission"], 403);
    }
  }

  public static function returnCheckScope($user, $allowed_scopes = [])
  {
    $scopes = $user->data_permissions->pluck("in_one_line")->toArray();
    $has_value = count(array_intersect($allowed_scopes, $scopes));
    return $has_value;
  }

  public static function callCheckScope($user, $allowed_scopes = [], $msg = "Forbidden", $return = false)
  {
    $scopes = $user->listPermissions();
    $has_value = count(array_intersect($allowed_scopes, $scopes));
    if ($return) {
      return $has_value;
    }

    if ($has_value == 0) {
      throw new MyException(["message" => $msg], 403);
    }
  }

  public static function checkActionScope($user, $allowed_scopes = [])
  {
    $scopes = $user->action_permissions->pluck("in_one_line")->toArray();
    $has_value = count(array_intersect($allowed_scopes, $scopes));
    return $has_value;
  }

  // public static function checkReturnOrFailLocation($user,$loc, $msg = "Forbidden")
  // {
  //   $has_value = in_array($loc,$user->hrm_revisi_lokasis());
  //   if ($has_value == 0) {
  //     throw new MyException(["message" => $msg], 403);
  //   }
  //   return $loc;
  // }

  // public static function checkScope($user, $allowed_scopes = [], $msg = "Forbidden", $return = false)
  // {
  //   $scopes = $user->listPermissions();
  //   $has_value = count(array_intersect($allowed_scopes, $scopes));
  //   if ($return) {
  //     return $has_value;
  //   }

  //   if ($has_value == 0) {
  //     throw new MyException(["message" => $msg], 403);
  //   }
  // }


  // public static function checkDataScope($user, $allowed_scopes = [])
  // {
  //   $scopes = $user->data_permissions->pluck("in_one_line")->toArray();
  //   $has_value = count(array_intersect($allowed_scopes, $scopes));
  //   return $has_value;
  // }

  // public static function checkActionScope($user, $allowed_scopes = [])
  // {
  //   $scopes = $user->action_permissions->pluck("in_one_line")->toArray();
  //   $has_value = count(array_intersect($allowed_scopes, $scopes));
  //   return $has_value;
  // }
}
