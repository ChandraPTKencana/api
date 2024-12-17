<?php

namespace App\Http\Requests\MySql;

use Illuminate\Foundation\Http\FormRequest;

class PermissionGroupRequest extends FormRequest
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
            $rules['id'] = 'required|exists:App\Models\MySql\PermissionGroup,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\MySql\PermissionGroup,id';
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['name'] = 'required|max:30';
        }
        return $rules;
    }

    public function messages()
    {
        return [

            'id.required' => 'ID tidak boleh kosong',
            'id.exists' => 'ID tidak terdaftar',

            'name.required' => 'Name tidak boleh kosong',
            'name.max' => 'Name Maksimal 30 Karakter',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => strtoupper($this->name),
        ]);
    }
}
