<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class StandbyTrxRequest extends FormRequest
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
            $rules['id'] = 'required|exists:App\Models\MySql\StandbyTrx,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\StandbyTrx,id';
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['transition_target'] = 'nullable|max:10|in:KPN,KAS,KUS,ARP,KAP,SMP';
            if($this->filled('transition_target')){
                $rules['transition_type'] = 'required|max:4|in:From,To';
            }

            $rules['standby_mst_id'] = 'required|exists:App\Models\MySql\StandbyMst,id';
            // $rules['cost_center_code'] = 'required';
            
            $rules['supir_id']      = 'required_without:kernet_id';
            $rules['kernet_id']     = 'required_without:supir_id';

            // $rules['no_pol'] = 'required|max:12|regex:/(\D)+\s{1}(\d)+\s{1}(\D)+/|exists:App\Models\MySql\Vehicle,no_pol';
            $rules['no_pol'] = 'nullable|max:12|regex:/(\D)+\s{1}(\d)+\s{1}(\D)+/|exists:App\Models\MySql\Vehicle,no_pol';
            $rules['xto'] = 'nullable|max:50|exists:App\Models\MySql\Ujalan,xto';

            // $rules['online_status'] = 'required';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            
            'id.required'                   => 'ID tidak boleh kosong',
            'id.exists'                     => 'ID tidak terdaftar',

            'standby_mst_id.required'       => 'Standby tidak boleh kosong',
            'standby_mst_id.exists'         => 'Standby tidak terdaftar',

            'jenis.required'                => 'Jenis tidak boleh kosong',
            'jenis.in'                      => 'Jenis harus dipilih',

            'supir_id.required_without'     => 'Supir tidak boleh kosong Kalau Kernet Kosong',
            'supir_id.exists'               => 'Supir tidak terdaftar',

            'kernet_id.required_without'    => 'Kernet tidak boleh kosong Kalau Supir Kosong',
            'kernet_id.exists'              => 'Kernet tidak terdaftar',

            'no_pol.required'               => 'No Pol tidak boleh kosong',
            'no_pol.max'                    => 'No Pol tidak boleh melebihi 12 karakter',
            'no_pol.regex'                  => 'Format No Pol salah',
            'no_pol.exists'                 => 'No Pol tidak terdaftar',

            // 'xto.required'                  => 'Tujuan tidak boleh kosong',
            'xto.max'                       => 'Tujuan tidak boleh melebihi 255 karakter',
            'xto.exists'                    => 'Tujuan tidak terdaftar',

            'transition_target.max'         => 'Tujuan Pengalihan tidak boleh melebihi 10 karakter',
            'transition_target.in'          => 'Tujuan Pengalihan harus dipilih',

            'transition_type.required'      => 'Tipe Pengalihan tidak boleh kosong',
            'transition_type.max'           => 'Tipe Pengalihan tidak boleh melebihi 4 karakter',
            'transition_type.in'            => 'Tipe Pengalihan harus dipilih',

            // 'cost_center_code.required'     => 'Cost Center Code tidak boleh kosong',

        ];
    }
}
