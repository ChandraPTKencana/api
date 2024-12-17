<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
            // $rules['name'] = 'required|max:50|unique:App\Models\MySql\Employee,name';
        }
        if (request()->isMethod('get')) {
            $rules['id'] = 'required|exists:App\Models\MySql\Employee,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\Employee,id';
            // $rules['name'] = 'required|max:50|unique:App\Models\MySql\Employee,name,' . request()->id;
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['name'] = 'required|max:50';
            $rules['role'] = 'required|in:Supir,Kernet';
            $rules['bank_id'] = 'nullable|exists:App\Models\MySql\Bank,id';
            // $rules['birth_date']  = 'required|date_format:Y-m-d';
            // $rules['birth_place']  = 'required|max:100';
            
            // $rules['ktp_no'] = 'required_with:id_type';
            // $rules['id_type'] = 'required_with:id_no';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'id.required' => 'ID tidak boleh kosong',
            'id.exists' => 'ID tidak terdaftar',

            'name.required' => 'Nama Tidak boleh kosong',
            'name.max' => 'Nama tidak boleh lebih dari 50 karakter',
            'name.unique' => 'Nama sudah terdaftar',

            'role.required' => 'Jabatan Tidak boleh kosong',
            'role.in' => 'Jabatan harus dipilih',

            'bank_id.exists' => 'Bank harap di pilih',
            // 'id_no.required_with' => 'No ID perlu diisi',
            // 'id_type.required_with' => 'Tipe ID perlu dipilih',

        ];
    }
}
