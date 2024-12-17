<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class TrxTrpTicketRequest extends FormRequest
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
            // $rules['online_status'] = 'required';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            
            'tanggal.required'      => 'U.Jalan Per tidak boleh kosong',
            'tanggal.date_format'   => 'Format U.Jalan Per Tidak sesuai',

            'id.required'           => 'ID tidak boleh kosong',
            'id.exists'             => 'ID tidak terdaftar',

            'id_uj.required'        => 'Tipe tidak boleh kosong',
            'id_uj.exists'          => 'Tipe tidak terdaftar',

            'xto.required'          => 'Tujuan tidak boleh kosong',
            'xto.max'               => 'Tujuan tidak boleh melebihi 50 karakter',

            'jenis.required'        => 'Jenis tidak boleh kosong',
            'jenis.in'              => 'Jenis harus dipilih',

            'supir_id.required'     => 'Supir tidak boleh kosong',
            // 'supir_id.max'       => 'Supir tidak boleh melebihi 255 karakter',
            'supir_id.exists'       => 'Supir tidak terdaftar',

            // 'kernet_id.max'      => 'Kernet tidak boleh melebihi 255 karakter',
            'kernet_id.exists'      => 'Kernet tidak terdaftar',

            'no_pol.required'       => 'No Pol tidak boleh kosong',
            'no_pol.max'            => 'No Pol tidak boleh melebihi 12 karakter',
            'no_pol.regex'          => 'Format No Pol salah',
            'no_pol.exists'         => 'No Pol tidak terdaftar',

        ];
    }
}
