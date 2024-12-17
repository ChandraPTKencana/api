<?php

namespace App\Http\Controllers\Stok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Helpers\MyLib;
use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Models\Stok\Unit;
use App\Helpers\MyAdmin;
use App\Helpers\MyLog;
use App\Http\Requests\Stok\UnitRequest;
use App\Http\Resources\Stok\UnitResource;
use Exception;
use Illuminate\Support\Facades\DB;
use Image;
use File;
use App\Http\Resources\MySql\IsUserResource;
use App\Models\MySql\IsUser;

class UnitController extends Controller
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
    MyAdmin::checkScope($this->permissions, 'unit.views');

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
    $model_query = Unit::offset($offset)->limit($limit);

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
      $model_query = $model_query->orderBy('id', 'asc');
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

    $model_query = $model_query->with(['created_by','updated_by'])->get();

    return response()->json([
      // "data"=>EmployeeResource::collection($employees->keyBy->id),
      "data" => UnitResource::collection($model_query),
    ], 200);
  }

  // public function getProduct($store_domain,$product_domain)
  // {
  //   $data=[];

  //   if ($store_domain=="") {
  //     throw new MyException("Maaf nama toko tidak boleh kosong");
  //   }

  //   $store=SellerStore::where("domain",$store_domain)->first();
  //   if (!$store) {
  //     throw new MyException("Maaf toko tidak ditemukan");
  //   }

  //   if ($product_domain=="") {
  //     throw new MyException("Maaf nama produk tidak boleh kosong");
  //   }

  //   $product=Product::where("domain",$product_domain)->first();
  //   if (!$product) {
  //     throw new MyException("Maaf produk tidak ditemukan");
  //   }

  //   // $purchaseOrder=PurchaseOrder::sellerAvailable($store->seller->id)->first();
  //   $avaliable=PurchaseOrder::produkAvailable($store->seller->id,$product->id)->first() ? true : false;

  //   $data=[
  //     "domain"=>$product->domain,
  //     "name"=>$product->name,
  //     "price"=>$product->price,
  //     "image"=>$product->image,
  //     "available"=>$avaliable
  //   ];

  //   return response()->json([
  //     "data"=>$data
  //   ],200);

  // }
  public function show(UnitRequest $request)
  {
    // MyLib::checkScope($this->auth, ['ap-member-view']);
    MyAdmin::checkScope($this->permissions, 'unit.view');

    $model_query = Unit::with(['created_by', 'updated_by'])->find($request->id);
    return response()->json([
      "data" => new UnitResource($model_query),
    ], 200);
  }

  public function store(UnitRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'unit.create');

    $name = $request->name;

    DB::beginTransaction();
    $t_stamp = date("Y-m-d H:i:s");

    try {

      $model_query             = new Unit();
      $model_query->name       = $request->name;
      $model_query->created_at = $t_stamp;
      $model_query->created_user = $this->admin_id;
      $model_query->updated_at = $t_stamp;
      $model_query->updated_user = $this->admin_id;
      $model_query->save();
      MyLog::sys("unit",$model_query->id,"insert");

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
        "message" => "Proses tambah data gagal",
        // "message" => $e->getMessage(),

      ], 400);
    }
  }

  public function update(UnitRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'unit.modify');

    DB::beginTransaction();
    $t_stamp = date("Y-m-d H:i:s");

    try {
      $model_query             = Unit::find($request->id);
      $SYSOLD                  = clone($model_query);

      $model_query->name       = $request->name;
      $model_query->updated_at = $t_stamp;
      $model_query->updated_user = $this->admin_id;
      $model_query->save();

      $SYSNOTE = MyLib::compareChange($SYSOLD,$model_query); 
      MyLog::sys("unit",$request->id,"update",$SYSNOTE);

      DB::commit();
      return response()->json([
        "message" => "Proses ubah data berhasil",
        "updated_at" => $t_stamp,
        "updated_user"=>$model_query->updated_user,
        "updated_by"=>$model_query->updated_user ? new IsUserResource(IsUser::where("id_user",$model_query->updated_user)->first()) : null,
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();
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

  public function delete(UnitRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'unit.remove');
    // MyAdmin::checkRole($this->role, ['Super Admin','User']);

    DB::beginTransaction();

    try {
      $model_query = Unit::find($request->id);
      if (!$model_query) {
        throw new \Exception("Data tidak terdaftar", 1);
      }
      $model_query->delete();
      MyLog::sys("unit",$request->id,"delete");

      DB::commit();
      return response()->json([
        "message" => "Proses hapus data berhasil",
      ], 200);
    } catch (\Exception  $e) {
      DB::rollback();

      // return response()->json([
      //   "getCode" => $e->getCode(),
      //   "line" => $e->getLine(),
      //   "message" => $e->getMessage(),
      // ], 400);

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
