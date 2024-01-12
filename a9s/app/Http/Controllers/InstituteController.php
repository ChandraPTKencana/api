<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Helpers\MyLib;
use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Model\Main\Institute;
use App\Http\Resources\Internal\InstituteResource;
use App\Http\Requests\Internal\InstituteRequest;

use Illuminate\Support\Facades\DB;

class InstituteController extends Controller
{
  private $auth;
  public function __construct(Request $request)
  {
    $this->auth = \App\Helpers\MyAdmin::user();
  }

  public function index(Request $request)
  {
    \App\Helpers\MyAdmin::checkScope($this->auth, ['ap-institute-view']);

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
    $model_query = Institute::offset($offset)->limit($limit);

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

      if (isset($sort_lists["name"])) {
        $model_query = $model_query->orderBy("name", $sort_lists["name"]);
      }

      if (isset($sort_lists["contact_number"])) {
        $model_query = $model_query->orderBy("contact_number", $sort_lists["contact_number"]);
      }

      if (isset($sort_lists["contact_person"])) {
        $model_query = $model_query->orderBy("contact_person", $sort_lists["contact_person"]);
      }

      if (isset($sort_lists["active_until"])) {
        $model_query = $model_query->orderBy("active_until", $sort_lists["active_until"]);
      }
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

      if (isset($like_lists["name"])) {
        $model_query = $model_query->orWhere("name", "like", $like_lists["name"]);
      }

      if (isset($like_lists["contact_number"])) {
        $model_query = $model_query->orWhere("contact_number", "like", $like_lists["contact_number"]);
      }

      if (isset($like_lists["contact_person"])) {
        $model_query = $model_query->orWhere("contact_person", "like", $like_lists["contact_person"]);
      }
    }

    // ==============
    // Model Filter
    // ==============


    if (isset($request->name)) {
      $model_query = $model_query->where("name", 'like', '%' . $request->name . '%');
    }
    if (isset($request->contact_number)) {
      $model_query = $model_query->where("contact_number", 'like', '%' . $request->contact_number . '%');
    }
    if (isset($request->contact_person)) {
      $model_query = $model_query->where("contact_person", 'like', '%' . $request->contact_person . '%');
    }

    $model_query = $model_query->with('internal_marketer')->get();

    return response()->json([
      // "data"=>EmployeeResource::collection($employees->keyBy->id),
      "data" => InstituteResource::collection($model_query),
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
  public function show(InstituteRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-institute-view']);

    $model_query = Institute::with([
      'internal_marketer',
      'members' => function ($q) {
        $q->wherePivot('role', 'operator');
        $q->where(function ($q2) {
          $q2->whereNotNull("member_institutes.internal_created_by")
            ->orWhereNotNull("member_institutes.internal_updated_by");
        });
      }
    ])->find($request->id);
    return response()->json([
      "data" => new InstituteResource($model_query),
    ], 200);
  }

  public function store(InstituteRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-institute-add']);

    $marketer_id = $request->internal_marketer_id;
    if (!\App\Model\Internal\User::where("id", $marketer_id)->where("role", "Marketing")->first()) {
      return response()->json([
        "internal_marketer_id" => ["Marketer harus memiliki role Marketing"]
      ], 422);
    }

    $link_name = MyLib::textToLink($request->name);
    if (Institute::where("link_name", $link_name)->first()) {
      return response()->json([
        "name" => ["Maaf Nama Telah Digunakan"]
      ], 422);
    }

    DB::beginTransaction();
    try {
      $model_query             = new Institute();
      $model_query->name      = $request->name;
      $model_query->link_name = $link_name;
      $model_query->address = $request->address;
      $model_query->contact_number   = $request->contact_number;
      $model_query->contact_person       = $request->contact_person;
      $model_query->active_until  = $request->active_until;
      $model_query->internal_marketer_by = $marketer_id;
      $model_query->internal_created_at = MyLib::manualMillis(date("Y-m-d H:i:s"));
      $model_query->internal_created_by = $this->auth->id;
      $model_query->internal_updated_at = MyLib::manualMillis(date("Y-m-d H:i:s"));
      $model_query->internal_updated_by = $this->auth->id;
      $model_query->save();

      $operator_member_id = $request->operator_member_id;
      if ($operator_member_id) {

        \App\Model\Main\MemberInstitute::insert([
          "member_id" => $operator_member_id,
          "institute_id" => $model_query->id,
          "role" => "operator",
          "internal_created_at" => MyLib::manualMillis(date("Y-m-d H:i:s")),
          "internal_created_by" => $this->auth->id,
          "internal_updated_at" => MyLib::manualMillis(date("Y-m-d H:i:s")),
          "internal_updated_by" => $this->auth->id,
        ]);
      }

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

  public function update(InstituteRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-institute-edit']);

    $marketer_id = $request->internal_marketer_id;
    if (!\App\Model\Internal\User::where("id", $marketer_id)->where("role", "Marketing")->first()) {
      return response()->json([
        "internal_marketer_id" => ["Marketer harus memiliki role Marketing"]
      ], 422);
    }

    $link_name = MyLib::textToLink($request->name);
    if (Institute::where("id", "!=", $request->id)->where("link_name", $link_name)->first()) {
      return response()->json([
        "name" => ["Maaf Nama Telah Digunakan"]
      ], 422);
    }


    DB::beginTransaction();
    try {
      $model_query              = Institute::find($request->id);
      $model_query->name       = MyLib::beOneSpaces($request->name);
      $model_query->link_name = $link_name;
      $model_query->address    = $request->address;
      $model_query->contact_number        = $request->contact_number;
      $model_query->contact_person = $request->contact_person;
      $model_query->active_until   = $request->active_until;
      $model_query->internal_marketer_by = $marketer_id;
      $model_query->internal_updated_at = MyLib::manualMillis(date("Y-m-d H:i:s"));
      $model_query->internal_updated_by = $this->auth->id;
      $model_query->save();


      $operator_member_id = $request->operator_member_id;

      $mdl_member_institute = \App\Model\Main\MemberInstitute::where("role", "operator")
        ->where("institute_id", $model_query->id)
        ->where(function ($q) {
          $q->whereNotNull("internal_created_by")->whereNotNull("internal_updated_by");
        });

      $member_institute = $mdl_member_institute->first();

      if ($member_institute && ($operator_member_id == null || $operator_member_id !== $member_institute->member_id))
        $mdl_member_institute->delete();


      if ((!$member_institute && $operator_member_id !== null) || ($member_institute && $operator_member_id !== null && $operator_member_id !== $member_institute->member_id))
        \App\Model\Main\MemberInstitute::insert([
          "member_id" => $operator_member_id,
          "institute_id" => $model_query->id,
          "role" => "operator",
          "internal_created_at" => MyLib::manualMillis(date("Y-m-d H:i:s")),
          "internal_created_by" => $this->auth->id,
          "internal_updated_at" => MyLib::manualMillis(date("Y-m-d H:i:s")),
          "internal_updated_by" => $this->auth->id,
        ]);



      if ($member_institute && $operator_member_id !== null && $operator_member_id == $member_institute->member_id) {
        \App\Model\Main\MemberInstitute::where("member_id", $operator_member_id)
          ->where("role", "operator")
          ->where("institute_id", $model_query->id)
          ->update([
            "internal_created_at" => MyLib::manualMillis(date("Y-m-d H:i:s")),
            "internal_created_by" => $this->auth->id,
            "internal_updated_at" => MyLib::manualMillis(date("Y-m-d H:i:s")),
            "internal_updated_by" => $this->auth->id,
          ]);
      }


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
      // return response()->json([
      //   "message" => $e->getLine(),
      // ], 400);
      return response()->json([
        "message" => "Proses ubah data gagal"
      ], 400);
    }
  }


  public function delete(InstituteRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-institute-remove']);

    DB::beginTransaction();

    try {

      $model_query = Institute::find($request->id);
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
