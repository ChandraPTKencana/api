<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class SalaryPaidRequest extends FormRequest
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
            $rules['id'] = 'required|exists:App\Models\MySql\SalaryPaid,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\SalaryPaid,id';
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['period_end']    = 'required|date_format:Y-m';
            $rules['period_part']    = 'required|numeric|min:1|max:2';
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

            'period_part.required'        => 'Period Part tidak boleh kosong',
            'period_part.numeric'         => 'Period Part format harus berupa angka',
            'period_part.max'             => 'Period Part max tidak diterima',

        ];
    }
}
