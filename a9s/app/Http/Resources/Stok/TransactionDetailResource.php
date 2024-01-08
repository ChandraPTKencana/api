<?php

namespace App\Http\Resources\Stok;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class TransactionDetailResource extends JsonResource
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
            'item'              => new ItemResource($this->whenLoaded('item')),
            'item_id'           => $this->st_item_id,
            'qty_in'            => $this->qty_in,
            'qty_out'           => $this->qty_out,
            'qty_reminder'      => $this->qty_reminder,
            'note'              => $this->note,
        ];
    }
}
