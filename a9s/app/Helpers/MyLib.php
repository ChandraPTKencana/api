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

  public static function timestampMs() {
    $time = microtime(true);
    $mSecs = sprintf('%03d', ($time - floor($time)) * 1000);
    return date("Y-m-d H:i:s").".".$mSecs;
  }

  public static function objsToArray($objs){
    return $objs->map(function ($item) {
      return array_map('utf8_encode', (array)$item);
    })->toArray();
  }

  public static function compareChange($old,$new){
    $note="";
    $o = is_array($old) ? $old : $old->toArray();
    $n = is_array($new) ? $new : $new->toArray();

    foreach ($o as $k => $v) {
      if(isset($n[$k]) && $n[$k]!=$v){
        if($note==""){
          $note.="Data yang berubah: \n";
        }
        $note.="[".$k."] ".$v." => ".$n[$k]."\n";
      }
    }
    return $note;
  }

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

  public static function utcDateToIdnDate($strDate)
  {
    // date local to utc millis
    $date = new \DateTime($strDate);
    $date->add(new \DateInterval('PT7H'));
    return $date->format('Y-m-d\TH:i:s.v\Z');
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
    return implode(".",$split);
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


  public static function queryCheck($value,$key,$q,$request=""){
    if(array_search($value['type'],['string','number'])!==false && $value['value_1']!==''){
  
      if($value["operator"]=='exactly_same'){
        $q->Where($key, $value["value_1"]);
      }

      if($value["operator"]=='exactly_not_same'){
        $q->Where($key,"!=", $value["value_1"]);
      }

      if($value["operator"]=='same'){
        $v_val1=explode(",",$value["value_1"]);
        $q->where(function ($q1)use($v_val1,$key){
          foreach ($v_val1 as $k1 => $v1) {
            $q1->orwhere($key,"like", '%'.$v1.'%');
          }
        });
      }

      if($value["operator"]=='not_same'){
        $v_val1=explode(",",$value["value_1"]);
        $q->where(function ($q1)use($v_val1,$key){
          foreach ($v_val1 as $k1 => $v1) {
            $q1->orwhere($key,"not like", '%'.$v1.'%');
          }
        });
      }

      if($value["operator"]=='more_then'){
        $q->Where($key,">", $value["value_1"]);
      }
      
      if($value["operator"]=='more_and'){
        $q->Where($key,">=", $value["value_1"]);
      }

      if($value["operator"]=='less_then'){
        $q->Where($key,"<", $value["value_1"]);
      }

      if($value["operator"]=='less_and'){
        $q->Where($key,"<=", $value["value_1"]);
      }
    }

    if(array_search($value['type'],['date','datetime'])!==false){
      if($value['value_1'] || $value['value_2']){
        $date_from = $value['value_1'];
        if(!$date_from)
        throw new MyException([ "message" => "Date From pada ".$value['label']." harus diisi" ], 400);
  
        if(!strtotime($date_from))
        throw new MyException(["message"=>"Format Date pada ".$value['label']." From Tidak Cocok"], 400);

      
        $date_to = $value['value_2'];                
        if(!$date_to)
        throw new MyException([ "message" => "Date To pada ".$value['label']." harus diisi" ], 400);
      
        if(!strtotime($date_to))
        throw new MyException(["message"=>"Format Date To pada ".$value['label']." Tidak Cocok"], 400);
    
        if($value['operator']=="specific"){
          $date_from = date($value['type']=='datetime'?"Y-m-d H:i:s.v" :"Y-m-d",strtotime($date_from));
          $date_to = date($value['type']=='datetime'?"Y-m-d H:i:s.v" :"Y-m-d",strtotime($date_to));
          if(strtotime($date_from)>strtotime($date_to))
          throw new MyException(["message"=>"Pada ".$value['label']." Ada kesalahan, Cek kembali Date From dan To"], 400);
          
          $q->whereBetween($key,[$date_from,$date_to]);
        }
        
        if($value['operator']=="fullday"){
          $date_from = date("Y-m-d",strtotime($date_from))." 00:00:00.000";
          $date_to = date("Y-m-d",strtotime($date_to))." 23:59:59.999";

          if(strtotime($date_from)>strtotime($date_to))
          throw new MyException(["message"=>"Pada ".$value['label']." Ada kesalahan, Cek kembali Date From dan To"], 400);

          $q->where($key,">=",$date_from);
          $q->where($key,"<=",$date_to);
        }
      }
    }

    if(array_search($value['type'],['select'])!==false && $value['value_1']!==''){ 
      if($value["operator"]=='exactly_same'){
        $q->Where($key, $value["value_1"]);
      }

      if($value["operator"]=='exactly_not_same'){
        $q->Where($key,"!=", $value["value_1"]);
      }
    }
  }

  public static function queryCheckP1($alias,$value,$key,$q,$table=""){
    $nkey = str_replace($alias."_","",$key);
    if($table=="") $table = $alias;

    if(array_search($value['type'],['string','number'])!==false && $value['value_1']!==''){
  
      if($value["operator"]=='exactly_same'){
        $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,$value['value_1']);          
        });
      }

      if($value["operator"]=='exactly_not_same'){
        $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,'!=',$value['value_1']);          
        });
      }

      if($value["operator"]=='same'){
        $v_val1=explode(",",$value["value_1"]);

        $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$v_val1) {
          $q1->from($table)->select('id');
          $q1->where(function ($q2)use($v_val1,$nkey){
            foreach ($v_val1 as $k1 => $v1) {
              $q2->orwhere($nkey,"like", '%'.$v1.'%');
            }
          });          
        });
      }

      if($value["operator"]=='not_same'){
        $v_val1=explode(",",$value["value_1"]);

        $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$v_val1) {
          $q1->from($table)->select('id');
          $q1->where(function ($q2)use($v_val1,$nkey){
            foreach ($v_val1 as $k1 => $v1) {
              $q2->orwhere($nkey,"not like", '%'.$v1.'%');
            }
          });          
        });
      }

      if($value["operator"]=='more_then'){
        $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,'>',$value['value_1']);          
        });
      }
      
      if($value["operator"]=='more_and'){
        $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,'>=',$value['value_1']);          
        });
      }

      if($value["operator"]=='less_then'){
        $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,'<',$value['value_1']);          
        });
      }

      if($value["operator"]=='less_and'){
        $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,'<=',$value['value_1']);          
        });
      }
    }

    if(array_search($value['type'],['date','datetime'])!==false){
      if($value['value_1'] || $value['value_2']){
        $date_from = $value['value_1'];
        if(!$date_from)
        throw new MyException([ "message" => "Date From pada ".$value['label']." harus diisi" ], 400);
  
        if(!strtotime($date_from))
        throw new MyException(["message"=>"Format Date pada ".$value['label']." From Tidak Cocok"], 400);

      
        $date_to = $value['value_2'];                
        if(!$date_to)
        throw new MyException([ "message" => "Date To pada ".$value['label']." harus diisi" ], 400);
      
        if(!strtotime($date_to))
        throw new MyException(["message"=>"Format Date To pada ".$value['label']." Tidak Cocok"], 400);
    
        if($value['operator']=="specific"){
          $date_from = date($value['type']=='datetime'?"Y-m-d H:i:s.v" :"Y-m-d",strtotime($date_from));
          $date_to = date($value['type']=='datetime'?"Y-m-d H:i:s.v" :"Y-m-d",strtotime($date_to));
          if(strtotime($date_from)>strtotime($date_to))
          throw new MyException(["message"=>"Pada ".$value['label']." Ada kesalahan, Cek kembali Date From dan To"], 400);
          
          $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$date_from,$date_to) {
            $q1->from($table)
            ->select('id')->whereBetween($nkey,[$date_from,$date_to]);          
          });
        }
        
        if($value['operator']=="fullday"){
          $date_from = date("Y-m-d",strtotime($date_from))." 00:00:00.000";
          $date_to = date("Y-m-d",strtotime($date_to))." 23:59:59.999";

          if(strtotime($date_from)>strtotime($date_to))
          throw new MyException(["message"=>"Pada ".$value['label']." Ada kesalahan, Cek kembali Date From dan To"], 400);

          $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$date_from,$date_to) {
            $q1->from($table)
            ->select('id')->where($nkey,">=",$date_from)->where($nkey,"<=",$date_to);          
          });
        }
      }
    }

    if(array_search($value['type'],['select'])!==false && $value['value_1']!==''){ 
      if($value["operator"]=='exactly_same'){
        $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,$value['value_1']);          
        });
      }

      if($value["operator"]=='exactly_not_same'){
        $q->whereIn($alias.'_id', function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,"!=",$value['value_1']);          
        });
      }
    }
  }

  public static function queryCheckP1Dif($alias,$value,$key,$q,$table="",$pk=""){
    $nkey = str_replace($alias."_","",$key);
    $pk_id = $pk ? $pk : $alias.'_id';
    if($table=="") $table = $alias;

    if(array_search($value['type'],['string','number'])!==false && $value['value_1']!==''){
  
      if($value["operator"]=='exactly_same'){
        $q->whereIn($pk_id, function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,$value['value_1']);          
        });
      }

      if($value["operator"]=='exactly_not_same'){
        $q->whereIn($pk_id, function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,'!=',$value['value_1']);          
        });
      }

      if($value["operator"]=='same'){
        $v_val1=explode(",",$value["value_1"]);

        $q->whereIn($pk_id, function($q1)use($table,$nkey,$v_val1) {
          $q1->from($table)->select('id');
          $q1->where(function ($q2)use($v_val1,$nkey){
            foreach ($v_val1 as $k1 => $v1) {
              $q2->orwhere($nkey,"like", '%'.$v1.'%');
            }
          });          
        });
      }

      if($value["operator"]=='not_same'){
        $v_val1=explode(",",$value["value_1"]);

        $q->whereIn($pk_id, function($q1)use($table,$nkey,$v_val1) {
          $q1->from($table)->select('id');
          $q1->where(function ($q2)use($v_val1,$nkey){
            foreach ($v_val1 as $k1 => $v1) {
              $q2->orwhere($nkey,"not like", '%'.$v1.'%');
            }
          });          
        });
      }

      if($value["operator"]=='more_then'){
        $q->whereIn($pk_id, function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,'>',$value['value_1']);          
        });
      }
      
      if($value["operator"]=='more_and'){
        $q->whereIn($pk_id, function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,'>=',$value['value_1']);          
        });
      }

      if($value["operator"]=='less_then'){
        $q->whereIn($pk_id, function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,'<',$value['value_1']);          
        });
      }

      if($value["operator"]=='less_and'){
        $q->whereIn($pk_id, function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,'<=',$value['value_1']);          
        });
      }
    }

    if(array_search($value['type'],['date','datetime'])!==false){
      if($value['value_1'] || $value['value_2']){
        $date_from = $value['value_1'];
        if(!$date_from)
        throw new MyException([ "message" => "Date From pada ".$value['label']." harus diisi" ], 400);
  
        if(!strtotime($date_from))
        throw new MyException(["message"=>"Format Date pada ".$value['label']." From Tidak Cocok"], 400);

      
        $date_to = $value['value_2'];                
        if(!$date_to)
        throw new MyException([ "message" => "Date To pada ".$value['label']." harus diisi" ], 400);
      
        if(!strtotime($date_to))
        throw new MyException(["message"=>"Format Date To pada ".$value['label']." Tidak Cocok"], 400);
    
        if($value['operator']=="specific"){
          $date_from = date($value['type']=='datetime'?"Y-m-d H:i:s.v" :"Y-m-d",strtotime($date_from));
          $date_to = date($value['type']=='datetime'?"Y-m-d H:i:s.v" :"Y-m-d",strtotime($date_to));
          if(strtotime($date_from)>strtotime($date_to))
          throw new MyException(["message"=>"Pada ".$value['label']." Ada kesalahan, Cek kembali Date From dan To"], 400);
          
          $q->whereIn($pk_id, function($q1)use($table,$nkey,$date_from,$date_to) {
            $q1->from($table)
            ->select('id')->whereBetween($nkey,[$date_from,$date_to]);          
          });
        }
        
        if($value['operator']=="fullday"){
          $date_from = date("Y-m-d",strtotime($date_from))." 00:00:00.000";
          $date_to = date("Y-m-d",strtotime($date_to))." 23:59:59.999";

          if(strtotime($date_from)>strtotime($date_to))
          throw new MyException(["message"=>"Pada ".$value['label']." Ada kesalahan, Cek kembali Date From dan To"], 400);

          $q->whereIn($pk_id, function($q1)use($table,$nkey,$date_from,$date_to) {
            $q1->from($table)
            ->select('id')->where($nkey,">=",$date_from)->where($nkey,"<=",$date_to);          
          });
        }
      }
    }

    if(array_search($value['type'],['select'])!==false && $value['value_1']!==''){ 
      if($value["operator"]=='exactly_same'){
        $q->whereIn($pk_id, function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,$value['value_1']);          
        });
      }

      if($value["operator"]=='exactly_not_same'){
        $q->whereIn($pk_id, function($q1)use($table,$nkey,$value) {
          $q1->from($table)
          ->select('id')->where($nkey,"!=",$value['value_1']);          
        });
      }
    }
  }

  public static function queryCheckC1($table,$alias,$value,$key,$q){
    $nkey = str_replace($table."_","",$key);

    if(array_search($value['type'],['string','number'])!==false && $value['value_1']!==''){
  
      if($value["operator"]=='exactly_same'){
        $q->whereIn('id', function($q1)use($table,$alias,$nkey,$value) {
          $q1->from($table)
          ->select($alias.'_id')->where($nkey,$value['value_1']);          
        });
      }

      if($value["operator"]=='exactly_not_same'){
        $q->whereIn('id', function($q1)use($table,$alias,$nkey,$value) {
          $q1->from($table)
          ->select($alias.'_id')->where($nkey,'!=',$value['value_1']);          
        });
      }

      if($value["operator"]=='same'){
        $v_val1=explode(",",$value["value_1"]);

        $q->whereIn('id', function($q1)use($table,$alias,$nkey,$v_val1) {
          $q1->from($table)->select($alias.'_id');
          $q1->where(function ($q2)use($v_val1,$nkey){
            foreach ($v_val1 as $k1 => $v1) {
              $q2->orwhere($nkey,"like", '%'.$v1.'%');
            }
          });          
        });
      }

      if($value["operator"]=='not_same'){
        $v_val1=explode(",",$value["value_1"]);

        $q->whereIn('id', function($q1)use($table,$alias,$nkey,$v_val1) {
          $q1->from($table)->select($alias.'_id');
          $q1->where(function ($q2)use($v_val1,$nkey){
            foreach ($v_val1 as $k1 => $v1) {
              $q2->orwhere($nkey,"not like", '%'.$v1.'%');
            }
          });          
        });
      }

      if($value["operator"]=='more_then'){
        $q->whereIn('id', function($q1)use($table,$alias,$nkey,$value) {
          $q1->from($table)
          ->select($alias.'_id')->where($nkey,'>',$value['value_1']);          
        });
      }
      
      if($value["operator"]=='more_and'){
        $q->whereIn('id', function($q1)use($table,$alias,$nkey,$value) {
          $q1->from($table)
          ->select($alias.'_id')->where($nkey,'>=',$value['value_1']);          
        });
      }

      if($value["operator"]=='less_then'){
        $q->whereIn('id', function($q1)use($table,$alias,$nkey,$value) {
          $q1->from($table)
          ->select($alias.'_id')->where($nkey,'<',$value['value_1']);          
        });
      }

      if($value["operator"]=='less_and'){
        $q->whereIn('id', function($q1)use($table,$alias,$nkey,$value) {
          $q1->from($table)
          ->select($alias.'_id')->where($nkey,'<=',$value['value_1']);          
        });
      }
    }

    if(array_search($value['type'],['date','datetime'])!==false){
      if($value['value_1'] || $value['value_2']){
        $date_from = $value['value_1'];
        if(!$date_from)
        throw new MyException([ "message" => "Date From pada ".$value['label']." harus diisi" ], 400);
  
        if(!strtotime($date_from))
        throw new MyException(["message"=>"Format Date pada ".$value['label']." From Tidak Cocok"], 400);

      
        $date_to = $value['value_2'];                
        if(!$date_to)
        throw new MyException([ "message" => "Date To pada ".$value['label']." harus diisi" ], 400);
      
        if(!strtotime($date_to))
        throw new MyException(["message"=>"Format Date To pada ".$value['label']." Tidak Cocok"], 400);
    
        if($value['operator']=="specific"){
          $date_from = date($value['type']=='datetime'?"Y-m-d H:i:s.v" :"Y-m-d",strtotime($date_from));
          $date_to = date($value['type']=='datetime'?"Y-m-d H:i:s.v" :"Y-m-d",strtotime($date_to));
          if(strtotime($date_from)>strtotime($date_to))
          throw new MyException(["message"=>"Pada ".$value['label']." Ada kesalahan, Cek kembali Date From dan To"], 400);
          
          $q->whereIn('id', function($q1)use($table,$alias,$nkey,$date_from,$date_to) {
            $q1->from($table)
            ->select($alias.'_id')->whereBetween($nkey,[$date_from,$date_to]);          
          });
        }
        
        if($value['operator']=="fullday"){
          $date_from = date("Y-m-d",strtotime($date_from))." 00:00:00.000";
          $date_to = date("Y-m-d",strtotime($date_to))." 23:59:59.999";

          if(strtotime($date_from)>strtotime($date_to))
          throw new MyException(["message"=>"Pada ".$value['label']." Ada kesalahan, Cek kembali Date From dan To"], 400);

          $q->whereIn('id', function($q1)use($table,$alias,$nkey,$date_from,$date_to) {
            $q1->from($table)
            ->select($alias.'_id')->where($nkey,">=",$date_from)->where($nkey,"<=",$date_to);          
          });
        }
      }
    }

    if(array_search($value['type'],['select'])!==false && $value['value_1']!==''){ 
      if($value["operator"]=='exactly_same'){
        $q->whereIn('id', function($q1)use($table,$alias,$nkey,$value) {
          $q1->from($table)
          ->select($alias.'_id')->where($nkey,$value['value_1']);          
        });
      }

      if($value["operator"]=='exactly_not_same'){
        $q->whereIn('id', function($q1)use($table,$alias,$nkey,$value) {
          $q1->from($table)
          ->select($alias.'_id')->where($nkey,"!=",$value['value_1']);          
        });
      }
    }
  }

  public static function queryOrderP1($mq,$alias,$pk_id,$keyval,$keysort,$table=""){
    $nkey = str_replace($alias."_","",$keyval);
    if($table=="") $table = $alias;

    $mq = $mq->orderBy(function($q)use($table,$pk_id,$nkey){
      $q->from($table." as u")
      ->select("u.".$nkey)
      ->whereColumn("u.id",$pk_id);
    },$keysort);


    return $mq;
  }

}
