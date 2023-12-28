<?php

namespace App\Http\Controllers\Internal\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Helpers\MyLib;
use App\Helpers\MyAdmin;

use App\Exceptions\MyException;
use App\Helpers\MyLog;
use App\Model\Internal\User;
use App\Model\Employee;
use App\Http\Resources\Internal\UserPermissionResource;
use App\Http\Requests\Internal\UserPermissionRequest;

use Illuminate\Support\Facades\DB;

class UserPermissionController extends Controller
{
  private $auth;
  public function __construct(Request $request)
  {
    $this->auth = MyAdmin::user();
  }

  public function getActionPermissions()
  {
    return response()->json([
      "data" => \App\Model\Internal\ActionPermission::get(),
    ], 200);
  }

  public function getDataPermissions()
  {
    return response()->json([
      "data" => \App\Model\Internal\DataPermission::get(),
    ], 200);
  }

  public function show(UserPermissionRequest $request)
  {
    MyAdmin::checkScope($this->auth, ['ap-user_permission-view']);

    $model_query = User::with([
      "action_permissions", "data_permissions"
    ])->find($request->id);
    return response()->json([
      "data" => new UserPermissionResource($model_query),
    ], 200);
  }

  public function update(UserPermissionRequest $request)
  {
    MyAdmin::checkScope($this->auth, ['ap-user_permission-edit']);


    $action_permissions_in = json_decode($request->action_permissions, true);
    $data_permissions_in = json_decode($request->data_permissions, true);

    $rules = [
      'action_permissions' => 'nullable|array',
      'action_permissions.*.id' => 'required|exists:\App\Model\Internal\ActionPermission,id',
      'data_permissions' => 'nullable|array',
      'data_permissions.*.id' => 'required|exists:\App\Model\Internal\DataPermission,id',
    ];

    $messages = [
      'action_permissions.array' => 'Format Action Permission Salah',
      'action_permissions.*.id.required' => 'Action permission id kosong',
      'action_permissions.*.id.exists' => 'ID Action permission tidak tersedia',

      'data_permissions.array' => 'Format Data permission Salah',
      'data_permissions.*.id.required' => 'Data permission id kosong',
      'data_permissions.*.id.exists' => 'ID Data permission tidak tersedia',
    ];

    // // Replace :index with the actual index value in the custom error messages
    // foreach ($quotation_details_in as $index => $msg) {
    //     $messages["quotation_details.{$index}.qty.required"] = "Baris #".($index + 1).". Quantity yang diminta tidak boleh kosong.";
    //     $messages["quotation_details.{$index}.qty.numeric"] = "Baris #".($index + 1).".Quantity yang diminta harus angka";
    //     $messages["quotation_details.{$index}.qty.min"] = "Baris #".($index + 1).".Quantity minimal 1";
    //     $messages["quotation_details.{$index}.quotation_item.required"] = "Baris #".($index + 1).".Item di detail quotation harus di isi";
    //     $messages["quotation_details.{$index}.quotation_item.array"] = "Baris #".($index + 1).". Format Item di detail quotation Salah";
    //     $messages["quotation_details.{$index}.quotation_item.code.required"] = "Baris #".($index + 1).". Item harus di isi";
    //     $messages["quotation_details.{$index}.quotation_item.code.exists"] = "Baris #".($index + 1).". Item tidak terdaftar";
    // }

    $validator = \Validator::make([
      'action_permissions' => $action_permissions_in,
      'data_permissions' => $data_permissions_in,
    ], $rules, $messages);

    // Check if validation fails
    if ($validator->fails()) {
      foreach ($validator->messages()->all() as $k => $v) {
        throw new MyException(["message" => $v], 400);
      }
    }

    DB::beginTransaction();
    try {
      $model_query = User::find($request->id);

      \App\Model\Internal\UserPermission::where("user_id", $model_query->id)->delete();

      $ids = [];
      foreach ($action_permissions_in as $key => $value) {

        if (in_array(strtolower($value['id']), $ids) == 1) {
          throw new \Exception("Maaf terdapat Nama Item yang sama");
        }
        array_push($ids, strtolower($value['id']));

        $child_query = new \App\Model\Internal\UserPermission();
        $child_query->user_id              = $model_query->id;
        $child_query->action_permission_id = $value['id'];
        $model_query->internal_created_by           = $this->auth->id;
        $model_query->internal_created_at           = date("Y-m-d H:i:s");
        $child_query->save();
      }
      $ids = [];


      foreach ($data_permissions_in as $key => $value) {

        if (in_array(strtolower($value['id']), $ids) == 1) {
          throw new \Exception("Maaf terdapat Nama Item yang sama");
        }
        array_push($ids, strtolower($value['id']));

        $child_query                      = new \App\Model\Internal\UserPermission();
        $child_query->user_id             = $model_query->id;
        $child_query->data_permission_id  = $value['id'];
        $model_query->internal_created_by          = $this->auth->id;
        $model_query->internal_created_at          = date("Y-m-d H:i:s");
        $child_query->save();
      }

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
}
