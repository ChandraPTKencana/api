<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Helpers\MyLib;
use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Model\Main\Member;
use App\Http\Resources\Internal\MemberResource;
use App\Http\Requests\Internal\MemberRequest;
use Exception;
use Illuminate\Support\Facades\DB;
use Image;
use File;

class MemberController extends Controller
{
  private $auth;
  public function __construct(Request $request)
  {
    $this->auth = \App\Helpers\MyAdmin::user();
  }

  public function index(Request $request)
  {
    \App\Helpers\MyAdmin::checkScope($this->auth, ['ap-member-view']);

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
    $model_query = Member::offset($offset)->limit($limit);

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

      if (isset($sort_lists["username"])) {
        $model_query = $model_query->orderBy("username", $sort_lists["username"]);
      }

      if (isset($sort_lists["email"])) {
        $model_query = $model_query->orderBy("email", $sort_lists["email"]);
      }

      if (isset($sort_lists["fullname"])) {
        $model_query = $model_query->orderBy("fullname", $sort_lists["fullname"]);
      }

      // if (isset($sort_lists["role"])) {
      //   $model_query = $model_query->orderBy("role", $sort_lists["role"]);
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

      if (isset($like_lists["username"])) {
        $model_query = $model_query->orWhere("username", "like", $like_lists["username"]);
      }

      if (isset($like_lists["email"])) {
        $model_query = $model_query->orWhere("email", "like", $like_lists["email"]);
      }

      if (isset($like_lists["fullname"])) {
        $model_query = $model_query->orWhere("fullname", "like", $like_lists["fullname"]);
      }

      // if (isset($like_lists["role"])) {
      //   $model_query = $model_query->orWhere("role", "like", $like_lists["role"]);
      // }
    }

    // ==============
    // Model Filter
    // ==============


    if (isset($request->username)) {
      $model_query = $model_query->where("username", 'like', '%' . $request->username . '%');
    }
    if (isset($request->email)) {
      $model_query = $model_query->where("email", 'like', '%' . $request->email . '%');
    }
    if (isset($request->fullname)) {
      $model_query = $model_query->where("fullname", 'like', '%' . $request->fullname . '%');
    }
    // if (isset($request->role)) {
    //   $model_query = $model_query->where("role", 'like', '%' . $request->role . '%');
    // }

    $model_query = $model_query->with(['internal_creator', 'internal_updator'])->get();

    return response()->json([
      // "data"=>EmployeeResource::collection($employees->keyBy->id),
      "data" => MemberResource::collection($model_query),
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
  public function show(MemberRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-member-view']);

    $model_query = Member::with(['internal_creator', 'internal_updator'])->find($request->id);
    return response()->json([
      "data" => new MemberResource($model_query),
    ], 200);
  }

  public function store(MemberRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-member-add']);

    $can_login = $request->can_login;
    $password = $request->password;

    if ($can_login == 1 && $password == null) {
      return response()->json([
        "password" => ["Password Perlu diisi"],
      ], 422);
    }




    DB::beginTransaction();
    try {

      $new_image = $request->file('photo');
      if ($new_image != null) {
        $date = new \DateTime();
        $timestamp = $date->format("Y-m-d H:i:s.v");
        $ext = $new_image->extension();
        $file_name = md5(preg_replace('/( |-|:)/', '', $timestamp)) . '.' . $ext;
        $location = "files/members/{$file_name}";
        try {
          ini_set('memory_limit', '256M');
          Image::make($new_image)->save(files_path($location));
        } catch (\Exception $e) {
          throw new Exception($e->getMessage());
          throw new Exception("Simpan Foto Gagal");
        }
      } else {
        $location = null;
      }


      $model_query                      = new Member();
      $model_query->username            = $request->username;
      $model_query->email               = MyLib::emptyStrToNull($request->email);
      $model_query->phone_number        = MyLib::emptyStrToNull($request->phone_number);
      $model_query->fullname            = MyLib::emptyStrToNull($request->fullname);
      if ($password)
        $model_query->password          = bcrypt($password);
      $model_query->can_login           = $request->can_login;
      $model_query->photo               = $location;

      $model_query->internal_created_at = MyLib::manualMillis(date("Y-m-d H:i:s"));
      $model_query->internal_created_by = $this->auth->id;
      $model_query->internal_updated_at = MyLib::manualMillis(date("Y-m-d H:i:s"));
      $model_query->internal_updated_by = $this->auth->id;
      $model_query->save();

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

      return response()->json([
        // "message" => "Proses tambah data gagal",
        "message" => $e->getMessage(),

      ], 400);
    }
  }

  public function update(MemberRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-member-edit']);

    $can_login = $request->can_login;
    $password = $request->password;
    $photo_preview = $request->photo_preview;

    DB::beginTransaction();
    try {
      $new_image = $request->file('photo');

      if ($new_image != null) {
        $date = new \DateTime();
        $timestamp = $date->format("Y-m-d H:i:s.v");
        $ext = $new_image->extension();
        $file_name = md5(preg_replace('/( |-|:)/', '', $timestamp)) . '.' . $ext;
        $filePath = "files/members/";
        $location = $filePath . $file_name;
        // $location = "files/members/{$file_name}";

        ini_set('memory_limit', '256M');
        // $url = "files/directory/brochures/{$file_name}";
        // Image::make($new_image)->save(public_path($location));
        // Image::make($new_image)->save(files_path($url));
        $new_image->move(files_path($filePath), $file_name);
      }

      if ($new_image == null && $photo_preview == null) {
        $location = null;
      }

      $model_query                      = Member::find($request->id);


      if ($photo_preview == null) {
        if (File::exists(files_path($model_query->photo)) && $model_query->photo != null) {
          unlink(files_path($model_query->photo));
        }

        // if(File::exists(files_path($data->url)) && $data->url != null){
        //   unlink(files_path($data->url));
        // }
      }


      if ($can_login == 1 && $password == null && $model_query->password == null) {
        return response()->json([
          "password" => ["Password Perlu diisi"],
        ], 422);
      }

      $model_query->username            = $request->username;
      $model_query->email               = MyLib::emptyStrToNull($request->email);
      $model_query->phone_number        = MyLib::emptyStrToNull($request->phone_number);
      $model_query->fullname            = MyLib::emptyStrToNull($request->fullname);
      if ($password)
        $model_query->password          = bcrypt($password);
      $model_query->can_login           = $request->can_login;
      $model_query->photo               = $location;

      $model_query->internal_updated_at = MyLib::manualMillis(date("Y-m-d H:i:s"));
      $model_query->internal_updated_by = $this->auth->id;
      $model_query->save();

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


  public function delete(MemberRequest $request)
  {
    MyLib::checkScope($this->auth, ['ap-member-remove']);

    DB::beginTransaction();

    try {

      $model_query = Member::find($request->id);
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
}
