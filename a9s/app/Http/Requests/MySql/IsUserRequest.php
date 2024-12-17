<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class IsUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [];
        // if (request()->isMethod('post')) {
        //     $rules['username'] = 'required|max:255|unique:App\Models\MySql\IsUser,username';
        //     $rules['password'] = 'required';
        // }
        if (request()->isMethod('get')) {
            $rules['id'] = 'required|exists:App\Models\MySql\IsUser,id_user';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\IsUser,id_user';
            // $rules['username'] = 'required|max:255|unique:App\Models\MySql\IsUser,username,' . request()->id;
        }
        // if (request()->isMethod('post') || request()->isMethod('put')) {
        //     // $rules['hak_akses'] = 'required|in:SuperAdmin,ViewOnly,Logistic,Finance,Marketing,MIS,PabrikTransport,Accounting,PabrikMandor';
        //     // $rules['is_active'] = 'required|in:0,1';
        // }
        return $rules;
    }

    public function messages()
    {
        return [
            'id.required' => 'ID tidak boleh kosong',
            'id.exists' => 'ID tidak terdaftar',

            'username.required' => 'UserNama Tidak boleh kosong',
            'username.max' => 'UserNama tidak boleh lebih dari 255 karakter',
            'username.unique' => 'UserNama sudah terdaftar',

            'password.required' => 'Password Tidak boleh kosong',

            'hak_akses.required' => 'Hak Akses Tidak boleh kosong',
            'hak_akses.in' => 'Hak Akses harus dipilih',

            'is_active.required' => 'Status Tidak boleh kosong',
            'is_active.in' => 'Status harus dipilih',

        ];
    }
}
