<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Exceptions\MyException;

use App\Helpers\MyAdmin;
use App\Helpers\MyLib;
use App\Helpers\MyLog;
use App\Models\MySql\PermissionUserDetail;
use App\Models\MySql\IsUser;

use App\Http\Requests\MySql\IsUserRequest;

use App\Http\Resources\MySql\IsUserResource;


class UserController extends Controller
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

    MyAdmin::checkScope($this->permissions, 'user.views');

    // \App\Helpers\MyAdmin::checkScope($this->auth, ['ap-user-view']);

    //======================================================================================================
    // Pembatasan Data hanya memerlukan limit dan offset
    //======================================================================================================

    $limit = 1000; // Limit +> Much Data
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
    $model_query = IsUser::offset($offset)->limit($limit);

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

      $list_to_like = ["id_user","username"];

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
      $model_query = $model_query->orderBy('username', 'asc')->orderBy('id_user','DESC');
    }
    
    $filter_status = $request->filter_status;
    
    // if($filter_status=="active"){
    //   $model_query = $model_query->where("is_active",1);
    // }

    // if($filter_status=="nonactive"){
    //   $model_query = $model_query->where("is_active",0);
    // }

    $model_query=$model_query->with(['permission_group_users'=>function ($q){
      $q->with('permission_group');      
    }])->where('status','aktif');

    $model_query = $model_query->get();

    return response()->json([
      // "data"=>EmployeeResource::collection($employees->keyBy->id),
      "data" => IsUserResource::collection($model_query),
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
  public function show(IsUserRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'user.view');

    $model_query = IsUser::with([
      'details'=>function($q){
        $q->orderBy("ordinal","asc");
      },
      'permission_group_users'=>function ($q){
        $q->with('permission_group');      
      }
    ])->where("id_user",$request->id)->first();
    return response()->json([
      "data" => new IsUserResource($model_query),
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

  // public function store(IsUserRequest $request)
  // {
  //   MyAdmin::checkScope($this->permissions, 'user.create');

  //   $permission_list_in = json_decode($request->permission_list, true);
  //   $this->validateItems($permission_list_in);

  //   if(count($permission_list_in)>0){
  //     MyAdmin::checkScope($this->permissions, 'permission_user.insert');
  //   }

  //   $rollback_id = -1;
  //   DB::beginTransaction();
  //   $t_stamp = date("Y-m-d H:i:s");
  //   try {
  //     $model_query             = new IsUser();
  //     $model_query->username      = trim($request->username);
  //     if ($request->password) {
  //       $model_query->password = bcrypt($request->password);
  //     }
  //     // $model_query->employee_no = $request->employee_no;
  //     $model_query->hak_akses  = $request->hak_akses;
  //     $model_query->is_active  = $request->is_active;
  //     $model_query->created_at = $t_stamp;
  //     $model_query->created_user = $this->admin_id;
  //     $model_query->updated_at = $t_stamp;
  //     $model_query->updated_user = $this->admin_id;
  //     $model_query->save();

  //     $rollback_id = $model_query->id - 1;

  //     // if($request->employee_no){
  //     //   $employee = Employee::where("no",$request->employee_no)->first();
  //     //   if(!$employee){
  //     //     throw new \Exception("Pegawai tidak terdaftar",1);
  //     //   }

  //     //   if($employee->which_user_id !== null){
  //     //     throw new \Exception("Pegawai tidak tersedia",1);
  //     //   }
  //     //   Employee::where("no",$request->employee_no)->update(["which_user_id"=>$model_query->id]);
  //     // }

  //     $ordinal=0;
  //     foreach ($permission_list_in as $key => $value) {
  //       $ordinal = $key + 1;
  //       PermissionUserDetail::insert([
  //         'ordinal' => $ordinal,
  //         'user_id' => $model_query->id,
  //         'permission_list_name' => $value['name'],
  //         'created_at' => $t_stamp,
  //         'created_user' => $this->admin_id,
  //         'updated_at' => $t_stamp,
  //         'updated_user' => $this->admin_id,
  //       ]);
  //     }
      

  //     DB::commit();
  //     return response()->json([
  //       "message" => "Proses tambah data berhasil",
  //       "id"=>$model_query->id,
  //       "created_at" => $t_stamp,
  //       "updated_at" => $t_stamp,
  //     ], 200);
  //   } catch (\Exception $e) {
  //     DB::rollback();

  //     if($rollback_id>-1)
  //     DB::statement("ALTER TABLE is_users AUTO_INCREMENT = $rollback_id");

  //     if ($e->getCode() == 1) {
  //       return response()->json([
  //         "message" => $e->getMessage(),
  //       ], 400);
  //     }

  //     // return response()->json([
  //     //   "message"=>$e->getMessage(),
  //     // ],400);

  //     return response()->json([
  //       "message" => "Proses tambah data gagal"
  //     ], 400);
  //   }
  // }

  public function update(IsUserRequest $request)
  {
    MyAdmin::checkScope($this->permissions, 'user.modify');

    $permission_list_in = json_decode($request->permission_list, true);
    $this->validateItems($permission_list_in);

    $t_stamp = date("Y-m-d H:i:s");
    DB::beginTransaction();
    try {
      $model_query              = IsUser::where("id_user",$request->id)->first();
      // $model_query->username       = trim($request->username);
      // if ($request->password) {
      //   $model_query->password  = bcrypt($request->password);
      // }
      // $model_query->hak_akses    = $request->hak_akses;
      // $model_query->is_active   = $request->is_active;
      // $model_query->updated_at  = $t_stamp;
      // $model_query->updated_user  = $this->admin_id;

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
      //start for permission_list
      $data_from_db = PermissionUserDetail::where('user_id', $model_query->id_user)
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
        MyAdmin::checkScope($this->permissions, 'permission_user.insert');
      }

      if(count(array_diff($from_dt, $in_dt))>0) {
        MyAdmin::checkScope($this->permissions, 'permission_user.remove');
      }

      $ordinal=0;
      foreach ($data_from_db as $k => $v) {
        $search = array_search($v->st_permission_list_name,$in_dt);
        if($search===false){          
          PermissionUserDetail::where('user_id',$v->user_id)->where('st_permission_list_name',$v->st_permission_list_name)
          ->delete();
        }else{
          $ordinal++;
          
          $updateV = [
            'ordinal' => $ordinal,
            'p_change' => false,
            'updated_at' => $t_stamp,
            'updated_user' => $this->admin_id
          ];

          PermissionUserDetail::where('user_id',$v->user_id)->where('st_permission_list_name',$v->st_permission_list_name)
          ->update($updateV);
        }
        
        $in_dt = array_filter($in_dt,function($q)use($v){
          return $q != $v->st_permission_list_name;
        });

      }

      foreach ($in_dt as $k => $v) {
        $ordinal++;
        PermissionUserDetail::insert([
            'user_id'                 => $model_query->id_user,
            'ordinal'                 => $ordinal,
            "st_permission_list_name" => $v,
            'created_at'              => $t_stamp,
            'created_user'            => $this->admin_id,
            'updated_at'              => $t_stamp,
            'updated_user'            => $this->admin_id,
        ]);
      }

      // $model_query->save();
      //end for permission_list
      DB::commit();
      return response()->json([
        "message" => "Proses ubah data berhasil",
        "updated_at" => $t_stamp,
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


  // public function delete(IsUserRequest $request)
  // {
  //   MyAdmin::checkScope($this->permissions, 'user.remove');

  //   DB::beginTransaction();

  //   try {

  //     $model_query = IsUser::find($request->id);
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
  //     if ($e->getCode() == "23000")
  //       return response()->json([
  //         "message" => "Data tidak dapat dihapus, data terkait dengan data yang lain nya",
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
  //   // if ($model_query->delete()) {
  //   //     return response()->json([
  //   //         "message"=>"Proses ubah data berhasil",
  //   //     ],200);
  //   // }

  //   // return response()->json([
  //   //     "message"=>"Proses ubah data gagal",
  //   // ],400);
  // }
}
