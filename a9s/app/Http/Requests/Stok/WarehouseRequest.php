<?php

namespace App\Http\Requests\Stok;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
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
        if (request()->isMethod('post')) {
            $rules['name'] = 'required|max:20|regex:/^\S*$/|unique:App\Models\Stok\Warehouse,name';
        }
        if (request()->isMethod('get')) {
            $rules['id'] = 'required|exists:App\Models\Stok\Warehouse,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\Stok\Warehouse,id';
            $rules['name'] = 'required|max:20|regex:/^\S*$/|unique:App\Models\Stok\Warehouse,name,' . request()->id;
        }
        // if (request()->isMethod('post') || request()->isMethod('put')) {
        // }
        return $rules;
    }

    public function messages()
    {
        return [
            'id.required' => 'ID tidak boleh kosong',
            'id.exists' => 'ID tidak terdaftar',

            'name.required' => 'Nama Tidak boleh kosong',
            'name.max' => 'Nama tidak boleh lebih dari 20 karakter',
            'name.regex' => 'Nama tidak boleh mengandung spasi',
            'name.unique' => 'Nama sudah terdaftar',
        ];
    }
}
