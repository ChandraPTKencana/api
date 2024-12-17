<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Exceptions\MyException;

use App\Helpers\MyAdmin;

use App\Models\MySql\PermissionList;

use App\Http\Resources\MySql\PermissionListResource;

class PermissionListController extends Controller
{
  private $admin;
  private $admin_id;
  private $permissions;

  public function __construct(Request $request)
  {
    $this->admin = MyAdmin::user();
    $this->admin_id = $this->admin->the_user->id;
    // $this->permissions = $this->admin->the_user->listPermissions();

  }

  public function index(Request $request)
  {
    // MyAdmin::checkScope($this->permissions, 'permission_list.views');

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
    // if (isset($request->page)) {
    //   $page =  (int) $request->page;
    //   $offset = ($page * $limit) - $limit;
    // }


    //======================================================================================================
    // Init Model
    //======================================================================================================
    // $model_query = PermissionList::offset($offset)->limit($limit);
    $model_query = new PermissionList();

    $first_row=[];
    if($request->first_row){
      $first_row 	= json_decode($request->first_row, true);
    }

    //======================================================================================================
    // Model Sorting | Example $request->sort = "username:desc,role:desc";
    //======================================================================================================
    

    // if ($request->sort) {
    //   // $sort_lists = [];

    //   // $sorts = explode(",", $request->sort);
    //   // foreach ($sorts as $key => $sort) {
    //   //   $side = explode(":", $sort);
    //   //   $side[1] = isset($side[1]) ? $side[1] : 'ASC';
    //   //   $sort_symbol = $side[1] == "desc" ? "<=" : ">=";
    //   //   $sort_lists[$side[0]] = $side[1];
    //   // }

    //   // // if (isset($sort_lists["id"])) {
    //   // //   $model_query = $model_query->orderBy("id", $sort_lists["id"]);
    //   // //   if (count($first_row) > 0) {
    //   // //     $model_query = $model_query->where("id",$sort_symbol,$first_row["id"]);
    //   // //   }
    //   // // }

    //   // if (isset($sort_lists["name"])) {
    //   //   $model_query = $model_query->orderBy("name", $sort_lists["name"]);
    //   //   if (count($first_row) > 0) {
    //   //     $model_query = $model_query->where("name",$sort_symbol,$first_row["name"]);
    //   //   }
    //   // }
      

    // } else {
    //   $model_query = $model_query->orderBy('name', 'asc');
    // }

    $model_query = $model_query->orderBy('name', 'asc');

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
            
          // if (isset($like_lists["id"])) {
          //   $q->orWhere("id", "like", $like_lists["id"]);
          // }
    
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
      "data" => PermissionListResource::collection($model_query),
    ], 200);
  }
}
