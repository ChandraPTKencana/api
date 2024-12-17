<?php

namespace App\Http\Resources\Stok;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MySql\IsUserResource;

class TransactionResource extends JsonResource
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
            'warehouse'         => new \App\Http\Resources\HrmRevisiLokasiResource($this->whenLoaded('warehouse')),
            'warehouse_source'  => new \App\Http\Resources\HrmRevisiLokasiResource($this->whenLoaded('warehouse_source')),
            'warehouse_target'  => new \App\Http\Resources\HrmRevisiLokasiResource($this->whenLoaded('warehouse_target')),
            'details'           => TransactionDetailResource::collection($this->whenLoaded('details')),
            'note'              => $this->note ?? "",
            'status'            => $this->status,
            'type'              => $this->type,
            'requested_at'      => $this->requested_at,
            'confirmed_at'      => $this->confirmed_at,
            'requested_by'      => new IsUserResource($this->whenLoaded('requested_by')),
            'confirmed_by'      => new IsUserResource($this->whenLoaded('confirmed_by')),
            'confirmed_user'    => $this->confirmed_user,
            'requested_user'    => $this->requested_user,
            'updated_at'        => $this->updated_at,
            'input_at'          => $this->input_at,
            'input_ordinal'     => $this->input_ordinal,
            'ref_id'            => $this->ref_id,
        ];
    }
}
