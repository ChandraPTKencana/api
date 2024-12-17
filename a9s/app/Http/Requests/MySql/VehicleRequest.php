<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
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
            $rules['no_pol'] = 'required|max:12|regex:/(\D)+\s{1}(\d)+\s{1}(\D)+/|unique:App\Models\MySql\Vehicle,no_pol';
        }
        if (request()->isMethod('get')) {
            $rules['id'] = 'required|exists:App\Models\MySql\Vehicle,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\Vehicle,id';
            $rules['no_pol'] = 'required|max:12|regex:/(\D)+\s{1}(\d)+\s{1}(\D)+/|unique:App\Models\MySql\Vehicle,no_pol,' . request()->id;
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'id.required' => 'ID tidak boleh kosong',
            'id.exists' => 'ID tidak terdaftar',

            'no_pol.required' => 'No Pol Tidak boleh kosong',
            'no_pol.max' => 'No Pol tidak boleh lebih dari 12 karakter',
            'no_pol.unique' => 'No Pol sudah terdaftar',
            'no_pol.regex' => 'Format No Pol salah',
        ];
    }
}
