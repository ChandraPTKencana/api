<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class UjalanDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'ordinal'     => $this->ordinal,
            'xdesc'       => $this->xdesc,
            'qty'         => $this->qty,
            'harga'       => $this->harga,
            'for_remarks' => $this->for_remarks ? 1 : 0,
        ];
    }
}
