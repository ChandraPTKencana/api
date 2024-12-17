<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class ExtraMoneyTrxRequest extends FormRequest
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
            $rules['id'] = 'required|exists:App\Models\MySql\ExtraMoneyTrx,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\ExtraMoneyTrx,id';
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['extra_money_id']        = 'required|exists:App\Models\MySql\ExtraMoney,id';
            $rules['prev_trx_trp_id']       = 'required|exists:App\Models\MySql\TrxTrp,id';
            $rules['tanggal']               = 'required|date_format:Y-m-d';
            $rules['employee_id']           = 'required|exists:App\Models\MySql\Employee,id';
            $rules['no_pol']                = 'required|exists:App\Models\MySql\Vehicle,no_pol';

            // $rules['cost_center_code']      = 'required';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            
            'id.required'               => 'ID tidak boleh kosong',
            'id.exists'                 => 'ID tidak terdaftar',

            'extra_money_id.required'   => 'ID Uang Tambahan tidak boleh kosong',
            'extra_money_id.exists'     => 'ID Uang Tambahan tidak terdaftar',

            'prev_trx_trp_id.required'  => 'ID Trx Trp tidak boleh kosong',
            'prev_trx_trp_id.exists'    => 'ID Trx Trp tidak terdaftar',

            'tanggal.required'          => 'Tanggal tidak boleh kosong',
            'tanggal.date_format'       => 'Format tanggal salah',

            'employee_id.required'   => 'ID Pekerja tidak boleh kosong',
            'employee_id.exists'     => 'ID Pekerja tidak terdaftar',

            'no_pol.required'           => 'No Pol tidak boleh kosong',
            'no_pol.exists'             => 'No Pol tidak terdaftar',

            'cost_center_code.required' => 'Cost Center Code tidak boleh kosong',

        ];
    }
}
