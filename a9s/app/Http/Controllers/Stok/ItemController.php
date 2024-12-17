<?php

namespace App\Http\Controllers\Stok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Helpers\MyLib;
use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Models\Stok\Item;
use App\Helpers\MyAdmin;
use App\Helpers\MyLog;
use App\Http\Requests\Stok\ItemRequest;
use App\Http\Resources\Stok\ItemResource;
use Exception;
use Illuminate\Support\Facades\DB;
use Image;
use File;
use App\Http\Resources\MySql\IsUserResource;
use App\Models\MySql\IsUser;

class ItemController extends Controller
{
  private $admin;
  private $admin_id;
  private $permissions;

  public function __construct(Request $request)
  {
    $this->admin = MyAdmin::user();
    $this->admin_id = $this->admin->the_user->id_user;
    $this->permissions = $this->admin->the_user->listPermissions();

  }

  public function index(Request $request)
  {

    MyAdmin::checkScope($this->permissions, 'item.views');

    // \App\Helpers\MyAdmin::checkScope($this->auth, ['ap-user-view']);

    //======================================================================================================
    // Pembatasan Data hanya memerlukan limit dan offset
    //======================================================================================================

    $limit = 250; // Limit +> Much Data
    if (isset($request->limit)) {
      if ($request->limit <= 250) {
        $limit = $request->limit;
      } else {
        throw new MyException(["message" => "Max Limit 250"]);
      }
    }

    $offset = isset($request->offset) ? (int) $request->offset : 0; // example offset 400 start from 401

    //======================================================================================================
    // Jika Halaman Ditentutkan maka $offset akan disesuaikan
    //======================================================================================================
    if (isset($request->page)) {
      $page =  (int) $request->page;
      $offset = ($page * $limit) - $limit;
    }


    //======================================================================================================
    // Init Model
    //======================================================================================================
    $model_query = Item::offset($offset)->limit($limit);

    $first_row=[];
    if($request->first_row){
      $first_row 	= json_decode($request->first_row, true);
    }
    //======================================================================================================
    // Model Filter | Example $request->like = "username:%username,role:%role%,name:role%,";
    //======================================================================================================

    if ($request->like) {
      $like_lists = [];

      $likes = explode(",", $request->like);
      foreach ($likes as $key => $like) {
        $side = explode(":", $like);
        $side[1] = isset($side[1]) ? $side[1] : '';
        $like_lists[$side[0]] = $side[1];
      }

      $list_to_like = ["id","name"];

      // $list_to_like_user = [
      //   ["val_name","val_user"],
      //   ["val1_name","val1_user"],
      //   ["val2_name","val2_user"],
      //   ["req_deleted_name","req_deleted_user"],
      //   ["deleted_name","deleted_user"],
      // ];

      

      if(count($like_lists) > 0){
        $model_query = $model_query->where(function ($q)use($like_lists,$list_to_like){
          foreach ($list_to_like as $key => $v) {
            if (isset($like_lists[$v])) {
              $q->orWhere($v, "like", $like_lists[$v]);
            }
          }

          // foreach ($list_to_like_user as $key => $v) {
          //   if (isset($like_lists[$v[0]])) {
          //     $q->orWhereIn($v[1], function($q2)use($like_lists,$v) {
          //       $q2->from('is_users')
          //       ->select('id')->where("username",'like',$like_lists[$v[0]]);          
          //     });
          //   }
          // }

        });        
      }     
    }

    // ==============
    // Model Filter
    // ==============

    $fm_sorts=[];
    if($request->filter_model){
      $filter_model = json_decode($request->filter_model,true);
  
      foreach ($filter_model as $key => $value) {
        if($value["sort_priority"] && $value["sort_type"]){
          array_push($fm_sorts,[
            "key"    =>$key,
            "priority"=>$value["sort_priority"],
          ]);
        }
      }

      if(count($fm_sorts)>0){
        usort($fm_sorts, function($a, $b) {return (int)$a['priority'] - (int)$b['priority'];});
        foreach ($fm_sorts as $key => $value) {
          $model_query = $model_query->orderBy($value['key'], $filter_model[$value['key']]["sort_type"]);
          if (count($first_row) > 0) {
            $sort_symbol = $filter_model[$value['key']]["sort_type"] == "desc" ? "<=" : ">=";
            $model_query = $model_query->where($value['key'],$sort_symbol,$first_row[$value['key']]);
          }
        }
      }

      $model_query = $model_query->where(function ($q)use($filter_model,$request){

        foreach ($filter_model as $key => $value) {
          if(!isset($value['type'])) continue;

          if(array_search($key,['status'])!==false){
          }else{
            MyLib::queryCheck($value,$key,$q);
          }
        }
        
         
       
        // if (isset($like_lists["requested_name"])) {
        //   $q->orWhereIn("requested_by", function($q2)use($like_lists) {
        //     $q2->from('is_users')
        //     ->select('id_user')->where("username",'like',$like_lists['requested_name']);          
        //   });
        // }
  
        // if (isset($like_lists["confirmed_name"])) {
        //   $q->orWhereIn("confirmed_by", function($q2)use($like_lists) {
        //     $q2->from('is_users')
        //     ->select('id_user')->where("username",'like',$like_lists['confirmed_name']);          
        //   });
        // }
      });  
    }
    
    if(!$request->filter_model || count($fm_sorts)==0){
      $model_query = $model_query->orderBy('name', 'asc');
    }
    
    $filter_status = $request->filter_status;
    
    // if($filter_status=="available"){
    //   $model_query = $model_query->where("deleted",0);
    // }

    // if($filter_status=="unapprove"){
    //   $model_query = $model_query->where("deleted",0)->where(function($q){
    //    $q->where("val",0); 
    //   });
    // }

    // if($filter_status=="deleted"){
    //   $model_query = $model_query->where("deleted",1);
    // }

    // if($filter_status=="req_deleted"){
    //   $model_query = $model_query->where("deleted",0);
    // }

    if ($request->exclude_lists) {
      $exclude_lists = json_decode($request->exclude_lists, true);
      if (count($exclude_lists) > 0) {
        // $exclude_lists = array_filter($exclude_lists,function ($x){return $x != "000.00.000";});
        $model_query = $model_query->whereNotIn("id", $exclude_lists);
      }
    }

    $model_query = $model_query->with(['created_by','updated_by','unit'])->get();

    return response()->json([
      // "data"=>EmployeeResource::collection($employees->keyBy->id),
      "data" => ItemResource::collection($model_query),
    ], 200);

  }

  public function show(ItemRequest $request)
  {
    // MyLib::checkScope($this->auth, ['ap-member-view']);
    MyAdmin::checkScope($this->permissions, 'item.view');

    $model_query = Item::with(['created_by', 'updated_by','unit'])->find($request->id);
    return response()->json([
      "data" => new ItemResource($model_query),
    ], 200);
  }

  public function store(ItemRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'item.create');

    // MyAdmin::checkRole($this->role, ['Super Admin','User','ClientPabrik','KTU']);

    $name = $request->name;
    $photo_preview = $request->photo_preview;

    // $filePath = "ho/images/stok/item/";
    // $file_name = null;
    $location = null;

    // $new_image = $request->file('photo');
    
    DB::beginTransaction();
    $t_stamp = date("Y-m-d H:i:s");

    try {

      // if ($new_image != null) {
      //   $date = new \DateTime();
      //   $timestamp = $date->format("Y-m-d H:i:s.v");
      //   $ext = $new_image->extension();
      //   $file_name = md5(preg_replace('/( |-|:)/', '', $timestamp)) . '.' . $ext;
      //   $location = $file_name;

      //   ini_set('memory_limit', '256M');
      //   $new_image->move(files_path($filePath), $file_name);
      // }

      $model_query                = new Item();
      $model_query->name          = $request->name;
      $model_query->value         = MyLib::emptyStrToNull($request->value);
      $model_query->note          = MyLib::emptyStrToNull($request->note);
      $model_query->st_unit_id    = MyLib::emptyStrToNull($request->unit_id);
      $model_query->created_at    = $t_stamp;
      $model_query->created_user  = $this->admin_id;
      $model_query->updated_at    = $t_stamp;
      $model_query->updated_user  = $this->admin_id;
      $model_query->photo         = $location;
      $model_query->save();
      MyLog::sys("item",$model_query->id,"insert");

      DB::commit();
      return response()->json([
        "message" => "Proses tambah data berhasil",
        "id"=>$model_query->id,
        "created_at" => $t_stamp,
        "created_user"=>$model_query->created_user,
        "created_by"=>$model_query->created_user ? new IsUserResource(IsUser::where("id_user",$model_query->created_user)->first()) : null,
        "updated_at" => $t_stamp,
        "updated_user"=>$model_query->updated_user,
        "updated_by"=>$model_query->updated_user ? new IsUserResource(IsUser::where("id_user",$model_query->updated_user)->first()) : null,
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();

      // if ($new_image != null && File::exists(files_path($filePath.$location)) && $location != null) {
      //   unlink(files_path($filePath.$location));
      // }

      // return response()->json([
      //   "message" => $e->getMessage(),
      // ], 400);
      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }

      return response()->json([
        "message" => "Proses tambah data gagal",
        // "message" => $e->getMessage(),

      ], 400);
    }
  }

  public function update(ItemRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'item.modify');

    $photo_preview = $request->photo_preview;

    // $filePath = "ho/images/stok/item/";
    // $file_name = null;
    $location = null;

    // $new_image = $request->file('photo');

    DB::beginTransaction();
    $t_stamp = date("Y-m-d H:i:s");

    try {
      $model_query             = Item::find($request->id);
      $SYSOLD                  = clone($model_query);

      $location = $model_query->photo;
      // if ($new_image != null) {
      //   $date = new \DateTime();
      //   $timestamp = $date->format("Y-m-d H:i:s.v");
      //   $ext = $new_image->extension();
      //   $file_name = md5(preg_replace('/( |-|:)/', '', $timestamp)) . '.' . $ext;
      //   $location = $file_name;

      //   ini_set('memory_limit', '256M');
      //   $new_image->move(files_path($filePath), $file_name);
      // }

      // if ($new_image == null && $photo_preview == null) {
      //   $location = null;
      // }


      // if ($photo_preview == null) {
      //   if (File::exists(files_path($filePath.$model_query->photo)) && $model_query->photo != null) {
      //     if(!unlink(files_path($filePath.$model_query->photo)))
      //     throw new \Exception("Gagal",1);
      //   }
      // }

      $model_query->name          = $request->name;
      $model_query->value         = MyLib::emptyStrToNull($request->value);
      $model_query->note          = MyLib::emptyStrToNull($request->note);
      $model_query->st_unit_id    = MyLib::emptyStrToNull($request->unit_id);
      $model_query->updated_at    = $t_stamp;
      $model_query->updated_user  = $this->admin_id;
      $model_query->photo         = $location;
      $model_query->save();

      $SYSNOTE = MyLib::compareChange($SYSOLD,$model_query); 
      MyLog::sys("item",$request->id,"update",$SYSNOTE);

      DB::commit();
      return response()->json([
        "message" => "Proses ubah data berhasil",
        "updated_at" => $t_stamp,
        "updated_user"=>$model_query->updated_user,
        "updated_by"=>$model_query->updated_user ? new IsUserResource(IsUser::where("id_user",$model_query->updated_user)->first()) : null,
      ], 200);

    } catch (\Exception $e) {
      DB::rollback();

      // if ($new_image != null && File::exists(files_path($filePath.$location)) && $location != null) {
      //   unlink(files_path($filePath.$location));
      // }
      
      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }

      return response()->json([
        // "line" => $e->getLine(),
        "message" => $e->getMessage(),
      ], 400);

      return response()->json([
        "message" => "Proses ubah data gagal"
      ], 400);
    }
  }

  public function delete(ItemRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'item.remove');

    DB::beginTransaction();

    try {
      $model_query = Item::find($request->id);
      if (!$model_query) {
        throw new \Exception("Data tidak terdaftar", 1);
      }
      $model_query->delete();
      MyLog::sys("item",$request->id,"delete");
      DB::commit();
      return response()->json([
        "message" => "Proses hapus data berhasil",
      ], 200);
    } catch (\Exception  $e) {
      DB::rollback();
      if ($e->getCode() == "23000")
        return response()->json([
          "message" => "Data tidak dapat dihapus, data terkait dengan data yang lain nya",
        ], 400);

      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }

      return response()->json([
        "message" => "Proses hapus data gagal",
      ], 400);
      //throw $th;
    }
  }
}
