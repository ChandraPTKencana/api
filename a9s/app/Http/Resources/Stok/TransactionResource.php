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
            'item'              => new ItemResource($this->whenLoaded('item')),

            'qty_in'            => $this->qty_in,
            'qty_out'           => $this->qty_out,
            'qty_reminder'      => $this->qty_reminder,
            'note'              => $this->note,
            'status'            => $this->status,
            'type'              => $this->type,

            'requested_at'      => $this->requested_at,
            'approved_at'       => $this->approved_at,
            'requester'         => new IsUserResource($this->whenLoaded('requester')),
            'approver'          => new IsUserResource($this->whenLoaded('approver')),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'ref_id'            => $this->ref_id,
        ];
    }
}
