<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class TrxTrpAbsenRequest extends FormRequest
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

        }
        return $rules;
    }

    public function messages()
    {
        return [
            'id.required'           => 'ID tidak boleh kosong',
            'id.exists'             => 'ID tidak terdaftar',
        ];
    }
}
