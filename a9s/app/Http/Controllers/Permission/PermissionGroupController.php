<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Exceptions\MyException;

use App\Helpers\MyLib;
use App\Helpers\MyAdmin;
use App\Helpers\MyLog;

use App\Models\MySql\PermissionGroup;

use App\Http\Requests\MySql\PermissionGroupRequest;

use App\Http\Resources\MySql\PermissionGroupResource;

use App\Models\MySql\PermissionGroupDetail;
use App\Models\MySql\PermissionGroupUser;

use Exception;
class PermissionGroupController extends Controller
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
    MyAdmin::checkScope($this->permissions, 'permission_group.views');
 
    //======================================================================================================
    // Pembatasan Data hanya memerlukan limit dan offset
    //======================================================================================================

    $limit = 50; // Limit +> Much Data
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
    $model_query = PermissionGroup::offset($offset)->limit($limit);

    $first_row=[];
    if($request->first_row){
      $first_row 	= json_decode($request->first_row, true);
    }

    //======================================================================================================
    // Model Sorting | Example $request->sort = "username:desc,role:desc";
    //======================================================================================================
    

    if ($request->sort) {
      $sort_lists = [];

      $sorts = explode(",", $request->sort);
      foreach ($sorts as $key => $sort) {
        $side = explode(":", $sort);
        $side[1] = isset($side[1]) ? $side[1] : 'ASC';
        $sort_symbol = $side[1] == "desc" ? "<=" : ">=";
        $sort_lists[$side[0]] = $side[1];
      }

      if (isset($sort_lists["id"])) {
        $model_query = $model_query->orderBy("id", $sort_lists["id"]);
        if (count($first_row) > 0) {
          $model_query = $model_query->where("id",$sort_symbol,$first_row["id"]);
        }
      }

      if (isset($sort_lists["name"])) {
        $model_query = $model_query->orderBy("name", $sort_lists["name"]);
        if (count($first_row) > 0) {
          $model_query = $model_query->where("name",$sort_symbol,$first_row["name"]);
        }
      }
      

    } else {
      $model_query = $model_query->orderBy('updated_at', 'DESC');
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

      if(count($like_lists) > 0){
        $model_query = $model_query->where(function ($q)use($like_lists){
            
          if (isset($like_lists["id"])) {
            $q->orWhere("id", "like", $like_lists["id"]);
          }
    
          if (isset($like_lists["name"])) {
            $q->orWhere("name", "like", $like_lists["name"]);
          }
        });        
      }

      
    }

    // ==============
    // Model Filter
    // ==============

    $model_query = $model_query->get();

    return response()->json([
      "data" => PermissionGroupResource::collection($model_query),
    ], 200);
  }

  public function show(PermissionGroupRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'permission_group.view');

    $model_query = PermissionGroup::with([
      'details'=>function($q){
        $q->orderBy("ordinal","asc");
      },
      'group_users'=>function($q){
        $q->orderBy("ordinal","asc");
        $q->with('user');
      },
    ])->find($request->id);

    return response()->json([
      "data" => new PermissionGroupResource($model_query),
    ], 200);
  }

  public function validateItems($permission_list_in){
    $rules = [      
      // 'permission_list'                      => 'required|array',
      'permission_list.*.name'               => 'required|exists:App\Models\MySql\PermissionList,name',
    ];

    $messages = [
      // 'permission_list.required' => 'List Item harus di isi',
      // 'permission_list.array' => 'Format Pengambilan Barang Salah',
    ];

    foreach ($permission_list_in as $index => $msg) {
      $messages["permission_list.{$index}.name.required"]  = "Baris #" . ($index + 1) . ". Nama tidak boleh kosong.";
      $messages["permission_list.{$index}.name.exists"]    = "Baris #" . ($index + 1) . ". Nama tidak terdaftar.";
    }

    $validator = Validator::make(['permission_list' => $permission_list_in], $rules, $messages);

    // Check if validation fails
    if ($validator->fails()) {
      foreach ($validator->messages()->all() as $k => $v) {
        throw new MyException(["message" => $v], 400);
      }
    }
  }

  public function validateItems2($users_in){
    $rules = [      
      // 'users'                      => 'required|array',
      'users.*.id' => 'required|exists:App\Models\MySql\IsUser,id_user',
    ];

    $messages = [
      // 'users.required' => 'List Item harus di isi',
      // 'users.array' => 'Format Pengambilan Barang Salah',
    ];

    foreach ($users_in as $index => $msg) {
      $messages["users.{$index}.id.required"]  = "Baris #" . ($index + 1) . ". User ID tidak boleh kosong.";
      $messages["users.{$index}.id.exists"]    = "Baris #" . ($index + 1) . ". User ID tidak terdaftar.";
    }

    $validator = Validator::make(['users' => $users_in], $rules, $messages);

    // Check if validation fails
    if ($validator->fails()) {
      foreach ($validator->messages()->all() as $k => $v) {
        throw new MyException(["message" => $v], 400);
      }
    }
  }

  public function store(PermissionGroupRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'permission_group.create');

    $permission_list_in = json_decode($request->permission_list, true);
    $this->validateItems($permission_list_in);

    $users_in = json_decode($request->users, true);
    $this->validateItems2($users_in);

    if(count($permission_list_in)>0){
      MyAdmin::checkScope($this->permissions, 'permission_group_detail.insert');
    }

    if(count($users_in)>0){
      MyAdmin::checkScope($this->permissions, 'permission_group_user.insert');
    }

    $rollback_id = -1;
    DB::beginTransaction();
    $t_stamp = date("Y-m-d H:i:s");
    try {

      $model_query                  = new PermissionGroup();      
      $model_query->name            = $request->name;

      $model_query->created_at      = $t_stamp;
      $model_query->created_user    = $this->admin_id;

      $model_query->updated_at      = $t_stamp;
      $model_query->updated_user    = $this->admin_id;

      $model_query->save();
      $rollback_id = $model_query->id - 1;

      $ordinal=0;
      foreach ($permission_list_in as $key => $value) {
        $ordinal = $key + 1;
        PermissionGroupDetail::insert([
          'ordinal' => $ordinal,
          'st_permission_group_id' => $model_query->id,
          'st_permission_list_name' => $value['name'],
          'created_at' => $t_stamp,
          'created_user' => $this->admin_id,
          'updated_at' => $t_stamp,
          'updated_user' => $this->admin_id,
        ]);
      }


      $ordinal=0;
      foreach ($users_in as $key => $value) {
        $ordinal = $key + 1;
        PermissionGroupUser::insert([
          'ordinal' => $ordinal,
          'st_permission_group_id' => $model_query->id,
          'user_id' => $value['id'],
          'created_at' => $t_stamp,
          'created_user' => $this->admin_id,
          'updated_at' => $t_stamp,
          'updated_user' => $this->admin_id,
        ]);
      }

      MyLog::sys("permission_group",$model_query->id,"insert");

      DB::commit();
      return response()->json([
        "message" => "Proses tambah data berhasil",
        "id"=>$model_query->id,
        "created_at" => $t_stamp,
        "updated_at" => $t_stamp,
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();

      if($rollback_id>-1)
      DB::statement("ALTER TABLE st_permission_group AUTO_INCREMENT = $rollback_id");
      
      return response()->json([
        "message" => $e->getMessage(),
      ], 400);
      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }

      return response()->json([
        "message" => "Proses tambah data gagal",
      ], 400);
    }
  }

  public function update(PermissionGroupRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'permission_group.modify');
    $t_stamp = date("Y-m-d H:i:s");

    //start for details2
    $permission_list_in = json_decode($request->permission_list, true);
    $this->validateItems($permission_list_in);
    //end for permission_list2

    $users_in = json_decode($request->users, true);
    $this->validateItems2($users_in);

    DB::beginTransaction();
    try {
      $SYSNOTES=[];
      $model_query = PermissionGroup::where("id",$request->id)->lockForUpdate()->first();
      $SYSOLD      = clone($model_query);

      $model_query->name            = $request->name;
      $model_query->updated_at      = $t_stamp;
      $model_query->updated_user    = $this->admin_id;

      //start for permission_list
      array_push( $SYSNOTES ,"Details Permission List: \n");
      $data_from_db = PermissionGroupDetail::where('st_permission_group_id', $model_query->id)
      ->orderBy("ordinal", "asc")
      ->lockForUpdate()
      ->get();
     
      $in_dt = array_map(function ($x) {
        return $x["name"];
      }, $permission_list_in);

      $from_dt = array_map(function ($x) {
        return $x['st_permission_list_name'];
      }, $data_from_db->toArray());


      if(count(array_diff($in_dt, $from_dt))>0) {
        MyAdmin::checkScope($this->permissions, 'permission_group_detail.insert');
      }

      if(count(array_diff($from_dt, $in_dt))>0) {
        MyAdmin::checkScope($this->permissions, 'permission_group_detail.remove');
      }

      //start for user
      $data_from_db2 = PermissionGroupUser::where('st_permission_group_id', $model_query->id)
      ->orderBy("ordinal", "asc")
      ->lockForUpdate()
      ->get();
     
      $in_dt2 = array_map(function ($x) {
        return $x["id"];
      }, $users_in);

      $from_dt2 = array_map(function ($x) {
        return $x['user_id'];
      }, $data_from_db2->toArray());


      if(count(array_diff($in_dt2, $from_dt2))>0) {
        MyAdmin::checkScope($this->permissions, 'permission_group_user.insert');
      }

      if(count(array_diff($from_dt2, $in_dt2))>0) {
        MyAdmin::checkScope($this->permissions, 'permission_group_user.remove');
      }

      $ordinal=0;
      foreach ($data_from_db as $k => $v) {
        $search = array_search($v->st_permission_list_name,$in_dt);
        if($search===false){
          array_push( $SYSNOTES ,"permission list name:".$v->st_permission_list_name." [Deleted]");
          PermissionGroupDetail::where('st_permission_group_id',$v->st_permission_group_id)->where('st_permission_list_name',$v->st_permission_list_name)
          ->delete();

        }else{
          $ordinal++;
          $vToCom = clone($v);

          $updateV = [
            'ordinal' => $ordinal,
            'p_change' => false,
            'updated_at' => $t_stamp,
            'updated_user' => $this->admin_id
          ];

          PermissionGroupDetail::where('st_permission_group_id',$v->st_permission_group_id)->where('st_permission_list_name',$v->st_permission_list_name)
          ->update($updateV);

          $SYSNOTE = MyLib::compareChange($vToCom,$updateV);
          array_push( $SYSNOTES ,"Ordinal ".$ordinal."\n".$SYSNOTE);
        }
        
        $in_dt = array_filter($in_dt,function($q)use($v){
          return $q != $v->st_permission_list_name;
        });
      }

      foreach ($in_dt as $k => $v) {
        $ordinal++;

        array_push( $SYSNOTES ,"Ordinal ".$ordinal." [Insert]");

        PermissionGroupDetail::insert([
            'st_permission_group_id'   => $model_query->id,
            'ordinal'               => $ordinal,
            "st_permission_list_name"  => $v,
            'created_at'      => $t_stamp,
            'created_user'    => $this->admin_id,
            'updated_at'      => $t_stamp,
            'updated_user'    => $this->admin_id,
        ]);
      }

      array_push( $SYSNOTES ,"Details Users: \n");

      $ordinal=0;
      foreach ($data_from_db2 as $k => $v) {
        $search = array_search($v->user_id,$in_dt2);
        if($search===false){
          array_push( $SYSNOTES ,"User ID:".$v->user_id." [Deleted]");
          PermissionGroupUser::where('st_permission_group_id',$v->st_permission_group_id)->where('user_id',$v->user_id)
          ->delete();

        }else{
          $ordinal++;
          $vToCom = clone($v);

          $updateV = [
            'ordinal' => $ordinal,
            'p_change' => false,
            'updated_at' => $t_stamp,
            'updated_user' => $this->admin_id
          ];

          PermissionGroupUser::where('st_permission_group_id',$v->st_permission_group_id)->where('user_id',$v->user_id)
          ->update($updateV);

          $SYSNOTE = MyLib::compareChange($vToCom,$updateV);
          array_push( $SYSNOTES ,"Ordinal ".$ordinal."\n".$SYSNOTE);
        }
        
        $in_dt2 = array_filter($in_dt2,function($q)use($v){
          return $q != $v->user_id;
        });
      }

      foreach ($in_dt2 as $k => $v) {
        $ordinal++;

        array_push( $SYSNOTES ,"Ordinal ".$ordinal." [Insert]");

        PermissionGroupUser::insert([
            'st_permission_group_id'   => $model_query->id,
            'ordinal'               => $ordinal,
            "user_id"               => $v,
            'created_at'            => $t_stamp,
            'created_user'          => $this->admin_id,
            'updated_at'            => $t_stamp,
            'updated_user'          => $this->admin_id,
        ]);
      }

      $model_query->save();

      $SYSNOTE = MyLib::compareChange($SYSOLD,$model_query); 
      array_unshift( $SYSNOTES , $SYSNOTE );            
      MyLog::sys("permission_group",$request->id,"update",implode("\n",$SYSNOTES));

    //end for permission_list2
      DB::commit();
      return response()->json([
        "message" => "Proses ubah data berhasil",
        "updated_at"=>$t_stamp
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();
      // return response()->json([
      //   "getCode" => $e->getCode(),
      //   "line" => $e->getLine(),
      //   "message" => $e->getMessage(),
      // ], 400);

      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }
      
      return response()->json([
        "message" => "Proses ubah data gagal",
      ], 400);
    }
  }

  public function delete(Request $request)
  {

    MyAdmin::checkScope($this->permissions, 'permission_group.remove');

    DB::beginTransaction();

    try {
      $deleted_reason = $request->deleted_reason;
      if(!$deleted_reason)
      throw new \Exception("Sertakan Alasan Penghapusan",1);
    
      $model_query = PermissionGroup::where("id",$request->id)->lockForUpdate()->first();

      if (!$model_query) {
        throw new \Exception("Data tidak terdaftar", 1);
      }
  
      $model_query->deleted         = 1;
      $model_query->deleted_user    = $this->admin_id;
      $model_query->deleted_at      = date("Y-m-d H:i:s");
      $model_query->deleted_reason  = $deleted_reason;
      $model_query->save();

      MyLog::sys("permission_group",$request->id,"delete");

      DB::commit();
      return response()->json([
        "message" => "Proses Hapus data berhasil",
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
