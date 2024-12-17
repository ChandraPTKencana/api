<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class TrxTrpRequest extends FormRequest
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
            $rules['id']            = 'required|exists:App\Models\MySql\TrxTrp,id';
        }
        if (request()->isMethod('put')) {
            $rules['id']            = 'required|exists:App\Models\MySql\TrxTrp,id';
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['tanggal']           = 'required|date_format:Y-m-d';
            // $rules['xto']               = 'required|max:50';
            $rules['id_uj']             = 'required|exists:App\Models\MySql\Ujalan,id';
            // $rules['tipe'] = 'required|max:50';
            $rules['jenis']             = 'required|in:CPO,TBS,PK,TBSK,LAIN';
            $rules['supir_id']          = 'required|exists:App\Models\MySql\Employee,id';
            $rules['kernet_id']         = 'nullable|exists:App\Models\MySql\Employee,id';
            $rules['no_pol']            = 'required|max:12|regex:/(\D)+\s{1}(\d)+\s{1}(\D)+/|exists:App\Models\MySql\Vehicle,no_pol';
            // $rules['online_status']     = 'required';
            $rules['payment_method_id'] = 'required|exists:App\Models\MySql\PaymentMethod,id';

        }
        return $rules;
    }

    public function messages()
    {
        return [
            
            'tanggal.required'              => 'U.Jalan Per tidak boleh kosong',
            'tanggal.date_format'           => 'Format U.Jalan Per Tidak sesuai',

            'id.required'                   => 'ID tidak boleh kosong',
            'id.exists'                     => 'ID tidak terdaftar',

            'id_uj.required'                => 'Uang jalan tidak boleh kosong',
            'id_uj.exists'                  => 'Uang jalan tidak terdaftar',

            // 'xto.required'                  => 'Tujuan tidak boleh kosong',
            // 'xto.max'                       => 'Tujuan tidak boleh melebihi 50 karakter',

            'jenis.required'                => 'Jenis tidak boleh kosong',
            'jenis.in'                      => 'Jenis harus dipilih',

            'supir_id.required'             => 'Supir tidak boleh kosong',
            // 'supir_id.max'               => 'Supir tidak boleh melebihi 255 karakter',
            'supir_id.exists'               => 'Supir tidak terdaftar',

            // 'kernet_id.max'              => 'Kernet tidak boleh melebihi 255 karakter',
            'kernet_id.exists'              => 'Kernet tidak terdaftar',

            'no_pol.required'               => 'No Pol tidak boleh kosong',
            'no_pol.max'                    => 'No Pol tidak boleh melebihi 12 karakter',
            'no_pol.regex'                  => 'Format No Pol salah',
            'no_pol.exists'                 => 'No Pol tidak terdaftar',

            'payment_method_id.required'    => 'Payment Method tidak boleh kosong',
            'payment_method_id.exists'      => 'Payment Method tidak terdaftar',

        ];
    }
}
