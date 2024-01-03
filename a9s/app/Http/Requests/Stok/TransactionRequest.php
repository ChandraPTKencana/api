<?php

namespace App\Http\Requests\Stok;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            // $rules['name'] = 'required|max:255|unique:App\Models\Stok\Item,name';
            $rules['warehouse_id'] = 'required|exists:App\Models\HrmRevisiLokasi,id';
            $rules['item_id'] = 'required|exists:App\Models\Stok\Item,id';
            $rules['qty_in'] = 'required_if:qty_out,null|nullable|numeric';
            $rules['qty_out'] = 'required_if:qty_in,null|nullable|numeric';
            $rules['type'] = 'required|in:transfer,used,in';
            $rules['warehouse_target_id'] = 'required_if:type,transfer|nullable|exists:App\Models\HrmRevisiLokasi,id';
    }
        if (request()->isMethod('get')) {
            $rules['id'] = 'required|exists:App\Models\Stok\Item,id';
        }
        // if (request()->isMethod('put')) {
        //     $rules['id'] = 'required|exists:App\Models\Stok\Item,id';
        //     $rules['name'] = 'required|max:255|unique:App\Models\Stok\Item,name,' . request()->id;
        // }
        // if (request()->isMethod('post') || request()->isMethod('put')) {
        //     $rules['value'] = 'required|numeric';
        //     $rules['unit_id'] = 'required|exists:App\Models\Stok\Unit,id';
        // }
        return $rules;
    }

    public function messages()
    {
        return [
            'warehouse_id.required' => 'Lokasi tidak boleh kosong',
            'warehouse_id.exists' => 'Lokasi tidak terdaftar',

            'item_id.required' => 'Item tidak boleh kosong',
            'item_id.exists' => 'Item tidak terdaftar',

            'qty_in.required_if' => 'Qty Tidak boleh kosong',
            'qty_in.numeric' => 'Qty harus angka',

            'qty_out.required_if' => 'Qty Tidak boleh kosong',
            'qty_out.numeric' => 'Qty harus angka',

            'type.required' => 'Type Tidak boleh kosong',
            'type.in' => 'Format Type salah',

            'warehouse_target_id.required_if' => 'Lokasi Target harus di pilih',
            'warehouse_target_id.exists' => 'Lokasi Target tidak tersedia',
        ];
    }
}
