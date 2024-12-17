<?php
//app/Helpers/Envato/User.php
namespace App\Helpers;

use App\Models\MySql\Syslog;
use Illuminate\Support\Facades\DB;
use File;
use Request;

class MyLog {


    public static function error($e)
    {
      $date=new \DateTime();
      $timestamp=$date->format("Y-m-d H:i:s.v");
      $today=date("Y-m-d");
      $filename="/logs/errors.".$today.".log";
      // $content="[".$timestamp."] ".json_encode($e,JSON_PRETTY_PRINT).PHP_EOL;
      $content="[".$timestamp."] ".vsprintf("%s:%d %s (%d)\n", array($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode()));
      File::append(storage_path($filename),$content);
    }

    public static function logging($msg,$report_name = 'report')
    {
      $date=new \DateTime();
      $timestamp=$date->format("Y-m-d H:i:s.v");
      $today=date("Y-m-d");
      $filename="/logs/.".$report_name.$today.".log";
      $content="[".$timestamp."] ".json_encode($msg,JSON_PRETTY_PRINT).PHP_EOL;
      File::append(storage_path($filename),$content);
    }

    public static function sys($module,$module_id,$action,$note="")
    {
      $date=new \DateTime();
      $created_at=$date->format("Y-m-d H:i:s.v");

      $token = Request::bearerToken();
      if($token){
        $user = MyAdmin::user();
        $user_id = $user->the_user->id;
      }
      else{
        $user_id = null;
      }

      Syslog::insert([
        "created_at"=>$created_at,
        "ip_address"=>getRealIpAddress(),
        "created_user"=>$user_id,
        "module"=>$module,
        "module_id"=>$module_id,
        "action"=>$action,
        "note"=>$note,
      ]);
      // $today=date("Y-m-d");
      // $filename="/logs/data_history".$today.".log";

      // $content="[".$timestamp."] ".getRealIpAddress()." ".json_encode($msg,JSON_PRETTY_PRINT).PHP_EOL;
      
      // File::append(storage_path($filename),$content);

      

    }
}
