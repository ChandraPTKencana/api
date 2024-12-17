<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class SalaryBonusRequest extends FormRequest
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
        if (request()->isMethod('get')) {
            $rules['id'] = 'required|exists:App\Models\MySql\SalaryBonus,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\SalaryBonus,id';
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['tanggal']       = 'required|date_format:Y-m-d';
            $rules['type']          = 'required|in:Kerajinan,Lainnya';
            $rules['employee_id']   = 'required|exists:App\Models\MySql\Employee,id';
            $rules['nominal']       = 'required|numeric';
            // $rules['attachment_1']  = 'nullable|mimes:jpeg,jpg,png,pdf|max:2048';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            
            'id.required'           => 'ID tidak boleh kosong',
            'id.exists'             => 'ID tidak terdaftar',

            'tanggal.required'      => 'Period End tidak boleh kosong',
            'tanggal.date_format'   => 'Format Period End tidak cocok',

            'type.required'         => 'Tipe tidak boleh kosong',
            'type.in'               => 'Tipe harus dipilih',

            'employee_id.required'  => 'ID Pekerja tidak boleh kosong',
            'employee_id.exists'    => 'ID Pekerja tidak terdaftar',

            'nominal.required'      => 'Nominal tidak boleh kosong',
            'nominal.numeric'       => 'Format Nominal harus angka',

            'attachment_1.mimes'    => 'Lampiran Harus Berupa Gambar Atau PDF',
            'attachment_1.max'      => 'Besar File Lampiran maksimal 2048kB ',

        ];
    }
}
