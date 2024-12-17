<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class TrxTrpForFinPaymentReqResource extends JsonResource
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
            'id'                => $this->id,
            'tanggal'           => $this->tanggal,
            'amount'            => $this->amount,

            'id_uj'             => $this->id_uj,
            'pv_id'             => $this->pv_id ?? "",
            'pv_no'             => $this->pv_no ?? "",
            'pv_total'          => $this->pv_total ?? "",
            'pv_datetime'       => $this->pv_datetime ?? "",

            'supir'             => $this->supir,
            'kernet'            => $this->kernet ?? "",
            'no_pol'            => $this->no_pol,

            'created_user'      => $this->created_user,
            'updated_user'      => $this->updated_user,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,

            'pvr_id'            => $this->pvr_id ?? "",
            'pvr_no'            => $this->pvr_no ?? "",
            'pvr_total'         => $this->pvr_total ?? "",
            'pvr_had_detail'    => $this->pvr_had_detail ?? "",
            'transition_target' => $this->transition_target ?? "",
            'trx_absens'        => TrxAbsenResource::collection($this->whenLoaded('trx_absens')),

            'details_uj'        => UjalanDetailResource::collection($this->whenLoaded('uj_details')),
        ];
    }
}
