<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;
use App\Models\MySql\StandbyDtl;

class FinPaymentReqResource extends JsonResource
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
            'id'          => $this->id,
            'details'     => FinPaymentReqDtlResource::collection($this->whenLoaded('details')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,

            'val'         => $this->val,
            'val_user'    => $this->val_user ?? "",
            'val_by'      => new IsUserResource($this->whenLoaded('val_by')),
            'val_at'      => $this->val_at ?? "",
        ];
    }
}
