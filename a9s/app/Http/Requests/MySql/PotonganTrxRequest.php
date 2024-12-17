<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class PotonganTrxRequest extends FormRequest
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
            $rules['id'] = 'required|exists:App\Models\MySql\PotonganTrx,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\PotonganTrx,id';
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['nominal_cut'] = 'required|numeric';
            $rules['potongan_mst_id'] = 'required|exists:App\Models\MySql\PotonganMst,id';

        }
        return $rules;
    }

    public function messages()
    {
        return [

            'id.required' => 'ID tidak boleh kosong',
            'id.exists' => 'ID tidak terdaftar',

            'nominal.required' => 'Nominal tidak boleh kosong',
            'nominal.numeric' => 'Nominal harus berupa angka',

            'status.required' => 'Status tidak boleh kosong',
            'status.in' => 'Status harus dipilih',

        ];
    }

    protected function prepareForValidation()
    {
        // $this->merge([
        //     'name' => strtoupper($this->name),
        // ]);
    }
}
