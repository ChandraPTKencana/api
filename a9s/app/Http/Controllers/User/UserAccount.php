<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\IsUser;
use App\Helpers\MyLib;
use App\Helpers\MyAdmin;
use App\Models\HrmRevisiLokasi;
use Illuminate\Support\Facades\Log;
use DB;
use File;
class UserAccount extends Controller
{
  public function login(Request $request)
  {
    $request["username"] = strtolower($request->username);
    $rules = [
      'username' => 'required|exists:\App\Models\IsUser,username',
      // 'password' => "required|min:8",
      'password' => "required",
    ];

    $messages = [
      'username.required' => 'Nama Pengguna tidak boleh kosong',
      'username.exists' => 'Nama Pengguna tidak terdaftar',
      'password.required' => 'Kata Sandi tidak boleh kosong',
      'password.min' => 'Kata Sandi minimal 8 Karakter',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    $username = $request->username;
    $password = $request->password;
    

    $admin = IsUser::where("username", $username)->first();
    
    if ($admin && $admin->status == 'blokir') {
      return response()->json([
        "message" => "Izin Masuk Tidak Diberikan"
      ], 403);
    }

    if($admin->password != md5($password)){
      return response()->json([
        "message" => "Nama Pengguna dan Kata Sandi tidak cocok"
      ], 403);
    }
    DB::beginTransaction();
    try{

      $token = $admin->generateToken();
      DB::commit();

      return response()->json([
        "message" => "Berhasil login",
        "token" => $token,
      ], 200);

    }catch(\Exception $e){
      DB::rollback();

      // return response()->json([
      //   "message" => $e->getMessage(),
      // ], 400);
      
      return response()->json([
        "message" => "Ada Kesalahan"
      ], 400);
    }
  }


  public function logout(Request $request)
  {
    $admin = MyAdmin::user();
    \App\Models\Session::where("token",$admin->token)->delete();

    return response()->json([
      "message" => "Logout Berhasil",
    ], 200);
  }

  public function checkUser(Request $request)
  {
    $admin = MyAdmin::user();
    return response()->json([
      "message" => "Tampilkan data user",
      "user" => [
        // "id"=>$p_user->id,
        "username" => $admin->the_user->username,
        "fullname" => $admin->the_user->nama_user,
        "role" => $admin->the_user->hak_akses,
        "locs"=>HrmRevisiLokasi::whereRaw("id in (".$admin->the_user->loc.")")->get()
        // // "scope"=>($p_user->role && count($p_user->role->permissions)>0) ? $p_user->role->permissions->pluck('name') : [],
        // "scopes" => $p_user->listPermissions()
      ],
    ], 200);
  }

  public function dataUser(Request $request)
  {
    $admin = MyAdmin::user();
    return response()->json([
      "username" => $admin->the_user->username,
      "fullname" => $admin->the_user->nama_user,
      "email" => $admin->the_user->email ?? '',
      "photo" => $admin->the_user->foto ? ("/ho/images/user/".$admin->the_user->foto) : null,
      "phone_number" => $admin->the_user->telepon,
    ], 200);
  }

  public function change_password(Request $request)
  {
    $admin = MyAdmin::user();

    $rules = [
      'old_password' => 'required|min:8|max:255',
      'password' => 'required|confirmed|min:8|max:255',
      'password_confirmation' => 'required|same:password|min:8|max:255',
    ];

    $rule = [
      "old_password.required" => "Kata Sandi lama harus diisi",
      "old_password.min" => "Kata Sandi lama minimal 8 karakter",
      "old_password.max" => "Kata Sandi lama maksimal 255 karakter",

      "password.required" => "Kata Sandi Baru harus diisi",
      "password.confirmed" => "Kata Sandi Baru tidak cocok",
      "password.min" => "Kata Sandi Baru minimal 8 karakter",
      "password.max" => "Kata Sandi Baru maksimal 255 karakter",

      "password_confirmation.required" => "Ulangi Kata Sandi Baru harus diisi",
      "password_confirmation.same" => "Ulangi Kata Sandi Baru tidak cocok",
      "password_confirmation.min" => "Ulangi Kata Sandi Baru minimal 8 karakter",
      "password_confirmation.max" => "Ulangi Kata Sandi Baru maksimal 255 karakter",
    ];

    $validator = Validator::make($request->all(), $rules, $rule);
    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    $old_password = $request->old_password;
    if (!Hash::check($old_password, $admin->password)) {
      return response()->json([
        "message" => "Kata sandi lama tidak sesuai"
      ], 400);
    }

    $admin->password = bcrypt($request->password);
    $admin->updated_at = MyLib::getMillis();
    $admin->save();

    return response()->json([
      "message" => "Kata sandi berhasil diubah",
    ], 200);
  }

  public function updateUser(Request $request)
  {
    $admin = MyAdmin::user();

    $rules = [
      'username' => 'required|max:50',
      'fullname' => 'required|max:50',
      'email' => 'nullable|email|max:50',
      'phone_number' => 'nullable|max:13',
      'photo' => 'nullable|image|mimes:jpeg,png|max:2048',
    ];

    $rule = [
      'username.required' => 'Nama Pengguna tidak boleh kosong',
      'username.max' => 'Nama Pengguna tidak boleh lebih dari 50 karakter',

      'fullname.required' => 'Nama Identitas tidak boleh kosong',
      'fullname.max' => 'Nama Identitas tidak boleh lebih dari 50 karakter',

      'email.required' => 'Email tidak boleh kosong',
      'email.max' => 'Email tidak boleh lebih dari 50 karakter',
      'email.email' => 'Format Email tidak benar',

      'phone_number.required' => 'Telepon tidak boleh kosong',
      'phone_number.max' => 'Telepon tidak boleh lebih dari 13 karakter',

      'photo.image' => 'Jenis Foto Harus Berupa Gambar',
      'photo.mimes' => 'Tipe Foto harus jpeg ataupun png',
      'photo.max' => 'Foto Maksimal 2048kb',
    ];

    $validator = Validator::make($request->all(), $rules, $rule);
    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    $username = $request->username;
    $fullname = $request->fullname;
    $email = $request->email;
    $phone_number = $request->phone_number;
    $photo_preview = $request->photo_preview;

    $location = null;


    DB::beginTransaction();
    try {
      $new_image = $request->file('photo');
      $filePath = "ho/images/user/";
      $model_query                      = IsUser::find($admin->the_user->id_user);
      $location = $model_query->foto;

      if ($new_image != null) {
        $date = new \DateTime();
        $timestamp = $date->format("Y-m-d H:i:s.v");
        $ext = $new_image->extension();
        $file_name = md5(preg_replace('/( |-|:)/', '', $timestamp)) . '.' . $ext;
        $location = $file_name;

        ini_set('memory_limit', '256M');
        $new_image->move(files_path($filePath), $file_name);
      }

      if ($new_image == null && $photo_preview == null) {
        $location = null;
      }


      if ($photo_preview == null) {
        if (File::exists(files_path($filePath.$model_query->foto)) && $model_query->foto != null) {
          if(!unlink(files_path($filePath.$model_query->foto)))
          throw new \Exception("Gagal",1);
        }
      }

      $model_query->username            = $request->username;
      $model_query->nama_user           = MyLib::emptyStrToNull($request->fullname);
      $model_query->email               = MyLib::emptyStrToNull($request->email);
      $model_query->telepon             = MyLib::emptyStrToNull($request->phone_number);
      $model_query->foto                = $location;

      $model_query->updated_at = date("Y-m-d H:i:s");
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
}
