<?php

namespace App\Http\Resources\Stok;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MySql\IsUserResource;

class UnitResource extends JsonResource
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

            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'created_by'          => new IsUserResource($this->whenLoaded('created_by')),
            'updated_by'          => new IsUserResource($this->whenLoaded('updated_by')),
        ];
    }
}
