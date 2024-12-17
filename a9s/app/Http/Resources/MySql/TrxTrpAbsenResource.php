<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class TrxTrpAbsenResource extends JsonResource
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

            'id_uj'             => $this->id_uj,
            'jenis'             => $this->jenis,
            'xto'               => $this->xto,
            'tipe'              => $this->tipe,

            'supir_id'          => $this->supir_id ?? "",
            'supir'             => $this->supir,
            'supir_rek_no'      => $this->supir_rek_no ?? "",
            'supir_rek_name'    => $this->supir_rek_name ?? "",
            'kernet_id'         => $this->kernet_id ?? "",
            'kernet'            => $this->kernet ?? "",
            'kernet_rek_no'     => $this->kernet_rek_no ?? "",
            'kernet_rek_name'   => $this->kernet_rek_name ?? "",
            'no_pol'            => $this->no_pol,
            
            'created_user'      => $this->created_user,
            'updated_user'      => $this->updated_user,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,

            'deleted'           => $this->deleted,
            'deleted_user'      => $this->deleted_user ?? "",
            'deleted_at'        => $this->deleted_at ?? "",
            'deleted_by'        => new IsUserResource($this->whenLoaded('deleted_by')),
            'deleted_reason'    => $this->deleted_reason ?? "",

            'req_deleted'       => $this->req_deleted,
            'req_deleted_user'  => $this->req_deleted_user ?? "",
            'req_deleted_at'    => $this->req_deleted_at ?? "",
            'req_deleted_by'    => new IsUserResource($this->whenLoaded('req_deleted_by')),
            'req_deleted_reason'=> $this->req_deleted_reason ?? "",

            'transition_target' => $this->transition_target ?? "",
            'transition_type'   => $this->transition_type ?? "",
            'trx_absens'        => TrxAbsenResource::collection($this->whenLoaded('trx_absens')),

            'ritase_leave_at'   => $this->ritase_leave_at ?? "",
            'ritase_arrive_at'  => $this->ritase_arrive_at ?? "",
            'ritase_return_at'  => $this->ritase_return_at ?? "",
            'ritase_till_at'    => $this->ritase_till_at ?? "",
            'ritase_note'       => $this->ritase_note ?? "",
            
            'ritase_val'        => $this->ritase_val,
            'ritase_val_user'   => $this->ritase_val_user ?? "",
            'ritase_val_by'     => new IsUserResource($this->whenLoaded('ritase_val_by')),
            'ritase_val_at'     => $this->ritase_val_at ?? "",

            'ritase_val1'       => $this->ritase_val1,
            'ritase_val1_user'  => $this->ritase_val1_user ?? "",
            'ritase_val1_by'    => new IsUserResource($this->whenLoaded('ritase_val1_by')),
            'ritase_val1_at'    => $this->ritase_val1_at ?? "",

            'ritase_val2'       => $this->ritase_val2,
            'ritase_val2_user'  => $this->ritase_val2_user ?? "",
            'ritase_val2_by'    => new IsUserResource($this->whenLoaded('ritase_val2_by')),
            'ritase_val2_at'    => $this->ritase_val2_at ?? "",

            'uj'                => new UjalanResource($this->whenLoaded('uj')),

        ];
    }
}
