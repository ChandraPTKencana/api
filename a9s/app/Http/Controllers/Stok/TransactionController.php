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
use App\Models\HrmRevisiLokasi;
use App\Models\Stok\Item;
use App\Models\Stok\TransactionDetail;
use Exception;
use Illuminate\Support\Facades\DB;
use Image;
use File;
use App\Http\Resources\MySql\IsUserResource;
use App\Models\MySql\IsUser;

class TransactionController extends Controller
{
  private $admin;
  private $admin_id;
  private $permissions;
  private $role;


  public function __construct(Request $request)
  {
    $this->admin = MyAdmin::user();
    $this->admin_id = $this->admin->the_user->id_user;
    $this->permissions = $this->admin->the_user->listPermissions();
    $this->role = $this->admin->the_user->hak_akses;
  }

  public function index(Request $request)
  {
    MyAdmin::checkScope($this->permissions, 'transaction.views');

    // \App\Helpers\MyAdmin::checkScope($this->auth, ['ap-user-view']);

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
    $model_query = Transaction::offset($offset)->limit($limit);

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

      $list_to_like = ["id","status","type"];    
    
      if(count($like_lists) > 0){
        $model_query = $model_query->where(function ($q)use($like_lists,$list_to_like){
          foreach ($list_to_like as $key => $v) {
            if (isset($like_lists[$v])) {
              $q->orWhere($v, "like", $like_lists[$v]);
            }
          }

          $list_to_like_lokasi_mst = [
            ["warehouse_name","hrm_revisi_lokasi_id"],
            ["warehouse_source_name","hrm_revisi_lokasi_source_id"],
            ["warehouse_target_name","hrm_revisi_lokasi_target_id"]
          ];
          foreach ($list_to_like_lokasi_mst as $key => $v) {
            if (isset($like_lists[$v[0]])) {
              $q->orWhereIn($v[1], function($q2)use($like_lists,$v) {
                $q2->from('hrm_revisi_lokasi')
                ->select('id')->where('lokasi','like',$like_lists[$v[0]]);          
              });
            }
          }

          // $list_to_like_user = [  
          //   ["val_name","val_user"],
          //   ["val1_name","val1_user"],
          //   ["val2_name","val2_user"],
          //   ["req_deleted_name","req_deleted_user"],
          //   ["deleted_name","deleted_user"],
          // ];
          // foreach ($list_to_like_user as $key => $v) {
          //   if (isset($like_lists[$v[0]])) {
          //     $q->orWhereIn($v[1], function($q2)use($like_lists,$v) {
          //       $q2->from('is_users')
          //       ->select('id')->where("username",'like',$like_lists[$v[0]]);          
          //     });
          //   }
          // }
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
    
    $filter_status = $request->filter_status;
    if(!$request->filter_model || count($fm_sorts)==0){
      // if($filter_status=="all")
      // $model_query = $model_query->orderBy('id', 'desc');
      // else
      $model_query = $model_query->orderBy('id', 'desc');
    }
    
    if($this->role=='ClientPabrik' || $this->role=='KTU'){
      $model_query = $model_query->whereIn("hrm_revisi_lokasi_id",$this->admin->the_user->hrm_revisi_lokasis());
    }
    
    
    if($filter_status=="pending"){
      $model_query = $model_query->whereNull("confirmed_at");
    }

    if($filter_status=="done"){
      $model_query = $model_query->whereNotNull("confirmed_at");
    }

    if(array_search($filter_status,['all','pending','done'])!==false){
      $model_query = $model_query->where(function ($q){
        $q->whereNull("ref_id");
        $q->orWhere(function($q2){
          $q2->whereNotNull("ref_id")->whereNotNull("confirmed_user");
        });
      });
    }

    if($filter_status=="all-req"){
      $model_query = $model_query->whereNotNull("ref_id")->where('type','transfer');
    }
    
    if($filter_status=="pending-req"){
      $model_query = $model_query->whereNotNull("ref_id")->where('type','transfer')->whereNull('confirmed_user');
    }

    if($filter_status=="done-req"){
      $model_query = $model_query->whereNotNull("ref_id")->where('type','transfer')->whereNotNull('confirmed_user');
    }

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

    // $model_query = $model_query->with(['created_by','updated_by'])->get();

    
    $model_query = $model_query->orderBy("ref_id","desc");
    $model_query = $model_query->with(['warehouse','warehouse_source','warehouse_target','requested_by', 'confirmed_by',
    'details'=>function($q){
      $q->with([
        'item'=>function($q){
          $q->with('unit');      
        }
      ]);      
    }])->get();

    return response()->json([
      // "data"=>EmployeeResource::collection($employees->keyBy->id),
      "data" => TransactionResource::collection($model_query),
    ], 200);
  }

  public function show(TransactionRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'transaction.view');

    $model_query = Transaction::with(['warehouse','warehouse_source','warehouse_target','requested_by', 'confirmed_by',
    'details'=>function($q){
      $q->with([
        'item'=>function($q){
          $q->with('unit');      
        }
      ]);  
      $q->orderBy("ordinal","asc");
    }])->find($request->id);

    // if($model_query->requested_by != $this->admin_id){
    //   return response()->json([
    //     "message" => "Hanya yang membuat transaksi yang boleh melakukan pergantian atau konfirmasi data",
    //   ], 400);
    // }
    
    if($this->role=='ClientPabrik' || $this->role=='KTU')
    MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$model_query->hrm_revisi_lokasi_id);

    // if($model_query->ref_id!=null){
    //   return response()->json([
    //     "message" => "Ubah data ditolak",
    //   ], 400);
    // }

    return response()->json([
      "data" => new TransactionResource($model_query),
    ], 200);
  }

  public function validateItems($details_in){
    $rules = [
      'details'                          => 'required|array',
      'details.*.p_status'               => 'required|in:Add,Edit,Remove',
      'details.*.qty_in'                 => 'required_if:qty_out,|nullable|numeric',
      'details.*.qty_out'                => 'required_if:qty_in,|nullable|numeric',
      'details.*.item_id'                => 'required_unless:details.*.p_status,Remove|exists:App\Models\Stok\Item,id',

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
    MyAdmin::checkScope($this->permissions, 'transaction.create');

    $details_in = json_decode($request->details, true);
    $this->validateItems($details_in);

    if(count($details_in)>0){
      MyAdmin::checkScope($this->permissions, 'transaction.detail.insert');
    }

    $rollback_id = -1;
    DB::beginTransaction();
    $t_stamp = date("Y-m-d H:i:s");

    try {
      //start for details2
      $unique_items = [];
      $unique_used=[];
      foreach ($details_in as $key => $value) {
        $unique_data = strtolower(trim($value['item_id']));
        if (in_array($unique_data, $unique_items) == 1) {
          throw new \Exception("Maaf terdapat Item yang sama",1);
        }
        array_push($unique_items, $unique_data);
        if($value["p_status"]!="Remove")
        array_push($unique_used,$value['item_id']);
      }
      $unique_used = array_unique($unique_used);

      $model_query                         = new Transaction();
      
      $warehouse_id = $request->warehouse_id; // lokasi yang di kelola
      if($this->role=='ClientPabrik' || $this->role=='KTU')
      MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$request->warehouse_id);
      
      $model_query->hrm_revisi_lokasi_id        = $warehouse_id;
      $model_query->note                        = MyLib::emptyStrToNull($request->note);

      $type = $request->type;
      $model_query->type                        = $type;

      $model_query->status                      = "pending";
     
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
      
      $model_query->requested_at              = $t_stamp;
      $model_query->requested_user            = $this->admin_id;

      $model_query->save();

      $ordinal=0;

      foreach ($details_in as $key => $value) {
        $ordinal = $key + 1;
        $detail                     = new TransactionDetail();
        $detail->st_transaction_id = $model_query->id;
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

      MyLog::sys("transaction",$model_query->id,"insert");

      DB::commit();
      return response()->json([
        "message" => "Proses tambah data berhasil",
        "id"=>$model_query->id,
        "status"=>$model_query->status,
        // "created_at" => $t_stamp,
        // "created_user"=>$model_query->created_user,
        // "created_by"=>$model_query->created_user ? new IsUserResource(IsUser::where("id_user",$model_query->created_user)->first()) : null,
        "updated_at" => $t_stamp,
        // "updated_user"=>$model_query->updated_user,
        // "updated_by"=>$model_query->updated_user ? new IsUserResource(IsUser::where("id_user",$model_query->updated_user)->first()) : null,
        "requested_at" => $t_stamp,
        "requested_user"=>$model_query->requested_user,
        "requested_by"=>$model_query->requested_user ? new IsUserResource(IsUser::where("id_user",$model_query->requested_user)->first()) : null,

        "confirmed_at" => $t_stamp,
        "confirmed_user"=>$model_query->confirmed_user,
        "confirmed_by"=>$model_query->confirmed_user ? new IsUserResource(IsUser::where("id_user",$model_query->confirmed_user)->first()) : null,

        "warehouse"=>$model_query->hrm_revisi_lokasi_id ? new \App\Http\Resources\HrmRevisiLokasiResource(\App\Models\HrmRevisiLokasi::where("id",$model_query->hrm_revisi_lokasi_id)->first()) : null,
        "warehouse_source"=>$model_query->hrm_revisi_lokasi_source_id ? new \App\Http\Resources\HrmRevisiLokasiResource(\App\Models\HrmRevisiLokasi::where("id",$model_query->hrm_revisi_lokasi_source_id)->first()) : null,
        "warehouse_target"=>$model_query->hrm_revisi_lokasi_target_id ? new \App\Http\Resources\HrmRevisiLokasiResource(\App\Models\HrmRevisiLokasi::where("id",$model_query->hrm_revisi_lokasi_target_id)->first()) : null,

      ], 200);
    } catch (\Exception $e) {
      DB::rollback();
      if($rollback_id>-1)
      DB::statement("ALTER TABLE st_transaction AUTO_INCREMENT = $rollback_id");


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

  public function update(TransactionRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'transaction.modify');

    $details_in = json_decode($request->details, true);
    $this->validateItems($details_in);

    DB::beginTransaction();
    $t_stamp = date("Y-m-d H:i:s");

    try {
      //start for details2
      $unique_items = [];
      $unique_used=[];
      foreach ($details_in as $key => $value) {
        $unique_data = strtolower(trim($value['item_id']));
        if (in_array($unique_data, $unique_items) == 1) {
          throw new \Exception("Maaf terdapat Item yang sama",1);
        }
        array_push($unique_items, $unique_data);
        if($value["p_status"]!="Remove")
        array_push($unique_used,$value['item_id']);
      }
      $unique_used = array_unique($unique_used);

      $SYSNOTES=[];
      $model_query             = Transaction::where("id",$request->id)->lockForUpdate()->first();
      $SYSOLD      = clone($model_query);
      array_push( $SYSNOTES ,"Details: \n");

      if($model_query->requested_user != $this->admin_id){
        throw new \Exception("Hanya yang membuat transaksi yang boleh melakukan pergantian data",1);
      }

      if($model_query->ref_id!=null){
        throw new \Exception("Ubah data ditolak. Data berasal dari transfer",1);
      }

      if($model_query->confirmed_user != null){
        throw new \Exception("Ubah ditolak. Data sudah di konfirmasi.",1);
      }

      $warehouse_id = $request->warehouse_id;
      if($this->role=='ClientPabrik' || $this->role=='KTU')
      $warehouse_id = MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$model_query->hrm_revisi_lokasi_id);
  
      // $dt_before = $this->getLastDataConfirmed($warehouse_id,$request->item_id);
      // if($dt_before && $dt_before->id != $model_query->id){
      //   throw new \Exception("Ubah ditolak. Hanya data terbaru yang bisa diubah.",1);
      // }
      
      $type = $request->type;

      $model_query->hrm_revisi_lokasi_id        = $warehouse_id;
      $model_query->note                        = MyLib::emptyStrToNull($request->note);
      
      $model_query->type                        = $type;
  
      $model_query->status                      = "pending";
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


      // $items_id_in = array_map(function ($x) {
      //   return $x["item_id"];
      // },$details_in);

      // $prev_checks = $this->getLastDataConfirmed($items_id_in,$model_query->hrm_revisi_lokasi_id)->toArray();

      // $items_id = array_map(function ($x) {
      //   return $x["st_item_id"];
      // },$prev_checks);


      $data_from_db = TransactionDetail::where('st_transaction_id', $model_query->id)
      ->orderBy("ordinal", "asc")->lockForUpdate()
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
          throw new Exception('Ada ketidak sesuaian data, harap hubungi staff IT atau refresh browser anda',1);
      }

      $id_items           = [];
      $ordinal            = 0;
      $for_deletes        = [];
      $for_edits          = [];
      $for_adds           = [];
      $data_to_processes  = [];
      foreach ($details_in as $k => $v) {        
        if (in_array($v["p_status"], ["Add", "Edit"])) {
          $unique = strtolower(trim($v['item_id']));
          if (in_array($unique, $id_items) == 1) {
              throw new \Exception("Maaf terdapat Nama Item yang sama",1);
          }
          array_push($id_items, $unique);
        }

        if ($v["p_status"] !== "Remove") {
          $ordinal++;
          $details_in[$k]["ordinal"] = $ordinal;
          if ($v["p_status"] == "Edit")
              array_unshift($for_edits, $details_in[$k]);
          elseif ($v["p_status"] == "Add")
              array_push($for_adds, $details_in[$k]);
        } else
            array_push($for_deletes, $details_in[$k]);
      }

      if(count($for_adds) > 0){
        MyAdmin::checkScope($this->permissions, 'transaction.detail.insert');
      }

      if(count($for_edits) > 0){
        MyAdmin::checkScope($this->permissions, 'transaction.detail.modify');
      }

      if(count($for_deletes) > 0){
        MyAdmin::checkScope($this->permissions, 'transaction.detail.remove');
      }

      $data_to_processes = array_merge($for_deletes, $for_edits, $for_adds);
      
      if(count($for_edits)==0 && count($for_adds)==0){
        throw new \Exception("Data List Harus Diisi",1);
      }

      foreach ($data_to_processes as $k => $v) {
        $index = false;

        if (isset($v["key"])) {
            $index = array_search($v["key"], $am_ordinal_db);
        }

        if(in_array($v["p_status"],["Add","Edit"])){
          // $ordinal++;

          if(($type=="transfer" || $type=="used")){
            $v['qty_in']=null;
            if($v['qty_out']==0) 
              throw new \Exception("Baris #" .$ordinal." Qty Out Tidak Boleh 0",1);
          }

          if($type=="in"){
            $v['qty_out']=null;
            if($v['qty_in']==0)
            throw new \Exception("Baris #" .$ordinal.".Qty In Tidak Boleh 0",1);
          }


          // $indexItem = array_search($v['item_id'], $items_id);
          // $qty_reminder = 0;

          // if ($indexItem !== false){
          //   $qty_reminder = $prev_checks[$indexItem]["qty_reminder"];
          // }
  
          // if(($type=="used" || $type=="transfer") && $qty_reminder - $v['qty_out'] < 0){
          //   // MyLog::logging($prev_checks);

          //   // throw new \Exception("Baris #" .$ordinal.".Qty melebihi stok : ".$qty_reminder, 1);
          // }
        }


        if ($v["p_status"] == "Remove") {

            if ($index === false) {
                throw new \Exception("Data yang ingin dihapus tidak ditemukan",1);
            } else {
                $dt = $data_from_db[$index];
                array_push( $SYSNOTES ,"Ordinal ".$dt["ordinal"]." [Deleted]");
                TransactionDetail::where("st_transaction_id",$model_query->id)->where("ordinal",$dt["ordinal"])->delete();
            }
        } else if ($v["p_status"] == "Edit") {

            if ($index === false) {
                throw new \Exception("Data yang ingin diubah tidak ditemukan" . $k,1);
            } else {
             
              // $model_query->amount  += $v["amount"];

              // $index_item = array_search($v['ac_account_code'], $listToCheck);
              // $ac_account_id        = null;
              // $ac_account_name      = null;
              // $ac_account_code      = null;
              // if ($index_item !== false){
              //   $ac_account_id      = $temp_ac_accounts[$index_item]['AccountID'];
              //   $ac_account_name    = $temp_ac_accounts[$index_item]['AccountName'];
              //   $ac_account_code    = $temp_ac_accounts[$index_item]['AccountCode'];
              // }

              
              $mq=TransactionDetail::where("st_transaction_id", $model_query->id)
              ->where("ordinal", $v["key"])->where("p_change",false)->lockForUpdate()->first();

              $mqToCom = clone($mq);

              $mq->ordinal          = $v["ordinal"];
              $mq->st_item_id       = $v["item_id"];
              $mq->qty_in           = $v["qty_in"];
              $mq->qty_out          = $v["qty_out"];
              $mq->note             = $v["note"];
              $mq->p_change         = true;
              // $mq->updated_at       = $t_stamp;
              // $mq->updated_user     = $this->admin_id;
              $mq->save();
              
              $SYSNOTE = MyLib::compareChange($mqToCom,$mq); 
              array_push( $SYSNOTES ,"Ordinal ".$v["key"]."\n".$SYSNOTE);

            }
        } else if ($v["p_status"] == "Add") {
            // $model_query->amount += $v["amount"];

            // $index_item = array_search($v['ac_account_code'], $listToCheck);
            // $ac_account_id   = null;
            // $ac_account_name = null;
            // $ac_account_code = null;
            // if ($index_item !== false){
            //   $ac_account_id    = $temp_ac_accounts[$index_item]['AccountID'];
            //   $ac_account_name  = $temp_ac_accounts[$index_item]['AccountName'];
            //   $ac_account_code  = $temp_ac_accounts[$index_item]['AccountCode'];
            // }

            array_push( $SYSNOTES ,"Ordinal ".$v["ordinal"]." [Insert]");

            TransactionDetail::insert([
                'st_transaction_id'   => $model_query->id,
                'ordinal'             => $v["ordinal"],
                "st_item_id"          => $v["item_id"],
                "qty_in"              => $v["qty_in"],
                "qty_out"             => $v["qty_out"],
                'note'                => $v['note'],
                "p_change"            => true,
                // 'created_at'      => $t_stamp,
                // 'created_user'    => $this->admin_id,
                // 'updated_at'      => $t_stamp,
                // 'updated_user'    => $this->admin_id,
            ]);
        }
      }

      $model_query->save();
      TransactionDetail::where('st_transaction_id',$model_query->id)->update(["p_change"=>false]);
      
      $SYSNOTE = MyLib::compareChange($SYSOLD,$model_query); 
      array_unshift( $SYSNOTES , $SYSNOTE );            
      MyLog::sys("transaction",$request->id,"update",implode("\n",$SYSNOTES));

      DB::commit();
      return response()->json([
        "message" => "Proses ubah data berhasil",
        "warehouse"=>$model_query->hrm_revisi_lokasi_id ? new \App\Http\Resources\HrmRevisiLokasiResource(\App\Models\HrmRevisiLokasi::where("id",$model_query->hrm_revisi_lokasi_id)->first()) : null,
        "warehouse_source"=>$model_query->hrm_revisi_lokasi_source_id ? new \App\Http\Resources\HrmRevisiLokasiResource(\App\Models\HrmRevisiLokasi::where("id",$model_query->hrm_revisi_lokasi_source_id)->first()) : null,
        "warehouse_target"=>$model_query->hrm_revisi_lokasi_target_id ? new \App\Http\Resources\HrmRevisiLokasiResource(\App\Models\HrmRevisiLokasi::where("id",$model_query->hrm_revisi_lokasi_target_id)->first()) : null,

      ], 200);
    } catch (\Exception $e) {
      DB::rollback();
      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }
      // return response()->json([
      //   "getCode" => $e->getCode(),
      //   "line" => $e->getLine(),
      //   "message" => $e->getMessage(),
      // ], 400);
      return response()->json([
        "message" => "Proses ubah data gagal"
      ], 400);
    }
  }

  public function delete(TransactionRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'transaction.remove');

    DB::beginTransaction();

    try {
      $model_query = Transaction::where("id",$request->id)->lockForUpdate()->first();
      if($model_query->requested_user != $this->admin_id){
        throw new \Exception("Hanya yang membuat transaksi yang boleh melakukan penghapusan data",1);
      }
      
      $model_querys = TransactionDetail::where("st_transaction_id",$model_query->id)->lockForUpdate()->get();

      if (!$model_query) {
        throw new \Exception("Data tidak terdaftar", 1);
      }

      if($model_query->ref_id != null){
        throw new \Exception("Hapus data ditolak. Data berasal dari transfer",1);
      }

      if($model_query->confirmed_user != null){
        throw new \Exception("Hapus data ditolak. Data sudah dikonfirmasi",1);
      }
      
      if($this->role=='ClientPabrik' || $this->role=='KTU')
      MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$model_query->hrm_revisi_lokasi_id);
  

      TransactionDetail::where("st_transaction_id",$model_query->id)->delete();
      $model_query->delete();

      MyLog::sys("transaction",$request->id,"delete");

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


  // public function request_transactions(Request $request)
  // {
  //   MyAdmin::checkRole($this->role, ['Super Admin','User','ClientPabrik','KTU']);

  //   //======================================================================================================
  //   // Pembatasan Data hanya memerlukan limit dan offset
  //   //======================================================================================================

  //   $limit = 30; // Limit +> Much Data
  //   if (isset($request->limit)) {
  //     if ($request->limit <= 250) {
  //       $limit = $request->limit;
  //     } else {
  //       throw new MyException(["message" => "Max Limit 250"]);
  //     }
  //   }

  //   $offset = isset($request->offset) ? (int) $request->offset : 0; // example offset 400 start from 401

  //   //======================================================================================================
  //   // Jika Halaman Ditentutkan maka $offset akan disesuaikan
  //   //======================================================================================================
  //   if (isset($request->page)) {
  //     $page =  (int) $request->page;
  //     $offset = ($page * $limit) - $limit;
  //   }


  //   //======================================================================================================
  //   // Init Model
  //   //======================================================================================================
  //   $model_query = Transaction::offset($offset)->limit($limit);

  //   $first_row=[];
  //   if($request->first_row){
  //     $first_row 	= json_decode($request->first_row, true);
  //   }

  //   //======================================================================================================
  //   // Model Sorting | Example $request->sort = "username:desc,role:desc";
  //   //======================================================================================================

  //   if ($request->sort) {
  //     $sort_lists = [];

  //     $sorts = explode(",", $request->sort);
  //     foreach ($sorts as $key => $sort) {
  //       $side = explode(":", $sort);
  //       $side[1] = isset($side[1]) ? $side[1] : 'ASC';
  //       $sort_symbol = $side[1] == "desc" ? "<=" : ">=";
  //       $sort_lists[$side[0]] = $side[1];
  //     }

  //     if (isset($sort_lists["name"])) {
  //       $model_query = $model_query->orderBy("name", $sort_lists["name"]);
  //       if (count($first_row) > 0) {
  //         $model_query = $model_query->where("name",$sort_symbol,$first_row["name"]);
  //       }
  //     }

  //     if (isset($sort_lists["id"])) {
  //       $model_query = $model_query->orderBy("id", $sort_lists["id"]);
  //       if (count($first_row) > 0) {
  //         $model_query = $model_query->where("id",$sort_symbol,$first_row["id"]);
  //       }
  //     }

  //     // if (isset($sort_lists["input_at"])) {
  //     //   $model_query = $model_query->orderBy("input_at", $sort_lists["input_at"]);
  //     //   if (count($first_row) > 0) {
  //     //     $model_query = $model_query->where("input_at",$sort_symbol,MyLib::utcDateToIdnDate($first_row["input_at"]));
  //     //   }
  //     // }

  //     // if (isset($sort_lists["fullname"])) {
  //     //   $model_query = $model_query->orderBy("fullname", $sort_lists["fullname"]);
  //     // }

  //     // if (isset($sort_lists["role"])) {
  //     //   $model_query = $model_query->orderBy("role", $sort_lists["role"]);
  //     // }
  //   } else {
  //     $model_query = $model_query->orderBy('id', 'DESC');
  //   }
  //   //======================================================================================================
  //   // Model Filter | Example $request->like = "username:%username,role:%role%,name:role%,";
  //   //======================================================================================================

  //   if ($request->like) {
  //     $like_lists = [];

  //     $likes = explode(",", $request->like);
  //     foreach ($likes as $key => $like) {
  //       $side = explode(":", $like);
  //       $side[1] = isset($side[1]) ? $side[1] : '';
  //       $like_lists[$side[0]] = $side[1];
  //     }

  //     if(count($like_lists) > 0){
  //       $model_query = $model_query->where(function ($q)use($like_lists){
          
  //         if (isset($like_lists["warehouse_name"])) {
  //           $q->orWhereIn("hrm_revisi_lokasi_id", function($q2)use($like_lists) {
  //             $q2->from('hrm_revisi_lokasi')
  //             ->select('id')->where("lokasi",'like',$like_lists['warehouse_name']);          
  //           });
  //         }
    
  //         if (isset($like_lists["warehouse_source_name"])) {
  //           $q->orWhereIn("hrm_revisi_lokasi_source_id", function($q2)use($like_lists) {
  //             $q2->from('hrm_revisi_lokasi')
  //             ->select('id')->where("lokasi",'like',$like_lists['warehouse_source_name']);          
  //           });
  //         }
    
  //         if (isset($like_lists["warehouse_target_name"])) {
  //           $q->orWhereIn("hrm_revisi_lokasi_target_id", function($q2)use($like_lists) {
  //             $q2->from('hrm_revisi_lokasi')
  //             ->select('id')->where("lokasi",'like',$like_lists['warehouse_target_name']);          
  //           });
  //         }
    
    
  //         if (isset($like_lists["id"])) {
  //           $q->orWhere("id", "like", $like_lists["id"]);
  //         }
    
    
  //         // if (isset($like_lists["item_name"])) {
  //         //   $q->orWhereIn("st_item_id", function($q2)use($like_lists) {
  //         //     $q2->from('st_items')
  //         //     ->select('id')->where("name",'like',$like_lists['item_name']);          
  //         //   });
  //         // }
    
  //         if (isset($like_lists["status"])) {
  //           $q->orWhere("status", "like", $like_lists["status"]);
  //         }
    
  //         if (isset($like_lists["type"])) {
  //           $q->orWhere("type", "like", $like_lists["type"]);
  //         }
    
  //         if (isset($like_lists["requested_name"])) {
  //           $q->orWhereIn("requested_user", function($q2)use($like_lists) {
  //             $q2->from('is_users')
  //             ->select('id_user')->where("username",'like',$like_lists['requested_name']);          
  //           });
  //         }
    
  //         if (isset($like_lists["confirmed_name"])) {
  //           $q->orWhereIn("confirmed_user", function($q2)use($like_lists) {
  //             $q2->from('is_users')
  //             ->select('id_user')->where("username",'like',$like_lists['confirmed_name']);          
  //           });
  //         }
  //       });        
  //     }

      

  //     // if (isset($like_lists["fullname"])) {
  //     //   $model_query = $model_query->orWhere("fullname", "like", $like_lists["fullname"]);
  //     // }

  //     // if (isset($like_lists["role"])) {
  //     //   $model_query = $model_query->orWhere("role", "like", $like_lists["role"]);
  //     // }
  //   }

  //   // ==============
  //   // Model Filter
  //   // ==============


  //   // if (isset($request->id)) {
  //   //   $model_query = $model_query->where("id", 'like', '%' . $request->id . '%');
  //   // }
  //   // if (isset($request->name)) {
  //   //   $model_query = $model_query->where("name", 'like', '%' . $request->name . '%');
  //   // }
  //   // if (isset($request->fullname)) {
  //   //   $model_query = $model_query->where("fullname", 'like', '%' . $request->fullname . '%');
  //   // }
  //   // if (isset($request->role)) {
  //   //   $model_query = $model_query->where("role", 'like', '%' . $request->role . '%');
  //   // }

  //   if($this->role=='ClientPabrik' || $this->role=='KTU')
  //   $warehouse_ids = $this->admin->the_user->hrm_revisi_lokasis();
  //   else
  //   $warehouse_ids = \App\Models\HrmRevisiLokasi::get()->pluck("id")->toArray();


  //   $model_query = $model_query->whereIn("hrm_revisi_lokasi_target_id",$warehouse_ids)->whereNull('confirmed_user')->whereNotNull("ref_id");
  //   $model_query = $model_query->orderBy("ref_id","desc");
  //   $model_query = $model_query->with(['warehouse','warehouse_source','warehouse_target','requester', 'confirmer',
  //   'details'=>function($q){
  //     $q->with([
  //       'item'=>function($q){
  //         $q->with('unit');      
  //       }
  //     ]);      
  //   }])->get();


  //   $model_query_notify = Transaction::whereNull("confirmed_user")->get();

  //   return response()->json([
  //     // "data"=>EmployeeResource::collection($employees->keyBy->id),
  //     "data" => TransactionResource::collection($model_query),
  //     "request_notif"=> count($model_query_notify)
  //   ], 200);
  // }

  public function summary_transactions(Request $request)
  {
    MyAdmin::checkRole($this->role, ['Super Admin','User','ClientPabrik','KTU']);

    $warehouses = new HrmRevisiLokasi();

    if($this->role=='ClientPabrik' || $this->role=='KTU'){
      $warehouse_ids = $this->admin->the_user->hrm_revisi_lokasis();
      $warehouses = $warehouses->whereIn("id",$warehouse_ids);
    }
    $warehouses = $warehouses->where("lokasi","not like","Ramp%");


    $date_to = $request->to;
    if(!$date_to){
      return response()->json([
        "message" => "Tanggal tidak boleh kosong",
      ], 400);
    }
    // $date_to = str_replace('"', '', $date_to);
    $date_to = MyLib::utcDateToIdnDate(trim($date_to, '"'));
    $date_to = new \DateTime($date_to);
    $date_to->add(new \DateInterval('P1D'));
    // // $date = $date->format('Y-m-d H:i:s');
    $date_to = $date_to->format('Y-m-d')."T00:00:00.000Z";
    $dates = explode("T",$date_to);

    $warehouses = $warehouses->get(); 
    $items = Item::orderBy('name','asc')->get();
    $items_id = $items->pluck("id");
    // $subquery = TransactionDetail::selectRaw("distinct st_item_id,st_transactions.hrm_revisi_lokasi_id , max(st_transactions.input_at) as max_input_at")
    // ->whereIn("st_item_id",$items_id)
    // ->join("st_transactions",function ($q){
    //   $q->on('st_transactions.id',"=","st_transaction_details.st_transaction_id");
    // })
    // ->whereNotNull("confirmed_by")
    // ->where("st_transactions.input_at","<=",$dates[0])
    // // ->orderBy("st_transactions.input_at","desc")
    // // ->orderBy("ref_id","desc")
    // ->groupBy(["st_item_id","hrm_revisi_lokasi_id"]);

    // $model_query =TransactionDetail::selectRaw("st_transaction_id,st_transaction_details.st_item_id, qty_reminder, st_transactions.hrm_revisi_lokasi_id,st_transactions.updated_at,st_transactions.input_at")
    // ->joinSub($subquery,'dtfb',function ($join){
    //   $join->on('st_transaction_details.st_item_id', '=', 'dtfb.st_item_id');
    // })
    // ->join("st_transactions",function ($join) {
    //   $join->on("st_transactions.input_at","dtfb.max_input_at");
    //   $join->on("st_transactions.id","st_transaction_details.st_transaction_id");
    //   $join->orderBy("st_transactions.input_ordinal","desc");
    // })
    // ->whereNotNull("st_transaction_details.qty_reminder")
    // ->lockForUpdate()
    // ->get();

    $model_query =TransactionDetail::selectRaw("st_transaction_details.st_item_id, sum( coalesce(qty_in, 0) - coalesce(qty_out, 0)) as qty_reminder, st_transactions.hrm_revisi_lokasi_id")
    ->from('st_transactions')
    ->join('st_transaction_details',function ($join)use($items_id){
      $join->on('st_transactions.id', '=', 'st_transaction_details.st_transaction_id');
      $join->whereIn("st_transaction_details.st_item_id",$items_id);
    })
    ->whereNotNull("st_transaction_details.qty_reminder")
    ->whereNotNull("confirmed_user")
    ->where("st_transactions.requested_at","<=",$dates[0])
    ->groupBy(["hrm_revisi_lokasi_id","st_item_id"])
    ->lockForUpdate()
    ->get();

    $temp_items =[];
    foreach ($items as $k => $v) {
      array_push($temp_items,[
        "id"=>$v->id,
        "name"=>$v->name,
        "qty_reminder"=>0,
        "unit_name"=>$v->unit->name
      ]);      
    }

    $temp_data =[];
    foreach ($warehouses as $k => $v) {
      array_push($temp_data,[
        "id"=>$v->id,
        "lokasi"=>$v->lokasi,
        "items"=>$temp_items
      ]);
    }

    $temp_data_hrm_revisi_lokasi_ids = array_map(function ($x){
      return $x["id"];
    },$temp_data);

    foreach ($model_query as $k => $v) {
      $index = array_search($v->hrm_revisi_lokasi_id, $temp_data_hrm_revisi_lokasi_ids);
      if($index===false) continue;

      $temp_items_id = array_map(function ($x) {
        return $x["id"];
      },$temp_data[$index]["items"]);
      $index2 = array_search($v->st_item_id, $temp_items_id);
      if($index2===false) continue;
      $temp_data[$index]["items"][$index2]["qty_reminder"] = $v->qty_reminder;
    }

    return response()->json([
      "all" => $temp_data,
      "column_header"=>$warehouses,
      "row_header"=>$items,
    ], 200);

  }

  public function summary_detail_transactions(Request $request) {
    MyAdmin::checkRole($this->role, ['Super Admin','User','ClientPabrik','KTU']);
    
    $rules = [
      'item_id' => 'required|exists:\App\Models\Stok\Item,id',
      'warehouse_id' => 'required|exists:\App\Models\HrmRevisiLokasi,id',
    ];

    $messages = [
      'item_id.required' => 'Item ID tidak boleh kosong',
      'item_id.exists' => 'Item ID tidak terdaftar',

      'warehouse_id.required' => 'Warehouse ID tidak boleh kosong',
      'warehouse_id.exists' => 'Warehouse ID tidak terdaftar',
    ];

    $validator = \Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }
    
    $date_to = $request->to;
    if(!$date_to){
      return response()->json([
        "message" => "Tanggal tidak boleh kosong",
      ], 400);
    }
    // $date_to = str_replace('"', '', $date_to);
    $date_to = MyLib::utcDateToIdnDate(trim($date_to, '"'));
    $date_to = new \DateTime($date_to);
    $date_to->add(new \DateInterval('P1D'));
    // // $date = $date->format('Y-m-d H:i:s');
    $date_to = $date_to->format('Y-m-d')."T00:00:00.000Z";
    $dates = explode("T",$date_to);

    $item_id = $request->item_id;
    $warehouse_id = $request->warehouse_id;

    if($this->role=='ClientPabrik' || $this->role=='KTU')
    MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$warehouse_id);

    $model_query = TransactionDetail::selectRaw("st_transaction_details.*,st_transactions.type,st_transactions.requested_at,st_transactions.updated_at,st_transactions.confirmed_at,hrl_source.lokasi as source,hrl_target.lokasi as target")
    ->where('st_item_id',$item_id)
    ->join("st_transactions",function ($join)use($warehouse_id) {
      $join->on("st_transactions.id","=","st_transaction_details.st_transaction_id");
      $join->where("hrm_revisi_lokasi_id",$warehouse_id);
      $join->whereNotNull("confirmed_user");
    })
    ->leftjoin("hrm_revisi_lokasi as hrl_source",function ($join)use($warehouse_id) {
      $join->on("hrl_source.id","=","st_transactions.hrm_revisi_lokasi_source_id");
    })->leftjoin("hrm_revisi_lokasi as hrl_target",function ($join)use($warehouse_id) {
      $join->on("hrl_target.id","=","st_transactions.hrm_revisi_lokasi_target_id");
    })
    ->orderBy("st_transactions.requested_at","desc")
    ->where("st_transactions.requested_at","<=",$dates[0])
    // ->orderBy("st_transactions.input_ordinal","desc")
    // ->orderBy("ref_id","desc")
    ->get();

    // return response()->json([
    //   "data" => $model_query,
    // ], 400);

    // foreach ($model_query as $k => $v) {
    // }

    return response()->json([
      "data" => $model_query,
    ], 200);
  }
    

  public function getLastDataConfirmed($items_id,$hrm_revisi_lokasi_id,$st_transaction_id=""){
    $subquery = TransactionDetail::selectRaw("distinct st_item_id,max(st_transaction_details.st_transaction_id) as max_trx_id")
    ->whereIn("st_item_id",$items_id)
    ->join("st_transactions",function ($q)use($hrm_revisi_lokasi_id,$st_transaction_id){
      $q->on('st_transactions.id',"=","st_transaction_details.st_transaction_id");
      $q->where("hrm_revisi_lokasi_id",$hrm_revisi_lokasi_id);

      if($st_transaction_id!="")
      $q->where("st_transactions.id","<",$st_transaction_id);
    })
    ->whereNotNull("confirmed_user")
    ->groupBy("st_item_id");

    return TransactionDetail::select("*")
    ->joinSub($subquery,'dtfb',function ($join){
      $join->on('st_transaction_details.st_item_id', '=', 'dtfb.st_item_id');
    })
    ->join("st_transactions",function ($join) use ($hrm_revisi_lokasi_id) {
      $join->on("st_transactions.id",DB::raw("`dtfb`.`max_trx_id`"));
      $join->on("st_transactions.id","st_transaction_details.st_transaction_id");
      // $join->orderBy("st_transactions.input_ordinal","desc");
      $join->where("hrm_revisi_lokasi_id",$hrm_revisi_lokasi_id);
    })
    ->whereNotNull("st_transaction_details.qty_reminder")
    ->lockForUpdate()
    ->get();

    // $subquery = TransactionDetail::selectRaw("distinct st_item_id,max(concat(st_transactions.input_at,' ',st_transactions.confirmed_at)) as max_input_confirm_at")
    // ->whereIn("st_item_id",$items_id)
    // ->join("st_transactions",function ($q)use($hrm_revisi_lokasi_id,$date_limit){
    //   $q->on('st_transactions.id',"=","st_transaction_details.st_transaction_id");
    //   $q->where("hrm_revisi_lokasi_id",$hrm_revisi_lokasi_id);

    //   if($date_limit!="")
    //   $q->where("input_at","<=",$date_limit);
    // })
    // ->whereNotNull("confirmed_by")
    // ->groupBy("st_item_id");

    // $allQ = TransactionDetail::select("*")
    // ->joinSub($subquery,'dtfb',function ($join){
    //   $join->on('st_transaction_details.st_item_id', '=', 'dtfb.st_item_id');
    // })
    // ->join("st_transactions",function ($join) use ($hrm_revisi_lokasi_id) {
    //   $join->on("st_transactions.input_at",DB::raw("substring(`dtfb`.`max_input_confirm_at`from 1 for 10)"));
    //   $join->on("st_transactions.confirmed_at",DB::raw("substring(`dtfb`.`max_input_confirm_at`from 12 for 19)"));
    //   $join->on("st_transactions.id","st_transaction_details.st_transaction_id");
    //   $join->orderBy("st_transactions.input_ordinal","desc");
    //   $join->where("hrm_revisi_lokasi_id",$hrm_revisi_lokasi_id);
    // })
    // ->whereNotNull("st_transaction_details.qty_reminder")
    // ->lockForUpdate()
    // ->toSql();

    // MyLog::logging($allQ);
    // return Transaction::where('hrm_revisi_lokasi_id',$warehouse_id)
    // ->where('st_item_id',$item_id)
    // ->whereNotNull('confirmed_by')
    // ->orderBy("input_at","desc")
    // ->orderBy("ref_id","desc")
    // ->lockForUpdate()->first(); 
    
    
  }

  public function confirm_transaction(Request $request) {
    MyAdmin::checkMultiScope($this->permissions, ['transaction.val','transaction.val1']);
    
    $rules = [
      'id' => 'required|exists:\App\Models\Stok\Transaction,id',
    ];

    $messages = [
      'id.required' => 'ID tidak boleh kosong',
      'id.exists' => 'ID tidak terdaftar',
    ];

    $validator = \Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    // $date_to = $request->to;
    // if(!$date_to){
    //   return response()->json([
    //     "message" => "Tanggal tidak boleh kosong",
    //   ], 400);
    // }
    // // $date_to = str_replace('"', '', $date_to);
    // $date_to = MyLib::utcDateToIdnDate(trim($date_to, '"'));
    // $dates = explode("T",$date_to);
    
    // $date_to = new \DateTime($date_to);
    // $date_to->add(new \DateInterval('P1D'));
    // $date = $date->format('Y-m-d H:i:s');
    // $date_to = $date_to->format('Y-m-d')."T00:00:00.000Z";
    // $nowTime = date("H:i:s");
    // $nDateTime = $dates[0]." ".$nowTime;

    DB::beginTransaction();
    try {
      $model_query             = Transaction::where("id",$request->id)->lockForUpdate()->first();

      // if($model_query->ref_id == null &&  $model_query->requested_by != $this->admin_id && MyAdmin::checkScope($this->permissions, 'transaction.val',true)){
      // if($model_query->ref_id == null &&  MyAdmin::checkScope($this->permissions, 'transaction.val',true)){
      //   throw new \Exception("Anda ",1);
      // }

      if($model_query->confirmed_user != null){
        throw new \Exception("Konfirmasi ditolak. Data sudah di konfirmasi.",1);
      }

      if($this->role=='ClientPabrik' || $this->role=='KTU')
      MyAdmin::checkReturnOrFailLocation($this->admin->the_user,$model_query->hrm_revisi_lokasi_id);
  
      $type = $model_query->type;

      if($type=="transfer" && $model_query->ref_id==null){
        $model_query2                               = new Transaction();
        $model_query2->ref_id                       = $model_query->id;
        $model_query2->hrm_revisi_lokasi_id         = $model_query->hrm_revisi_lokasi_target_id;
        $model_query2->type                         = $type;
        $model_query2->status                       = "pending";
        $model_query2->hrm_revisi_lokasi_source_id  = $model_query->hrm_revisi_lokasi_source_id;
        $model_query2->hrm_revisi_lokasi_target_id  = $model_query->hrm_revisi_lokasi_target_id;
        $model_query2->requested_at                 = date("Y-m-d H:i:s");
        $model_query2->requested_user                 = $this->admin_id;
        $model_query2->confirmed_at                 = null;
        $model_query2->save();
      }

      $data_from_db = TransactionDetail::where('st_transaction_id', $model_query->id)
      ->orderBy("ordinal", "asc")->lockForUpdate()
      ->get();

      $prev_checks = $this->getLastDataConfirmed($data_from_db->pluck("st_item_id"),$model_query->hrm_revisi_lokasi_id,$model_query->id)->toArray();
      $items_id = array_map(function ($x) {
        return $x["st_item_id"];
      },$prev_checks);
     
      foreach ($data_from_db as $k => $v) {

        $index = array_search($v->st_item_id, $items_id);
        $qty_reminder = 0;
        
        if ($index !== false){
          $qty_reminder = $prev_checks[$index]["qty_reminder"];
        }

        if($model_query->ref_id == null){
          if($type=="in"){
            $qty_reminder += $v->qty_in;
          }elseif($type=="used" || $type=="transfer"){
            // if($qty_reminder - $v->qty_out < 0)
            //   throw new \Exception("Qty melebihi stok : ".$qty_reminder, 1);
            $qty_reminder-=$v->qty_out;
          }
        }else{
          $qty_reminder += $v->qty_in;
        }


        TransactionDetail::where("st_transaction_id",$v->st_transaction_id)
        ->where("st_item_id",$v->st_item_id)->update([
          "qty_reminder"=>$qty_reminder
        ]);
        
        if($type=="transfer" && $model_query->ref_id==null){
          $model_query3 = new TransactionDetail();
          $model_query3->st_transaction_id = $model_query2->id;
          $model_query3->ordinal = $v->ordinal;
          $model_query3->st_item_id = $v->st_item_id;
          $model_query3->qty_in = $v->qty_out;
          $model_query3->note = $v->note;
          $model_query3->save();
        }

        //recalculate qty reminder
        // $this->recalculateQtyReminder($v->st_item_id,$model_query->hrm_revisi_lokasi_id,$model_query->id,$v->qty_in - $v->qty_out);
      }

    
      // return response()->json([
      //   "xmessage" => $toGetMaxOrdinal,
      // ], 400);
      $model_query->confirmed_at               = MyLib::timestampMs();
      $model_query->confirmed_user             = $this->admin_id;
      $model_query->status                     = "done";

      $model_query->save();

      DB::commit();
      return response()->json([
        "message"         => "Proses ubah data berhasil",
        "confirmed_user"  =>  $this->admin_id,
        "confirmed_by"    =>  new IsUserResource($this->admin->the_user),
        "confirmed_at"    =>  $model_query->confirmed_at
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



  public function recalculateQtyReminder($st_items_id,$hrm_revisi_lokasi_id,$st_transaction_id,$qty){
    
    TransactionDetail::select("st_transaction_details.*")
    ->where("st_item_id",$st_items_id)
    ->whereIn("st_transaction_id",function ($q)use($hrm_revisi_lokasi_id,$st_transaction_id){
      $q->select("id")->from("st_transactions")->where("id",">",$st_transaction_id);
      $q->where("hrm_revisi_lokasi_id",$hrm_revisi_lokasi_id);
      $q->whereNotNull("confirmed_at");
    })->update([
      "qty_reminder"=>DB::raw("`qty_reminder`+".($qty))
    ]);
    
  }
}
