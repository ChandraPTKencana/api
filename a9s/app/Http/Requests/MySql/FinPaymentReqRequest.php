<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class FinPaymentReqRequest extends FormRequest
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
            $rules['id'] = 'required|exists:App\Models\MySql\FinPaymentReq,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\FinPaymentReq,id';
        }
        // if (request()->isMethod('post') || request()->isMethod('put')) {
        //     $rules['xto'] = 'required|max:50';
        //     $rules['tipe'] = 'required|max:50';
        //     $rules['jenis'] = 'required|in:CPO,TBS,PK';
        // }
        return $rules;
    }

    public function messages()
    {
        return [

            'id.required' => 'ID tidak boleh kosong',
            'id.exists' => 'ID tidak terdaftar',

            // 'xto.required' => 'To tidak boleh kosong',
            // 'xto.max' => 'To Maksimal 50 Karakter',

            // 'tipe.required' => 'Tipe tidak boleh kosong',
            // 'tipe.max' => 'Tipe Maksimal 50 Karakter',

            // 'jenis.required' => 'Jenis tidak boleh kosong',
            // 'jenis.in' => 'Jenis Harap Dipilih',

        ];
    }

    protected function prepareForValidation()
    {
        // $this->merge([
        //     'xto' => strtoupper($this->xto),
        //     'tipe' => strtoupper($this->tipe),
        //     // 'title' => fix_typos($this->title),
        //     // 'body' => filter_malicious_content($this->body),
        //     // 'tags' => convert_comma_separated_values_to_array($this->tags),
        //     // 'is_published' => (bool) $this->is_published,
        // ]);
    }
}
