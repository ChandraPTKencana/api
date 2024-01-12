<?php

namespace App\Http\Controllers\Internal\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Helpers\MyLib;
use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Model\Internal\User;
use App\Http\Resources\Internal\UserResource;
use App\Http\Requests\Internal\UserRequest;

use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
  private $auth;
  public function __construct(Request $request)
  {
    $this->auth = \App\Helpers\MyAdmin::user();
  }

  public function index(Request $request)
  {
    \App\Helpers\MyAdmin::checkScope($this->auth, ['ap-user-view']);

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
    $model_query = User::offset($offset)->limit($limit);

    //======================================================================================================
    // Model Sorting | Example $request->sort = "username:desc,role:desc";
    //======================================================================================================

    if ($request->sort) {
      $sort_lists = [];

      $sorts = explode(",", $request->sort);
      foreach ($sorts as $key => $sort) {
        $side = explode(":", $sort);
        $side[1] = isset($side[1]) ? $side[1] : 'ASC';
        $sort_lists[$side[0]] = $side[1];
      }

      if (isset($sort_lists["email"])) {
        $model_query = $model_query->orderBy("email", $sort_lists["email"]);
      }

      if (isset($sort_lists["id"])) {
        $model_query = $model_query->orderBy("id", $sort_lists["id"]);
      }

      if (isset($sort_lists["internal_created_at"])) {
        $model_query = $model_query->orderBy("internal_created_at", $sort_lists["internal_created_at"]);
      }


      // if (isset($sort_lists["role"])) {
      //   $model_query = $model_query->orderBy(function($q){
      //     $q->from("internal.roles")
      //     ->select("name")
      //     ->whereColumn("id","auths.role_id");
      //   },$sort_lists["role"]);
      // }

      // if (isset($sort_lists["auth"])) {
      //   $model_query = $model_query->orderBy(function($q){
      //     $q->from("users as u")
      //     ->select("u.username")
      //     ->whereColumn("u.id","users.id");
      //   },$sort_lists["auth"]);
      // }
    } else {
      $model_query = $model_query->orderBy('id', 'ASC');
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

      if (isset($like_lists["email"])) {
        $model_query = $model_query->orWhere("email", "like", $like_lists["email"]);
      }

      // if (isset($like_lists["role"])) {
      //   $model_query = $model_query->orWhere("role","like",$like_lists["role"]);
      // }
    }

    // ==============
    // Model Filter
    // ==============

    if (isset($request->username)) {
      $model_query = $model_query->where("username", 'like', '%' . $request->username . '%');
    }

    if (isset($request->name)) {
      $model_query = $model_query->where("name", 'like', '%' . $request->name . '%');
    }

    $model_query = $model_query->get();

    return response()->json([
      // "data"=>EmployeeResource::collection($employees->keyBy->id),
      "data" => UserResource::collection($model_query),
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
  public function show(UserRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-user-view']);

    $model_query = User::find($request->id);
    return response()->json([
      "data" => new UserResource($model_query),
    ], 200);
  }

  public function store(UserRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-user-add']);

    DB::beginTransaction();

    try {
      $model_query             = new User();
      $model_query->email      = trim($request->email);
      if ($request->password) {
        $model_query->password = bcrypt($request->password);
      }
      // $model_query->employee_no = $request->employee_no;
      $model_query->fullname   = $request->fullname;
      $model_query->role       = $request->role;
      $model_query->can_login  = $request->can_login;
      $model_query->internal_created_at = MyLib::manualMillis(date("Y-m-d H:i:s"));
      $model_query->internal_created_by = $this->auth->id;
      $model_query->internal_updated_at = MyLib::manualMillis(date("Y-m-d H:i:s"));
      $model_query->internal_updated_by = $this->auth->id;
      $model_query->save();
      // if($request->employee_no){
      //   $employee = Employee::where("no",$request->employee_no)->first();
      //   if(!$employee){
      //     throw new \Exception("Pegawai tidak terdaftar",1);
      //   }

      //   if($employee->which_user_id !== null){
      //     throw new \Exception("Pegawai tidak tersedia",1);
      //   }

      //   Employee::where("no",$request->employee_no)->update(["which_user_id"=>$model_query->id]);

      // }

      DB::commit();
      return response()->json([
        "message" => "Proses tambah data berhasil",
      ], 200);
    } catch (\Exception $e) {
      DB::rollback();

      if ($e->getCode() == 1) {
        return response()->json([
          "message" => $e->getMessage(),
        ], 400);
      }

      // return response()->json([
      //   "message"=>$e->getMessage(),
      // ],400);

      return response()->json([
        "message" => "Proses tambah data gagal"
      ], 400);
    }
  }

  public function update(UserRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-user-edit']);

    DB::beginTransaction();
    try {
      $model_query              = User::find($request->id);
      $model_query->email       = trim($request->email);
      if ($request->password) {
        $model_query->password  = bcrypt($request->password);
      }
      $model_query->fullname    = $request->fullname;
      $model_query->role        = $request->role;
      // $model_query->employee_no = $request->employee_no;
      $model_query->can_login   = $request->can_login;
      $model_query->internal_updated_at  = MyLib::manualMillis(date("Y-m-d H:i:s"));
      $model_query->internal_updated_by  = $this->auth->id;
      $model_query->save();


      // if($request->employee_no){
      //   //Check apakah di update dengan data yang sama
      //   $employee = Employee::where("no",$request->employee_no)->where("which_user_id",$request->id)->first();
      //   // jika berbeda
      //   if(!$employee){
      //     //check used employee and set null
      //     $employee = Employee::where("which_user_id",$request->id)->first();
      //     if($employee)
      //     Employee::where("which_user_id",$request->id)->update(["which_user_id"=>null]);

      //     //add used employee
      //     $employee = Employee::where("no",$request->employee_no)->first();
      //     if(!$employee){
      //       throw new \Exception("Karyawan tidak terdaftar",1);
      //     }
      //     if($employee->which_user_id !== null){
      //       throw new \Exception("Karyawan tidak tersedia",1);
      //     }
      //     Employee::where("no",$request->employee_no)->update(["which_user_id"=>$request->id]);
      //   }
      // }else{
      //   $employee = Employee::where("which_user_id",$request->id)->first();
      //   if($employee){
      //     Employee::where("which_user_id",$request->id)->update(["which_user_id"=>null]);
      //   }
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
        "message" => $e->getMessage(),
      ], 400);
      return response()->json([
        "message" => "Proses ubah data gagal"
      ], 400);
    }
  }


  public function delete(UserRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-user-remove']);

    DB::beginTransaction();

    try {

      $model_query = User::find($request->id);
      if (!$model_query) {
        throw new \Exception("Data tidak terdaftar", 1);
      }
      $model_query->delete();

      DB::commit();
      return response()->json([
        "message" => "Proses ubah data berhasil",
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
    // if ($model_query->delete()) {
    //     return response()->json([
    //         "message"=>"Proses ubah data berhasil",
    //     ],200);
    // }

    // return response()->json([
    //     "message"=>"Proses ubah data gagal",
    // ],400);
  }
}
