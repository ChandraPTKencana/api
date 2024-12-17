<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;
use App\Models\MySql\StandbyDtl;

class FinPaymentReqDtlResource extends JsonResource
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
        $array                          = new TrxTrpForFinPaymentReqResource($this->whenLoaded('r_trx_trp'));
        $array['fin_payment_req_id']    = $this->fin_payment_req_id;
        $array['trx_trp_id']            = $this->trx_trp_id;
        return $array;
    }
}
