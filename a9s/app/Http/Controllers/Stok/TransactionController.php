<?php

namespace App\Http\Controllers\Stok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Helpers\MyLib;
use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Models\Stok\Transaction;
use App\Helpers\MyAdmin;
use App\Http\Requests\Stok\TransactionRequest;
use App\Http\Resources\Stok\TransactionResource;
use Exception;
use Illuminate\Support\Facades\DB;
use Image;
use File;

class TransactionController extends Controller
{
  private $admin;
  private $role;
  private $admin_id;

  public function __construct(Request $request)
  {
    $this->admin = MyAdmin::user();
    $this->role = $this->admin->the_user->hak_akses;
    $this->admin_id = $this->admin->the_user->id_user;
  }

  public function index(Request $request)
  {
    MyAdmin::checkRole($this->role, ['User','ClientPabrik']);

    //======================================================================================================
    // Pembatasan Data hanya memerlukan limit dan offset
    //======================================================================================================

    $limit = 30; // Limit +> Much Data
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
    $model_query = Transaction::offset($offset)->limit($limit);

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

      if (isset($sort_lists["name"])) {
        $model_query = $model_query->orderBy("name", $sort_lists["name"]);
        if (count($first_row) > 0) {
          $model_query = $model_query->where("name",$sort_symbol,$first_row["name"]);
        }
      }

      if (isset($sort_lists["id"])) {
        $model_query = $model_query->orderBy("id", $sort_lists["id"]);
        if (count($first_row) > 0) {
          $model_query = $model_query->where("id",$sort_symbol,$first_row["id"]);
        }
      }

      if (isset($sort_lists["updated_at"])) {
        $model_query = $model_query->orderBy("updated_at", $sort_lists["updated_at"]);
        if (count($first_row) > 0) {
          $model_query = $model_query->where("updated_at",$sort_symbol,$first_row["updated_at"]);
        }
      }

      // if (isset($sort_lists["fullname"])) {
      //   $model_query = $model_query->orderBy("fullname", $sort_lists["fullname"]);
      // }

      // if (isset($sort_lists["role"])) {
      //   $model_query = $model_query->orderBy("role", $sort_lists["role"]);
      // }
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
          
          if (isset($like_lists["warehouse_name"])) {
            $q->orWhereIn("hrm_revisi_lokasi_id", function($q2)use($like_lists) {
              $q2->from('hrm_revisi_lokasi')
              ->select('id')->where("lokasi",'like',$like_lists['warehouse_name']);          
            });
          }
    
          // if (isset($like_lists["warehouse_source_name"])) {
          //   $q->orWhereIn("hrm_revisi_lokasi_source_id", function($q2)use($like_lists) {
          //     $q2->from('hrm_revisi_lokasi')
          //     ->select('id')->where("lokasi",'like',$like_lists['warehouse_source_name']);          
          //   });
          // }
    
          // if (isset($like_lists["warehouse_target_name"])) {
          //   $q->orWhereIn("hrm_revisi_lokasi_target_id", function($q2)use($like_lists) {
          //     $q2->from('hrm_revisi_lokasi')
          //     ->select('id')->where("lokasi",'like',$like_lists['warehouse_target_name']);          
          //   });
          // }
    
    
          if (isset($like_lists["id"])) {
            $q->orWhere("id", "like", $like_lists["id"]);
          }
    
    
          if (isset($like_lists["item_name"])) {
            $q->orWhereIn("st_item_id", function($q2)use($like_lists) {
              $q2->from('st_items')
              ->select('id')->where("name",'like',$like_lists['item_name']);          
            });
          }
    
          if (isset($like_lists["status"])) {
            $q->orWhere("status", "like", $like_lists["status"]);
          }
    
          if (isset($like_lists["type"])) {
            $q->orWhere("type", "like", $like_lists["type"]);
          }
    
          if (isset($like_lists["requested_name"])) {
            $q->orWhereIn("requested_by", function($q2)use($like_lists) {
              $q2->from('is_users')
              ->select('id_user')->where("username",'like',$like_lists['requested_name']);          
            });
          }
    
          if (isset($like_lists["confirmed_name"])) {
            $q->orWhereIn("confirmed_by", function($q2)use($like_lists) {
              $q2->from('is_users')
              ->select('id_user')->where("username",'like',$like_lists['confirmed_name']);          
            });
          }
        });        
      }

      

      // if (isset($like_lists["fullname"])) {
      //   $model_query = $model_query->orWhere("fullname", "like", $like_lists["fullname"]);
      // }

      // if (isset($like_lists["role"])) {
      //   $model_query = $model_query->orWhere("role", "like", $like_lists["role"]);
      // }
    }

    // ==============
    // Model Filter
    // ==============


    // if (isset($request->id)) {
    //   $model_query = $model_query->where("id", 'like', '%' . $request->id . '%');
    // }
    // if (isset($request->name)) {
    //   $model_query = $model_query->where("name", 'like', '%' . $request->name . '%');
    // }
    // if (isset($request->fullname)) {
    //   $model_query = $model_query->where("fullname", 'like', '%' . $request->fullname . '%');
    // }
    // if (isset($request->role)) {
    //   $model_query = $model_query->where("role", 'like', '%' . $request->role . '%');
    // }

    if($this->role=='ClientPabrik'){
      $model_query = $model_query->whereIn("hrm_revisi_lokasi_id",$this->admin->the_user->hrm_revisi_lokasis());
    }
    $model_query = $model_query->orderBy("ref_id","desc");
    $model_query = $model_query->with(['warehouse','warehouse_source','warehouse_target','requester', 'confirmer',
    'item'=>function($q){
      $q->with('unit');      
    }])->get();


    $model_query_notify = Transaction::whereNull("confirmed_by")->get();
    if($this->role=='ClientPabrik'){
      $model_query_notify = $model_query_notify->whereIn("hrm_revisi_lokasi_id",$this->admin->the_user->hrm_revisi_lokasis());
    }

    return response()->json([
      // "data"=>EmployeeResource::collection($employees->keyBy->id),
      "data" => TransactionResource::collection($model_query),
      "request_notif"=> count($model_query_notify)
    ], 200);
  }

  public function show(TransactionRequest $request)
  {
    // MyLib::checkScope($this->auth, ['ap-member-view']);

    $model_query = Transaction::with(['warehouse','warehouse_source','warehouse_target','requester', 'confirmer',
    'item'=>function($q){
      $q->with('unit');      
    }])->find($request->id);

    return response()->json([
      "data" => new TransactionResource($model_query),
    ], 200);
  }

  public function store(TransactionRequest $request)
  {
    MyAdmin::checkRole($this->role, ['User','ClientPabrik']);

    $name = $request->name;


    DB::beginTransaction();
    try {
      


      $model_query                         = new Transaction();
      
      $warehouse_id = $request->warehouse_id; // lokasi yang di kelola
      if($this->role=='ClientPabrik')
      $warehouse_id = MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$request->warehouse_id);
      
      $model_query->hrm_revisi_lokasi_id        = $warehouse_id;
      
      $model_query->st_item_id                  = $request->item_id;
      $model_query->note                        = MyLib::emptyStrToNull($request->note);

      $type = $request->type;
      $model_query->type                        = $type;

      if($type=="transfer" || $type=="used"){
        if($request->qty_out==0) {
          throw new \Exception("Qty Tidak Boleh 0",1);
        }
        $model_query->qty_out                     = $request->qty_out;
      }else{
        if($request->qty_in==0) {
          throw new \Exception("Qty Tidak Boleh 0",1);
        }
        $model_query->qty_in                      = $request->qty_in;
      }

      $model_query->status                      = "done";
      
      $warehouse_target_id = $request->warehouse_target_id;
      if($type=="transfer"){
        if($warehouse_id==$warehouse_target_id)
        throw new \Exception("Warehouse tidak boleh sama",1);

        $model_query->hrm_revisi_lokasi_source_id = $warehouse_id;
        $model_query->hrm_revisi_lokasi_target_id = $warehouse_target_id;
      }elseif($type=="used"){
        $model_query->hrm_revisi_lokasi_source_id = $warehouse_id;
        $model_query->hrm_revisi_lokasi_target_id = $warehouse_id;
      }elseif($type=="in"){
        $model_query->hrm_revisi_lokasi_source_id = null;
        $model_query->hrm_revisi_lokasi_target_id = $warehouse_id;
      }
      
      $model_query->requested_at              = date("Y-m-d H:i:s");
      $model_query->requested_by              = $this->admin_id;
      $model_query->confirmed_at               = date("Y-m-d H:i:s");
      $model_query->confirmed_by               = $this->admin_id;

      $model_query->created_at                = date("Y-m-d H:i:s");
      $model_query->updated_at                = date("Y-m-d H:i:s");


      $qty_reminder = 0;
      $dt_before = Transaction::where('hrm_revisi_lokasi_id',$warehouse_id)
      ->where('st_item_id',$request->item_id)
      ->whereNotNull('confirmed_by')
      ->orderBy("updated_at","desc")
      ->orderBy("ref_id","desc")
      ->lockForUpdate()->first();
      
      if($dt_before)
      $qty_reminder = $dt_before->qty_reminder;
      else{
        if($type!="in"){
          throw new \Exception("Stok Awal Diperlukan", 1);
        }
      }
      if($type=="in"){
        $qty_reminder += $request->qty_in;
      }else{

        if($qty_reminder - $request->qty_out < 0){
          throw new \Exception("Qty melebihi stok : ".$qty_reminder, 1);
        }

        $qty_reminder -= $request->qty_out;
      }

      
      
      $model_query->qty_reminder                = $qty_reminder;

      $model_query->save();



      if($type=="transfer"){
        $model_query2                         = new Transaction();
        $model_query2->ref_id = $model_query->id;
        $model_query2->hrm_revisi_lokasi_id        = $request->warehouse_target_id;
        $model_query2->st_item_id                  = $request->item_id;
        $model_query2->type                        = $type;
        $model_query2->qty_in                      = $request->qty_out;
        $model_query2->status                      = "pending";
        $model_query2->hrm_revisi_lokasi_source_id  = $warehouse_id;
        $model_query2->hrm_revisi_lokasi_target_id  = $warehouse_target_id;
        $model_query2->requested_at                 = date("Y-m-d H:i:s");
        $model_query2->requested_by                 = $this->admin_id;
        $model_query2->confirmed_at                  = null;
        $model_query2->created_at                   = date("Y-m-d H:i:s");
        $model_query2->updated_at                   = date("Y-m-d H:i:s");
        $model_query2->save();

      }
      DB::commit();
      return response()->json([
        "message" => "Proses tambah data berhasil",
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();
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

  // public function update(TransactionRequest $request)
  // {
  //   MyAdmin::checkRole($this->role, ['User','ClientPabrik']);

  //   DB::beginTransaction();
  //   try {
  //     $model_query             = Transaction::find($request->id);
  //     $model_query->name       = $request->name;
  //     $model_query->value      = MyLib::emptyStrToNull($request->value);
  //     $model_query->note       = MyLib::emptyStrToNull($request->note);
  //     $model_query->st_unit_id = MyLib::emptyStrToNull($request->unit_id);
  //     $model_query->updated_at = date("Y-m-d H:i:s");
  //     $model_query->updated_by = $this->admin_id;
  //     $model_query->save();

  //     DB::commit();
  //     return response()->json([
  //       "message" => "Proses ubah data berhasil",
  //     ], 200);
  //   } catch (\Exception $e) {
  //     DB::rollback();
  //     if ($e->getCode() == 1) {
  //       return response()->json([
  //         "message" => $e->getMessage(),
  //       ], 400);
  //     }
  //     return response()->json([
  //       // "line" => $e->getLine(),
  //       "message" => $e->getMessage(),
  //     ], 400);
  //     return response()->json([
  //       "message" => "Proses ubah data gagal"
  //     ], 400);
  //   }
  // }

  // public function delete(TransactionRequest $request)
  // {
  //   MyAdmin::checkRole($this->role, ['User','ClientPabrik']);

  //   DB::beginTransaction();

  //   try {
  //     $model_query = Transaction::find($request->id);
  //     if (!$model_query) {
  //       throw new \Exception("Data tidak terdaftar", 1);
  //     }
  //     $model_query->delete();

  //     DB::commit();
  //     return response()->json([
  //       "message" => "Proses ubah data berhasil",
  //     ], 200);
  //   } catch (\Exception  $e) {
  //     DB::rollback();
  //     if ($e->getCode() == "23503")
  //       return response()->json([
  //         "message" => "Data tidak dapat dihapus, data masih terkait dengan data yang lain nya",
  //       ], 400);

  //     if ($e->getCode() == 1) {
  //       return response()->json([
  //         "message" => $e->getMessage(),
  //       ], 400);
  //     }

  //     return response()->json([
  //       "message" => "Proses hapus data gagal",
  //     ], 400);
  //     //throw $th;
  //   }
  // }


  public function request_transactions(Request $request)
  {
    MyAdmin::checkRole($this->role, ['User','ClientPabrik']);

    //======================================================================================================
    // Pembatasan Data hanya memerlukan limit dan offset
    //======================================================================================================

    $limit = 30; // Limit +> Much Data
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
    $model_query = Transaction::offset($offset)->limit($limit);

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

      if (isset($sort_lists["name"])) {
        $model_query = $model_query->orderBy("name", $sort_lists["name"]);
        if (count($first_row) > 0) {
          $model_query = $model_query->where("name",$sort_symbol,$first_row["name"]);
        }
      }

      if (isset($sort_lists["id"])) {
        $model_query = $model_query->orderBy("id", $sort_lists["id"]);
        if (count($first_row) > 0) {
          $model_query = $model_query->where("id",$sort_symbol,$first_row["id"]);
        }
      }

      if (isset($sort_lists["created_at"])) {
        $model_query = $model_query->orderBy("created_at", $sort_lists["created_at"]);
        if (count($first_row) > 0) {
          $model_query = $model_query->where("created_at",$sort_symbol,$first_row["created_at"]);
        }
      }

      // if (isset($sort_lists["fullname"])) {
      //   $model_query = $model_query->orderBy("fullname", $sort_lists["fullname"]);
      // }

      // if (isset($sort_lists["role"])) {
      //   $model_query = $model_query->orderBy("role", $sort_lists["role"]);
      // }
    } else {
      $model_query = $model_query->orderBy('created_at', 'DESC');
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
          
          if (isset($like_lists["warehouse_name"])) {
            $q->orWhereIn("hrm_revisi_lokasi_id", function($q2)use($like_lists) {
              $q2->from('hrm_revisi_lokasi')
              ->select('id')->where("lokasi",'like',$like_lists['warehouse_name']);          
            });
          }
    
          if (isset($like_lists["warehouse_source_name"])) {
            $q->orWhereIn("hrm_revisi_lokasi_source_id", function($q2)use($like_lists) {
              $q2->from('hrm_revisi_lokasi')
              ->select('id')->where("lokasi",'like',$like_lists['warehouse_source_name']);          
            });
          }
    
          if (isset($like_lists["warehouse_target_name"])) {
            $q->orWhereIn("hrm_revisi_lokasi_target_id", function($q2)use($like_lists) {
              $q2->from('hrm_revisi_lokasi')
              ->select('id')->where("lokasi",'like',$like_lists['warehouse_target_name']);          
            });
          }
    
    
          if (isset($like_lists["id"])) {
            $q->orWhere("id", "like", $like_lists["id"]);
          }
    
    
          if (isset($like_lists["item_name"])) {
            $q->orWhereIn("st_item_id", function($q2)use($like_lists) {
              $q2->from('st_items')
              ->select('id')->where("name",'like',$like_lists['item_name']);          
            });
          }
    
          if (isset($like_lists["status"])) {
            $q->orWhere("status", "like", $like_lists["status"]);
          }
    
          if (isset($like_lists["type"])) {
            $q->orWhere("type", "like", $like_lists["type"]);
          }
    
          if (isset($like_lists["requested_name"])) {
            $q->orWhereIn("requested_by", function($q2)use($like_lists) {
              $q2->from('is_users')
              ->select('id_user')->where("username",'like',$like_lists['requested_name']);          
            });
          }
    
          if (isset($like_lists["confirmed_name"])) {
            $q->orWhereIn("confirmed_by", function($q2)use($like_lists) {
              $q2->from('is_users')
              ->select('id_user')->where("username",'like',$like_lists['confirmed_name']);          
            });
          }
        });        
      }

      

      // if (isset($like_lists["fullname"])) {
      //   $model_query = $model_query->orWhere("fullname", "like", $like_lists["fullname"]);
      // }

      // if (isset($like_lists["role"])) {
      //   $model_query = $model_query->orWhere("role", "like", $like_lists["role"]);
      // }
    }

    // ==============
    // Model Filter
    // ==============


    // if (isset($request->id)) {
    //   $model_query = $model_query->where("id", 'like', '%' . $request->id . '%');
    // }
    // if (isset($request->name)) {
    //   $model_query = $model_query->where("name", 'like', '%' . $request->name . '%');
    // }
    // if (isset($request->fullname)) {
    //   $model_query = $model_query->where("fullname", 'like', '%' . $request->fullname . '%');
    // }
    // if (isset($request->role)) {
    //   $model_query = $model_query->where("role", 'like', '%' . $request->role . '%');
    // }

    if($this->role=='ClientPabrik')
    $warehouse_ids = $this->admin->the_user->hrm_revisi_lokasis();
    else
    $warehouse_ids = \App\Models\HrmRevisiLokasi::get()->pluck("id")->toArray();


    $model_query = $model_query->whereIn("hrm_revisi_lokasi_target_id",$warehouse_ids)->whereNull('confirmed_by');
    $model_query = $model_query->orderBy("ref_id","desc");
    $model_query = $model_query->with(['warehouse','warehouse_source','warehouse_target','requester', 'confirmer',
    'item'=>function($q){
      $q->with('unit');      
    }])->get();


    $model_query_notify = Transaction::whereNull("confirmed_by")->get();

    return response()->json([
      // "data"=>EmployeeResource::collection($employees->keyBy->id),
      "data" => TransactionResource::collection($model_query),
      "request_notif"=> count($model_query_notify)
    ], 200);
  }



  public function request_transaction_confirm(Request $request)
  {
    MyAdmin::checkRole($this->role, ['User','ClientPabrik']);

    DB::beginTransaction();
    try {

      $model_query                         = Transaction::find($request->id);
      if(!$model_query)
      throw new \Exception("Data tidak ditemukan", 1);

      if($model_query->qty_reminder!=null)
      throw new \Exception("Transaksi tidak bisa dilanjutkan karna sisa sudah terisi", 1);

      $warehouse_id = $model_query->hrm_revisi_lokasi_id;
      if($this->role=='ClientPabrik')
      $warehouse_id = MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$model_query->hrm_revisi_lokasi_id);      
      $model_query->status                    = "done";
      $model_query->confirmed_at              = date("Y-m-d H:i:s");
      $model_query->confirmed_by              = $this->admin_id;
      $model_query->updated_at                = date("Y-m-d H:i:s");


      $qty_reminder = 0;
      $dt_before = Transaction::where('hrm_revisi_lokasi_id',$warehouse_id)
      ->where('st_item_id',$model_query->st_item_id)
      ->whereNotNull('confirmed_by')
      ->orderBy("updated_at","desc")
      ->orderBy("ref_id","desc")
      ->lockForUpdate()->first();
      
      if($dt_before)
      $qty_reminder = $dt_before->qty_reminder;
      
      $qty_reminder += $model_query->qty_in;
      $model_query->qty_reminder                = $qty_reminder;

      $model_query->save();
      DB::commit();
      return response()->json([
        "message" => "Proses ubah data berhasil",
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();
      // return response()->json([
      //   "message" => $e->getMessage(),
      // ], 400);
      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }

      return response()->json([
        "message" => "Proses ubah data gagal",
        // "message" => $e->getMessage(),

      ], 400);
    }
  }
}
