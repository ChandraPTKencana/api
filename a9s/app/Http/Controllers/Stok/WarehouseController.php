<?php

namespace App\Http\Controllers\Stok;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Helpers\MyLib;
use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Models\Stok\Warehouse;
use App\Helpers\MyAdmin;
use App\Http\Requests\Stok\WarehouseRequest;
use App\Http\Resources\Stok\WarehouseResource;
use Exception;
use Illuminate\Support\Facades\DB;
use Image;
use File;

class WarehouseController extends Controller
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
    MyAdmin::checkRole($this->role, ['User']);

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
    $model_query = Warehouse::offset($offset)->limit($limit);
    
    if (isset($request->firstRow_created_at)) {
      $data = $data->where("created_at","<=",$request->firstRow_created_at);
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
        $sort_lists[$side[0]] = $side[1];
      }

      if (isset($sort_lists["name"])) {
        $model_query = $model_query->orderBy("name", $sort_lists["name"]);
      }

      if (isset($sort_lists["id"])) {
        $model_query = $model_query->orderBy("id", $sort_lists["id"]);
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

      if (isset($like_lists["name"])) {
        $model_query = $model_query->orWhere("name", "ilike", $like_lists["name"]);
      }

      if (isset($like_lists["id"])) {
        $model_query = $model_query->orWhere("id", "ilike", $like_lists["id"]);
      }

      // if (isset($like_lists["fullname"])) {
      //   $model_query = $model_query->orWhere("fullname", "ilike", $like_lists["fullname"]);
      // }

      // if (isset($like_lists["role"])) {
      //   $model_query = $model_query->orWhere("role", "ilike", $like_lists["role"]);
      // }
    }

    // ==============
    // Model Filter
    // ==============


    if (isset($request->id)) {
      $model_query = $model_query->where("id", 'ilike', '%' . $request->id . '%');
    }
    if (isset($request->name)) {
      $model_query = $model_query->where("name", 'ilike', '%' . $request->name . '%');
    }
    // if (isset($request->fullname)) {
    //   $model_query = $model_query->where("fullname", 'ilike', '%' . $request->fullname . '%');
    // }
    // if (isset($request->role)) {
    //   $model_query = $model_query->where("role", 'ilike', '%' . $request->role . '%');
    // }

    $model_query = $model_query->with(['creator', 'updator'])->get();

    return response()->json([
      // "data"=>EmployeeResource::collection($employees->keyBy->id),
      "data" => WarehouseResource::collection($model_query),
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
  public function show(WarehouseRequest $request)
  {
    // MyLib::checkScope($this->auth, ['ap-member-view']);

    $model_query = Warehouse::with(['creator', 'updator'])->find($request->id);
    return response()->json([
      "data" => new WarehouseResource($model_query),
    ], 200);
  }

  public function store(WarehouseRequest $request)
  {
    MyAdmin::checkRole($this->role, ['User']);

    $name = $request->name;

    DB::beginTransaction();
    try {

      $model_query             = new Warehouse();
      $model_query->name       = $request->name;
      $model_query->created_at = date("Y-m-d H:i:s");
      $model_query->created_by = $this->admin_id;
      $model_query->updated_at = date("Y-m-d H:i:s");
      $model_query->updated_by = $this->admin_id;
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
        "message" => "Proses tambah data gagal",
        // "message" => $e->getMessage(),

      ], 400);
    }
  }

  public function update(WarehouseRequest $request)
  {
    MyAdmin::checkRole($this->role, ['User']);

    DB::beginTransaction();
    try {
      $model_query             = Warehouse::find($request->id);
      $model_query->name       = $request->name;
      $model_query->updated_at = date("Y-m-d H:i:s");
      $model_query->updated_by = $this->admin_id;
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
        // "line" => $e->getLine(),
        "message" => $e->getMessage(),
      ], 400);
      return response()->json([
        "message" => "Proses ubah data gagal"
      ], 400);
    }
  }

  public function delete(WarehouseRequest $request)
  {
    MyAdmin::checkRole($this->role, ['User']);

    DB::beginTransaction();

    try {
      $model_query = Warehouse::find($request->id);
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
