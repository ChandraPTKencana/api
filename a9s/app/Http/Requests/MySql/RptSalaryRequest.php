<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class RptSalaryRequest extends FormRequest
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
            $rules['id'] = 'required|exists:App\Models\MySql\RptSalary,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\RptSalary,id';
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['period_end']    = 'required|date_format:Y-m';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            
            'id.required'                 => 'ID tidak boleh kosong',
            'id.exists'                   => 'ID tidak terdaftar',

            'period_end.required'         => 'Period End tidak boleh kosong',
            'period_end.date_format'      => 'Period End format tanggal tidak cocok',

        ];
    }
}
