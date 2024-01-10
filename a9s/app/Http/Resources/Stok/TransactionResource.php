<?php

namespace App\Http\Resources\Stok;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

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
            'note'              => $this->note,
            'status'            => $this->status,
            'type'              => $this->type,
            'requested_at'      => $this->requested_at,
            'confirmed_at'       => $this->confirmed_at,
            'requester'         => new IsUserResource($this->whenLoaded('requester')),
            'confirmer'          => new IsUserResource($this->whenLoaded('confirmer')),
            'confirmed_by'       => $this->confirmed_by,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'ref_id'            => $this->ref_id,
        ];
    }
}
