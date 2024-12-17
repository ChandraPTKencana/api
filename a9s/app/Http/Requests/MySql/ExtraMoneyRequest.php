<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class ExtraMoneyRequest extends FormRequest
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
            $rules['id'] = 'required|exists:App\Models\MySql\ExtraMoney,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\ExtraMoney,id';
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['xto']                   = 'required|min:1|max:50';
            $rules['jenis']                 = 'required|in:CPO,TBS,PK,TBSK,LAIN';
            $rules['transition_target']     = 'nullable|max:10|in:KPN,KAS,KUS,ARP,KAP,SMP';
            if($this->filled('transition_target')){
                $rules['transition_type']   = 'required|max:4|in:From,To';
            }
            $rules['ac_account_id']         = 'required';
            $rules['qty']                   = 'required|numeric';
            $rules['nominal']                = 'required|numeric';
            $rules['description']           = 'required';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            
            'id.required'               => 'ID tidak boleh kosong',
            'id.exists'                 => 'ID tidak terdaftar',

            'xto.required'              => 'Tipe tidak boleh kosong',
            'xto.in'                    => 'Tipe harus dipilih',

            'jenis.required'            => 'Jenis tidak boleh kosong',
            'jenis.in'                  => 'Jenis Harap Dipilih',

            'transition_target.max'     => 'Asal Pengalihan max 10 karakter',
            'transition_target.in'      => 'Asal Pengalihan harap dipilih',

            'ac_account_id.required'    => 'Acc ID tidak boleh kosong',

            'qty.required'              => 'Qty tidak boleh kosong',
            'qty.numeric'               => 'Format Qty harus angka',

            'nominal.required'           => 'Harga tidak boleh kosong',
            'nominal.numeric'            => 'Format Harga harus angka',

            'description.required'      => 'Deskripsi tidak boleh kosong',

        ];
    }
}
