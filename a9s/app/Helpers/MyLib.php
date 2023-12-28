<?php
//app/Helpers/Envato/User.php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use File;
use Illuminate\Support\Facades\Request;
use App\Exceptions\MyException;
use App\Model\User;

class MyLib
{
  public static $exist_item_no = "000.00.000"; // use in itemcontrollers
  // public static $total_question = 10;
  // public static $correct_value = 1;
  // public static $incorrect_value = 0;

  public static function internalUser()
  {
    $token = Request::bearerToken();
    if ($token == "") {
      throw new MyException(["message" => "Get user info cannot complete, please restart the apps"], 400);
    }
    $model_query = \App\Model\Internal\User::where("api_token", $token)->first();
    if (!$model_query) {
      throw new MyException(["message" => "Unauthenticate"], 401);
    }
    if ($model_query->can_login == false) {
      throw new MyException(["message" => "Izin Masuk Tidak Diberikan"], 400);
    }

    return $model_query;
  }


  public static function checkScope($user, $allowed_scopes = [], $msg = "Forbidden", $return = false)
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

  public static function checkDataScope($user, $allowed_scopes = [])
  {
    $scopes = $user->data_permissions->pluck("in_one_line")->toArray();
    $has_value = count(array_intersect($allowed_scopes, $scopes));
    return $has_value;
  }

  public static function checkActionScope($user, $allowed_scopes = [])
  {
    $scopes = $user->action_permissions->pluck("in_one_line")->toArray();
    $has_value = count(array_intersect($allowed_scopes, $scopes));
    return $has_value;
  }

  public static function emptyStrToNull($var)
  {
    return $var == "" ? null : $var;
  }

  public static function beOneSpaces($var)
  {
    $var = trim($var);
    $var = preg_replace('/\s+/', ' ', $var);
    return $var;
  }

  public static function textToLink($var)
  {
    $var = strtolower(self::beOneSpaces($var));
    $var = str_replace(' ', '_', $var);
    return $var;
  }


  // public static function visitor()
  // {
  //   $token = Request::bearerToken();
  //   if ($token=="") {
  //     throw new MyException(["message"=>"Get user info cannot complete, please restart the apps"]);
  //   }

  //   $session = \App\Model\PalmOil\Public_Session::where("session_key",$token)->first();
  //   if (!$session) {
  //     throw new MyException(["message"=>"Unauthenticate"],403);
  //   }

  //   $visitor_profile = \App\Model\PalmOil\Visitor_Profile::where('visitor_id',$session->visitor_id)->first();
  //   if (!$visitor_profile) {
  //     throw new MyException(["message"=>"Sorry your account not listed"],403);
  //   }

  //   return $session;
  // }

  // public static function office_user()
  // {
  //   $token = request()->bearerToken();
  //   if ($token=="") {
  //     throw new MyException("Maaf anda tidak teridentifikasi");
  //   }

  //   $data = \App\Model\PalmOil\Office_User::where("token",$token)->first();
  //   if (!$data) {
  //     throw new MyException("Maaf data yang dimasukkan tidak valid");
  //   }
  //   return $data;
  // }


  // public static function company_admin()
  // {
  //   // Header tidak boleh memakai underscore
  //   $session_key = Request::header('session-key');
  //   $code = Request::header('code');
  //   // $token = Request::bearerToken();
  //   if ($session_key=="") {
  //     throw new MyException(["message"=>"Need Session Key"]);
  //   }
  //   if ($code=="") {
  //     throw new MyException(["message"=>"Need Company Code"]);
  //   }

  //   $user_session = DB::connection('pgsql')->table("company.user_sessions")->where('ukey',$session_key)->first();
  //   if (!$user_session) {
  //     throw new MyException(["message"=>"Unauthenticate"],403);
  //   }

  //   $session = DB::connection('pgsql')->table("company.users")->where('id',$user_session->user_id)->where("company_code",$code)->first();
  //   if (!$session) {
  //     throw new MyException(["message"=>"Unauthenticate"],403);
  //   }
  //   return [
  //     "session_key"=>$session_key,
  //     "company_code"=>$session->company_code,
  //     "id"=>$session->id
  //   ];
  // }

  public static function http_request($url)
  {
    // persiapkan curl
    $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, $url);

    // return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // $output contains the output string
    $output = curl_exec($ch);

    // tutup curl
    curl_close($ch);

    // mengembalikan hasil curl
    return $output;
  }

  public static function thumbnail_youtube($url)
  {
    $data = self::http_request("https://noembed.com/embed?url=https://www.youtube.com/watch?v=" . $url);
    $data = json_decode($data, TRUE);
    return $data['thumbnail_url'] ?? "";
  }


  public static function getMillis()
  {
    return round(microtime(true) * 1000);
  }

  public static function manualMillis($strDate)
  {
    $date = new \DateTime($strDate);
    return round((float)($date->format("U") . "." . $date->format("U")) * 1000);
  }

  public static function utcMillis($strDate)
  {
    // date local to utc millis
    $date = new \DateTime($strDate);
    $date->sub(new \DateInterval('PT7H'));
    return round((float)($date->format("U") . "." . $date->format("v")) * 1000);
  }


  public static function millisToDateUTC($millis)
  {
    // date local to utc millis
    $date = date("Y-m-d H:i:s", $millis / 1000);
    return $date;
    // $date->sub(new \DateInterval('PT7H'));
    // return round((float)($date->format("U").".".$date->format("v"))*1000);
  }

  public static function millisToDateFullUTC($millis)
  {
    // date local to utc millis
    $date = date("Y-m-d\TH:i:s.v\Z", $millis / 1000);
    return $date;
    // $date->sub(new \DateInterval('PT7H'));
    // return round((float)($date->format("U").".".$date->format("v"))*1000);
  }

  // public static function millisToDateLocal($millis){
  //   // date local to utc millis
  //   $date = new \DateTime(self::millisToDateUTC($millis));
  //   $date->add(new \DateInterval('PT7H'));
  //   return $date->format('Y-m-d H:i:s');
  // }

  public static function timestamp()
  {
    $date = new \DateTime();
    return $date->format("YmdHisv");
  }

  public static function roman($number)
  {
    $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
    $returnValue = '';
    while ($number > 0) {
      foreach ($map as $roman => $int) {
        if ($number >= $int) {
          $number -= $int;
          $returnValue .= $roman;
          break;
        }
      }
    }
    return $returnValue;
  }

  public static function formatNo($kode, $no_urut = "0001", $date = "")
  {
    if (!$date) {
      $date = date("Y-m-d");
    }

    $arrDate = explode("-", $date);
    return $kode . "." . $arrDate[1] . substr($arrDate[0], 2, 2) . $no_urut;

    // $part1=$arrDate[1].substr($arrDate[0],2,2).$no_urut;
    // $part2=$kode."/ARTI/".self::roman((int)$arrDate[1])."/".$arrDate[0];
    // return [
    //   $part1,
    //   $part2
    // ];
  }

  public static function nextNo($no)
  {
    $split = explode(".", $no);
    $noUrutInt = (int) substr($split[1], 4, strlen($split[1]) - 4) + 1;
    $noUrutStr = str_pad($noUrutInt, 4, "0", STR_PAD_LEFT);
    $split[1] = substr($split[1], 0, 4) . $noUrutStr;
    return implode($split, ".");
    // $split = explode("/",$no); 
    // $noUrutInt = (int) substr( $split[0], 4, strlen( $split[0] ) - 4) + 1;
    // $noUrutStr = str_pad( $noUrutInt, 4, "0", STR_PAD_LEFT );
    // $split[0] = substr($split[0], 0, 4).$noUrutStr;
    // return implode($split,"/");
  }


  public static function mime($ext)
  {
    $result = [
      "contentType" => "",
      "exportType"  => "",
      "dataBase64"  => "",
      "ext"         => $ext
    ];

    switch ($ext) {
      case 'csv':
        $result["contentType"] = "application/csv";
        $result["exportType"] = \Maatwebsite\Excel\Excel::CSV;
        break;

      case 'xls':
        $result["contentType"] = "application/vnd.ms-excel";
        $result["exportType"] = \Maatwebsite\Excel\Excel::XLSX;
        break;

      case 'xlsx':
        $result["contentType"] = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        $result["exportType"] = \Maatwebsite\Excel\Excel::XLSX;
        break;

      case 'pdf':
        $result["contentType"] = "application/pdf";
        $result["exportType"] = \Maatwebsite\Excel\Excel::DOMPDF;
        // $result["exportType"]=\Maatwebsite\Excel\Excel::PDF;
        break;

      default:
        // code...
        break;
    }

    $result["dataBase64"] = "data:" . $result["contentType"] . ";base64,";
    return $result;
  }

  public function dateDiff($date_1, $date_2)
  {
    // $date2 = strtotime("2018-09-21 10:44:01");

    // Declare and define two dates
    $date1 = strtotime($date_1);
    $date2 = strtotime($date_2);

    $diff = $date2 - $date1;
    // // Formulate the Difference between two dates
    // $diff = abs($date2 - $date1);
    //
    //
    // // To get the year divide the resultant date into
    // // total seconds in a year (365*60*60*24)
    // $years = floor($diff / (365*60*60*24));
    //
    //
    // // To get the month, subtract it with years and
    // // divide the resultant date into
    // // total seconds in a month (30*60*60*24)
    // $months = floor(($diff - $years * 365*60*60*24)
    //                                / (30*60*60*24));
    //
    //
    // // To get the day, subtract it with years and
    // // months and divide the resultant date into
    // // total seconds in a days (60*60*24)
    // $days = floor(($diff - $years * 365*60*60*24 -
    //              $months*30*60*60*24)/ (60*60*24));
    //
    //
    // // To get the hour, subtract it with years,
    // // months & seconds and divide the resultant
    // // date into total seconds in a hours (60*60)
    // $hours = floor(($diff - $years * 365*60*60*24
    //        - $months*30*60*60*24 - $days*60*60*24)
    //                                    / (60*60));
    //
    //
    // // To get the minutes, subtract it with years,
    // // months, seconds and hours and divide the
    // // resultant date into total seconds i.e. 60
    // $minutes = floor(($diff - $years * 365*60*60*24
    //          - $months*30*60*60*24 - $days*60*60*24
    //                           - $hours*60*60)/ 60);
    //
    //
    // // To get the minutes, subtract it with years,
    // // months, seconds, hours and minutes
    // $seconds = floor(($diff - $years * 365*60*60*24
    //          - $months*30*60*60*24 - $days*60*60*24
    //                 - $hours*60*60 - $minutes*60));

    // // Print the result
    // printf("%d years, %d months, %d days, %d hours, "
    //      . "%d minutes, %d seconds", $years, $months,
    //              $days, $hours, $minutes, $seconds);
  }
}
