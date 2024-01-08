<?php
//app/Helpers/Envato/User.php
namespace App\Helpers;

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
}
