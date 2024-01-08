<?php

namespace App\Http\Controllers\Stok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Helpers\MyLib;
use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Models\Stok\Transaction;
use App\Helpers\MyAdmin;
use App\Helpers\MyLog;
use App\Http\Requests\Stok\TransactionRequest;
use App\Http\Resources\Stok\TransactionResource;
use App\Models\Stok\TransactionDetail;
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

      // if (isset($sort_lists["name"])) {
      //   $model_query = $model_query->orderBy("name", $sort_lists["name"]);
      //   if (count($first_row) > 0) {
      //     $model_query = $model_query->where("name",$sort_symbol,$first_row["name"]);
      //   }
      // }

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
    
          // if (isset($like_lists["item_name"])) {
          //   $q->orWhereIn("st_item_id", function($q2)use($like_lists) {
          //     $q2->from('st_items')
          //     ->select('id')->where("name",'like',$like_lists['item_name']);          
          //   });
          // }
    
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
    'details'=>function($q){
      $q->with([
        'item'=>function($q){
          $q->with('unit');      
        }
      ]);      
    }])->get();


    $model_query_notify = Transaction::where("type","transfer")->whereNull("confirmed_by")->get();
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
    MyAdmin::checkRole($this->role, ['User','ClientPabrik']);


    $model_query = Transaction::with(['warehouse','warehouse_source','warehouse_target','requester', 'confirmer',
    'details'=>function($q){
      $q->with([
        'item'=>function($q){
          $q->with('unit');      
        }
      ]);  
      $q->orderBy("ordinal","asc");
    }])->find($request->id);

    if($this->role=='ClientPabrik')
    MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$model_query->hrm_revisi_lokasi_id);

    if($model_query->ref_id!=null){
      return response()->json([
        "message" => "Ubah data ditolak",
      ], 400);
    }

    return response()->json([
      "data" => new TransactionResource($model_query),
    ], 200);
  }

  public function validateItems($details_in){
    $rules = [
      'details'                          => 'required|array',
      'details.*.status'                 => 'required|in:Add,Edit,Remove',
      'details.*.qty_in'                 => 'required_if:qty_out,|nullable|numeric',
      'details.*.qty_out'                => 'required_if:qty_in,|nullable|numeric',
      'details.*.item_id'                => 'required_unless:details.*.status,Remove|exists:App\Models\Stok\Item,id',

      // 'details.*.item'                   => 'required|array',
      // 'details.*.item.code'              => 'required|exists:\App\Model\Item,code',
      // 'details.*.unit'                   => 'required|array',
      // 'details.*.unit.code'              => 'required|exists:\App\Model\Unit,code',
    ];

    $messages = [
      'details.required' => 'Item harus di isi',
      'details.array' => 'Format Pengambilan Barang Salah',

    ];

    // // Replace :index with the actual index value in the custom error messages
    foreach ($details_in as $index => $msg) {
      $messages["details.{$index}.qty_in.required_if"]          = "Baris #" . ($index + 1) . ". Qty In yang diminta tidak boleh kosong.";
      $messages["details.{$index}.qty_in.numeric"]              = "Baris #" . ($index + 1) . ". Qty In yang diminta harus angka";
      $messages["details.{$index}.qty_in.min"]                  = "Baris #" . ($index + 1) . ". Qty In minimal 1";

      $messages["details.{$index}.qty_out.required_if"]          = "Baris #" . ($index + 1) . ". Qty Out yang diminta tidak boleh kosong.";
      $messages["details.{$index}.qty_out.numeric"]              = "Baris #" . ($index + 1) . ". Qty Out yang diminta harus angka";
      $messages["details.{$index}.qty_out.min"]                  = "Baris #" . ($index + 1) . ". Qty Out minimal 1";

      $messages["details.{$index}.item_id.required_unless"]            = "Baris #" . ($index + 1) . ". Item harus di isi";
      $messages["details.{$index}.item_id.exists"]              = "Baris #" . ($index + 1) . ". Item tidak terdaftar";

      $messages["details.{$index}.status.required"]            = "Baris #" . ($index + 1) . ". Status harus di isi";
      $messages["details.{$index}.status.in"]                   = "Baris #" . ($index + 1) . ". Status tidak sesuai format";
      // $messages["details.{$index}.item.required"]                 = "Baris #" . ($index + 1) . ". Item di Form Pengambilan Barang Gudang harus di isi";
      // $messages["details.{$index}.item.array"]                    = "Baris #" . ($index + 1) . ". Format Item di Pengambilan Barang Gudang Salah";
      // $messages["details.{$index}.item.code.required"]            = "Baris #" . ($index + 1) . ". Item harus di isi";
      // $messages["details.{$index}.item.code.exists"]              = "Baris #" . ($index + 1) . ". Item tidak terdaftar";

      // $messages["details.{$index}.unit.required"]                 = 'Baris #' . ($index + 1) . '. Satuan di Pengambilan Barang Gudang harus di isi';
      // $messages["details.{$index}.unit.array"]                    = 'Baris #' . ($index + 1) . '. Format Satuan di Pengambilan Barang Gudang Salah';
      // $messages["details.{$index}.unit.code.required"]            = 'Baris #' . ($index + 1) . '. Satuan harus di isi';
      // $messages["details.{$index}.unit.code.exists"]              = 'Baris #' . ($index + 1) . '. Satuan tidak terdaftar';

    }

    $validator = \Validator::make(['details' => $details_in], $rules, $messages);

    // Check if validation fails
    if ($validator->fails()) {
      foreach ($validator->messages()->all() as $k => $v) {
        throw new MyException(["message" => $v], 400);
      }
    }
  }


  public function store(TransactionRequest $request)
  {
    MyAdmin::checkRole($this->role, ['User','ClientPabrik']);

    $details_in = json_decode($request->details, true);
    $this->validateItems($details_in);
    
    DB::beginTransaction();
    try {
      $model_query                         = new Transaction();
      
      $warehouse_id = $request->warehouse_id; // lokasi yang di kelola
      if($this->role=='ClientPabrik')
      $warehouse_id = MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$request->warehouse_id);
      
      $model_query->hrm_revisi_lokasi_id        = $warehouse_id;
      $model_query->note                        = MyLib::emptyStrToNull($request->note);

      $type = $request->type;
      $model_query->type                        = $type;

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

      $model_query->created_at                = date("Y-m-d H:i:s");
      $model_query->updated_at                = date("Y-m-d H:i:s");


      // $qty_reminder = 0;
      // $dt_before = $this->getLastDataConfirmed($warehouse_id,$request->item_id);
      // if($dt_before)
      // $qty_reminder = $dt_before->qty_reminder;
      // else{
      //   if($type!="in"){
      //     throw new \Exception("Stok Awal Diperlukan", 1);
      //   }
      // }
      // if($type=="in"){
      //   $qty_reminder += $request->qty_in;
      // }else{

      //   if($qty_reminder - $request->qty_out < 0){
      //     throw new \Exception("Qty melebihi stok : ".$qty_reminder, 1);
      //   }

      //   $qty_reminder -= $request->qty_out;
      // }

      
      
      // $model_query->qty_reminder                = $qty_reminder;

      $model_query->save();

      $ordinal=0;
      $id_items = [];
      foreach ($details_in as $key => $value) {

        $ordinal = $key + 1;
        if (in_array(strtolower($value['item_id']), $id_items) == 1) {
          throw new \Exception("Maaf terdapat Item yang sama");
        }
        array_push($id_items, strtolower($value['item_id']));

        $detail                    = new \App\Models\Stok\TransactionDetail();
        $detail->st_transaction_id    = $model_query->id;
        $detail->ordinal           = $ordinal;
        $detail->st_item_id        = $value['item_id'];

        if($type=="transfer" || $type=="used"){
          if($value['qty_out']==0) {
            throw new \Exception("Baris #" .$ordinal." Qty Out Tidak Boleh 0",1);
          }
          $detail->qty_out                     = $value['qty_out'];
        }else{
          if($value['qty_in']==0) {
            throw new \Exception("Baris #" .$ordinal."Qty In Tidak Boleh 0",1);
          }
          $detail->qty_in                      = $value['qty_in'];
        }

        $detail->note              = $value['note'];
        $detail->save();
      }

      // if($type=="transfer"){
      //   $model_query2                         = new Transaction();
      //   $model_query2->ref_id = $model_query->id;
      //   $model_query2->hrm_revisi_lokasi_id        = $request->warehouse_target_id;
      //   $model_query2->st_item_id                  = $request->item_id;
      //   $model_query2->type                        = $type;
      //   $model_query2->qty_in                      = $request->qty_out;
      //   $model_query2->status                      = "pending";
      //   $model_query2->hrm_revisi_lokasi_source_id  = $warehouse_id;
      //   $model_query2->hrm_revisi_lokasi_target_id  = $warehouse_target_id;
      //   $model_query2->requested_at                 = date("Y-m-d H:i:s");
      //   $model_query2->requested_by                 = $this->admin_id;
      //   $model_query2->confirmed_at                  = null;
      //   $model_query2->created_at                   = date("Y-m-d H:i:s");
      //   $model_query2->updated_at                   = date("Y-m-d H:i:s");
      //   $model_query2->save();

      // }

      DB::commit();
      return response()->json([
        "message" => "Proses tambah data berhasil",
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();
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
        // "message" => $e->getMessage(),

      ], 400);
    }
  }

  public function update(TransactionRequest $request)
  {
    MyAdmin::checkRole($this->role, ['User','ClientPabrik']);
    
    $details_in = json_decode($request->details, true);
    $this->validateItems($details_in);

    DB::beginTransaction();
    try {
      $model_query             = Transaction::where("id",$request->id)->lockForUpdate()->first();

      if($model_query->ref_id!=null){
        throw new \Exception("Ubah data ditolak",1);
      }

      if($model_query->confirmed_by != null){
        throw new \Exception("Ubah ditolak. Data pada sudah di konfirmasi.",1);
      }

      $warehouse_id = $request->warehouse_id;
      if($this->role=='ClientPabrik')
      $warehouse_id = MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$model_query->hrm_revisi_lokasi_id);
  
      // $dt_before = $this->getLastDataConfirmed($warehouse_id,$request->item_id);
      // if($dt_before && $dt_before->id != $model_query->id){
      //   throw new \Exception("Ubah ditolak. Hanya data terbaru yang bisa diubah.",1);
      // }

      
      $type = $request->type;
      $old_type = $model_query->type;

      $mdl = "";
      if($old_type=='transfer'){
        $mdl = Transaction::where("ref_id",$model_query->id)->lockForUpdate()->first();
        if($mdl->confirmed_by != null){
          throw new \Exception("Ubah ditolak. Data pada referensi sudah di konfirmasi.",1);
        }
      }

      // if($type!=="transfer" && $old_type=='transfer'){
      //   $mdl->delete();
      // }

      // $qty_reminder_old = $model_query->qty_reminder;
      // if($old_type=="transfer" || $old_type=="used")
      // $qty_reminder_old+=$model_query->qty_out;
      // else
      // $qty_reminder_old-=$model_query->qty_in;

      $model_query->hrm_revisi_lokasi_id        = $warehouse_id;
      $model_query->note                        = MyLib::emptyStrToNull($request->note);
      
      $model_query->type                        = $type;

      // if($type=="transfer" || $type=="used"){
      //   if($request->qty_out==0) {
      //     throw new \Exception("Qty Tidak Boleh 0",1);
      //   }
      //   $model_query->qty_out                     = $request->qty_out;
      // }else{
      //   if($request->qty_in==0) {
      //     throw new \Exception("Qty Tidak Boleh 0",1);
      //   }
      //   $model_query->qty_in                      = $request->qty_in;
      // }
  
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
      // $model_query->confirmed_at               = date("Y-m-d H:i:s");
      // $model_query->confirmed_by               = $this->admin_id;

      $model_query->updated_at                = date("Y-m-d H:i:s");

      
      // if($type=="in"){
      //   $qty_reminder_old += $request->qty_in;
      // }else{

      //   if($qty_reminder_old - $request->qty_out < 0){
      //     throw new \Exception("Qty melebihi stok : ".$qty_reminder_old, 1);
      //   }

      //   $qty_reminder_old -= $request->qty_out;
      // }

      // $model_query->qty_reminder                = $qty_reminder_old;

      $model_query->save();



  
      $auth_id = $this->admin_id;

      $data_from_db = \App\Models\Stok\TransactionDetail::where('st_transaction_id', $model_query->id)
      ->orderBy("ordinal", "asc")
      ->get()->toArray();
      

      $in_keys = array_filter($details_in, function ($x) {
          return isset($x["key"]);
      });

      $in_keys = array_map(function ($x) {
          return $x["key"];
      }, $in_keys);

      $am_ordinal_db = array_map(function ($x) {
          return $x["ordinal"];
      }, $data_from_db);

      if (count(array_diff($in_keys, $am_ordinal_db)) > 0 || count(array_diff($am_ordinal_db, $in_keys)) > 0) {
          throw new Exception('Ada ketidak sesuaian data, harap hubungi staff IT atau refresh browser anda');
      }

      $id_items = [];
      $ordinal = 0;
      $for_deletes = [];
      $for_edits = [];
      $for_adds = [];
      $data_to_processes = [];
      foreach ($details_in as $k => $v) {
        $item_id = $v['item_id'] ? $v['item_id'] : "";
        
        if (in_array($v["status"], ["Add", "Edit"])) {
          if (in_array(strtolower($v['item_id']), $id_items) == 1) {
              throw new \Exception("Maaf terdapat Nama Item yang sama");
          }
          array_push($id_items, strtolower($v['item_id']));
        }

        if ($v["status"] !== "Remove") {
          $ordinal++;
          $details_in[$k]["ordinal"] = $ordinal;
          if ($v["status"] == "Edit")
              array_unshift($for_edits, $details_in[$k]);
          elseif ($v["status"] == "Add")
              array_push($for_adds, $details_in[$k]);
        } else
            array_push($for_deletes, $details_in[$k]);
      }

      $data_to_processes = array_merge($for_deletes, $for_edits, $for_adds);
      // $ordinal = 0;
      // MyLog::logging([
      //   "data_to_processes"=>$data_to_processes,
      //   "data_from_db"=>$data_from_db,
      // ]);

      // return response()->json([
      //   "message" => "test",
      // ], 400);

      foreach ($data_to_processes as $k => $v) {
        $index = false;

        if (isset($v["key"])) {
            $index = array_search($v["key"], $am_ordinal_db);
        }
        
//         if($k==2)
// {        MyLog::logging([
//           "item_name"=>$v["item"]["name"],
//           "key"=>$v["key"],
//           "index"=>$index,
//           "ordinal_arr"=>$am_ordinal_db,
//           "v"=>$v,
//           "w"=>$data_from_db,
//         ]);

//         return response()->json([
//           "message" => "test",
//         ], 400);
// }


        if(in_array($v["status"],["Add","Edit"])){
          // $ordinal++;

          if(($type=="transfer" || $type=="used")){
            $v['qty_in']=null;
            if($v['qty_out']==0) 
              throw new \Exception("Baris #" .$ordinal." Qty Out Tidak Boleh 0",1);
          }

          if($type=="in"){
            $v['qty_out']=null;
            if($v['qty_in']==0)
            throw new \Exception("Baris #" .$ordinal."Qty In Tidak Boleh 0",1);
          }
        }


        // $v["item_code"] = MyLib::emptyStrToNull($v["item_code"]);
        // $v["note"] = MyLib::emptyStrToNull($v["note"]);
        // $v["qty_assumption"] = MyLib::emptyStrToNull($v["qty_assumption"]);
        // $v["qty_realization"] = MyLib::emptyStrToNull($v["qty_realization"]);
        // $v["stock"] = MyLib::emptyStrToNull($v["stock"]);
        // $v["price_assumption"] = MyLib::emptyStrToNull($v["price_assumption"]);
        // $v["price_realization"] = MyLib::emptyStrToNull($v["price_realization"]);

        if ($v["status"] == "Remove") {

            if ($index === false) {
                throw new \Exception("Data yang ingin dihapus tidak ditemukan");
            } else {
                $dt = $data_from_db[$index];
                // $has_permit = count(array_intersect(['ap-project_material_item-remove'], $scopes));
                // if (!$dt["is_locked"] && $dt["created_by"] == $auth_id && $has_permit) {
                //     ProjectMaterial::where("project_no", $model_query->no)->where("ordinal", $dt["ordinal"])->delete();
                // }
                TransactionDetail::where("st_transaction_id",$model_query->id)->where("ordinal",$dt["ordinal"])->delete();
            }
        } else if ($v["status"] == "Edit") {

            if ($index === false) {
                throw new \Exception("Data yang ingin diubah tidak ditemukan" . $k);
            } else {
                $dt = $data_from_db[$index];
                // $has_permit = count(array_intersect(['ap-project_material_item-edit'], $scopes));
                // if (!$has_permit) {
                //     throw new Exception('Ubah Project Material Item Tidak diizinkan');
                // }

                // if ($v["qty_assumption"] != $dt['qty_assumption']) {
                //     $has_value = count(array_intersect(['dp-project_material-manage-qty_assumption'], $scopes));

                //     if ($dt["is_locked"] || !$has_value || $dt["created_by"] != $auth_id)
                //         throw new Exception('Ubah Jumlah Asumsi Tidak diizinkan');
                // }
             

                TransactionDetail::where("st_transaction_id", $model_query->id)
                    ->where("ordinal", $v["key"])->update([
                        "ordinal"=>$v["ordinal"],
                        "st_item_id" => $v["item_id"],
                        "qty_in" => $v["qty_in"],
                        "qty_out" => $v["qty_out"],
                        "note" => $v["note"],
                        // 'updated_at' => date('Y-m-d H:i:s'),
                        // 'updated_by' => $auth_id,
                    ]);
            }

            // $ordinal++;
        } else if ($v["status"] == "Add") {

            // if (!count(array_intersect(['ap-project_material_item-add'], $scopes)))
            //     throw new Exception('Tambah Project Material Item Tidak diizinkan');

            // if (!count(array_intersect(['dp-project_material-manage-item_code'], $scopes))  && $v["item_code"] != "")
            //     throw new Exception('Tidak ada izin mengelola Kode item');

            TransactionDetail::insert([
                'st_transaction_id' => $model_query->id,
                'ordinal'           => $v["ordinal"],
                'st_item_id'        => $v['item_id'],
                'qty_in'            => $v["qty_in"],
                'qty_out'           => $v['qty_out'],
                'note'              => $v['note'],
                // 'created_at'        => date('Y-m-d H:i:s'),
                // 'created_by'        => $auth_id,
                // 'updated_at'        => date('Y-m-d H:i:s'),
                // 'updated_by'        => $auth_id,
            ]);
            // $ordinal++;
        }
    }


      // if($type=="transfer" && $old_type=='transfer'){
      //   $mdl->hrm_revisi_lokasi_id        = $request->warehouse_target_id;
      //   $mdl->st_item_id                  = $request->item_id;
      //   $mdl->type                        = $type;
      //   $mdl->qty_in                      = $request->qty_out;
      //   $mdl->status                      = "pending";
      //   $mdl->hrm_revisi_lokasi_source_id  = $warehouse_id;
      //   $mdl->hrm_revisi_lokasi_target_id  = $warehouse_target_id;
      //   $mdl->requested_at                 = date("Y-m-d H:i:s");
      //   $mdl->requested_by                 = $this->admin_id;
      //   $mdl->confirmed_at                  = null;
      //   $mdl->created_at                   = date("Y-m-d H:i:s");
      //   $mdl->updated_at                   = date("Y-m-d H:i:s");
      //   $mdl->save();
      // }elseif($type=="transfer" && $old_type!='transfer'){
      //   $model_query2                         = new Transaction();
      //   $model_query2->ref_id = $model_query->id;
      //   $model_query2->hrm_revisi_lokasi_id        = $request->warehouse_target_id;
      //   $model_query2->st_item_id                  = $request->item_id;
      //   $model_query2->type                        = $type;
      //   $model_query2->qty_in                      = $request->qty_out;
      //   $model_query2->status                      = "pending";
      //   $model_query2->hrm_revisi_lokasi_source_id  = $warehouse_id;
      //   $model_query2->hrm_revisi_lokasi_target_id  = $warehouse_target_id;
      //   $model_query2->requested_at                 = date("Y-m-d H:i:s");
      //   $model_query2->requested_by                 = $this->admin_id;
      //   $model_query2->confirmed_at                  = null;
      //   $model_query2->created_at                   = date("Y-m-d H:i:s");
      //   $model_query2->updated_at                   = date("Y-m-d H:i:s");
      //   $model_query2->save();
      // }

      DB::commit();
      return response()->json([
        "message" => "Proses ubah data berhasil",
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();
      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }
      return response()->json([
        "getCode" => $e->getCode(),
        "line" => $e->getLine(),
        "message" => $e->getMessage(),
      ], 400);
      return response()->json([
        "message" => "Proses ubah data gagal"
      ], 400);
    }
  }

  public function delete(TransactionRequest $request)
  {
    MyAdmin::checkRole($this->role, ['User','ClientPabrik']);

    DB::beginTransaction();

    try {
      $model_query = Transaction::where("id",$request->id)->lockForUpdate()->first();

      
      if (!$model_query) {
        throw new \Exception("Data tidak terdaftar", 1);
      }

      if($model_query->ref_id!=null){
        throw new \Exception("Ubah data ditolak",1);
      }
      
      if($this->role=='ClientPabrik')
      MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$model_query->hrm_revisi_lokasi_id);
  
      $dt_before = $this->getLastDataConfirmed($model_query->hrm_revisi_lokasi_id,$model_query->st_item_id);
      if($dt_before && $dt_before->id != $model_query->id){
        throw new \Exception("Hapus ditolak. Hanya data terbaru yang bisa dihapus.",1);
      }

      $type = $request->type;
      $old_type = $model_query->type;

      $mdl = "";
      if($old_type=='transfer'){
        $mdl = Transaction::where("ref_id",$model_query->id)->lockForUpdate()->first();
        if($mdl->confirmed_by != null){
          throw new \Exception("Hapus ditolak. Data pada referensi sudah di konfirmasi.",1);
        }

        $mdl->delete();
      }
      $model_query->delete();

      DB::commit();
      return response()->json([
        "message" => "Proses ubah data berhasil",
      ], 200);
    } catch (\Exception  $e) {
      DB::rollback();
      if ($e->getCode() == "23503")
        return response()->json([
          "message" => "Data tidak dapat dihapus, data masih terkait dengan data yang lain nya",
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
      $dt_before = $this->getLastDataConfirmed($warehouse_id,$model_query->st_item_id);
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

  public function summary_transactions(Request $request)
  {
    MyAdmin::checkRole($this->role, ['User','ClientPabrik']);

    $model_query = new Transaction();
    if($this->role=='ClientPabrik'){
      $warehouse_ids = $this->admin->the_user->hrm_revisi_lokasis();
      $model_query = $model_query->whereIn("hrm_revisi_lokasi_id",$warehouse_ids);
    }else {
      $warehouse_ids = \App\Models\HrmRevisiLokasi::get()->pluck("id")->toArray();
    }

    if($request->to){
      $model_query = $model_query->where("updated_at","<=",$request->to);
    }

    $model_query = $model_query->whereNotNull("qty_reminder");
    $model_query = $model_query->selectRaw('DISTINCT hrm_revisi_lokasi_id,st_item_id,max(updated_at)');
    $model_query = $model_query->groupBy(['hrm_revisi_lokasi_id','st_item_id']);
    $model_query = $model_query->get();


//     SELECT t.user_id, t.location_id, t.qty
// FROM tests t
// JOIN (
//     SELECT user_id, location_id, MAX(updated_at) AS max_updated_at
//     FROM tests
//     GROUP BY user_id, location_id
// ) max_dates
// ON t.user_id = max_dates.user_id
//    AND t.location_id = max_dates.location_id
//    AND t.updated_at = max_dates.max_updated_at;

    return response()->json([
      "q" => $model_query,
    ], 400);


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
      $dt_before = $this->getLastDataConfirmed($warehouse_id,$model_query->st_item_id);      
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


  public function getLastDataConfirmed($warehouse_id,$item_id){
    return Transaction::where('hrm_revisi_lokasi_id',$warehouse_id)
    ->where('st_item_id',$item_id)
    ->whereNotNull('confirmed_by')
    ->orderBy("updated_at","desc")
    ->orderBy("ref_id","desc")
    ->lockForUpdate()->first();    
  }
}
