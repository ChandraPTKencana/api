<?php

namespace App\Http\Requests\Stok;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
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
            $rules['name'] = 'required|max:255|unique:App\Models\Stok\Item,name';
        }
        if (request()->isMethod('get')) {
            $rules['id'] = 'required|exists:App\Models\Stok\Item,id';
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Models\Stok\Item,id';
            $rules['name'] = 'required|max:255|unique:App\Models\Stok\Item,name,' . request()->id;
        }
        if (request()->isMethod('post') || request()->isMethod('put')) {
            $rules['value'] = 'required|numeric';
            $rules['unit_id'] = 'required|exists:App\Models\Stok\Unit,id';
            $rules['photo'] = 'nullable|image|mimes:jpeg,png|max:2048';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'id.required' => 'ID tidak boleh kosong',
            'id.exists' => 'ID tidak terdaftar',

            'name.required' => 'Nama Tidak boleh kosong',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter',
            'name.unique' => 'Nama sudah terdaftar',

            'value.required' => 'Nilai Tidak boleh kosong',
            'value.numeric' => 'Nilai harus berupa angka',

            'unit_id.required' => 'Unit harus di pilih',
            'unit_id.exists' => 'Unit tidak tersedia',

            'photo.image' => 'Jenis Foto Harus Berupa Gambar',
            'photo.mimes' => 'Tipe Foto harus jpeg ataupun png',
            'photo.max' => 'Foto Maksimal 2048kb',
        ];
    }
}
