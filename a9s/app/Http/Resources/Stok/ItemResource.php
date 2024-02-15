<?php

namespace App\Http\Resources\Stok;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class ItemResource extends JsonResource
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
            'id'                  => $this->id,
            'name'                => $this->name,
            'value'               => $this->value ?? 0,
            'note'                => $this->note ?? '',
            'unit'                => new UnitResource($this->whenLoaded('unit')),
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'photo'               => $this->photo ? ("/ho/images/stok/item/".$this->photo) : null,
            // 'photo'               => $this->photo ? ("http://127.0.0.1/ho/images/stok/item/".$this->photo) : null,
            'updator'             => new IsUserResource($this->whenLoaded('updator')),
            'creator'             => new IsUserResource($this->whenLoaded('creator')),
        ];
    }
}
