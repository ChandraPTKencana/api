<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;
use App\Models\MySql\PotonganMst;

class PotonganTrxResource extends JsonResource
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
            'id'            => $this->id,
            'trx_trp_id'    => $this->trx_trp_id ?? '',
            'nominal_cut'   => $this->nominal_cut,
            'note'          => $this->note ?? '',
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,

            'val'           => $this->val,
            'val_user'      => $this->val_user ?? "",
            'val_by'        => new IsUserResource($this->whenLoaded('val_by')),
            'val_at'        => $this->val_at ?? "",

            'val1'          => $this->val1,
            'val1_user'     => $this->val1_user ?? "",
            'val1_by'       => new IsUserResource($this->whenLoaded('val1_by')),
            'val1_at'       => $this->val1_at ?? "",
            'potongan_mst'  => new PotonganMstResource($this->whenLoaded('potongan_mst')),

            'deleted'       => $this->deleted,
            'deleted_by'    => new IsUserResource($this->whenLoaded('deleted_by')),
            'deleted_reason'=> $this->deleted_reason ?? "",
            'deleted_at'    => $this->deleted_at ?? "",
        ];
    }
}
