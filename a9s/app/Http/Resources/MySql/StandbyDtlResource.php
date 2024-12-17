<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class StandbyDtlResource extends JsonResource
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
            'ordinal'           => $this->ordinal,
            'ac_account_id'     => $this->ac_account_id,
            'ac_account_code'   => $this->ac_account_code,
            'ac_account_name'   => $this->ac_account_name,
            'amount'            => $this->amount,
            'description'       => $this->description,
            'xfor'              => $this->xfor ?? "",
        ];
    }
}
