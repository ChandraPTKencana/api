<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;
use App\Models\MySql\StandbyDtl;

class StandbyMstResource extends JsonResource
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
            'name'              => $this->name,
            'tipe'              => $this->tipe,
            'amount'            => $this->amount,
            'details'           => StandbyDtlResource::collection($this->whenLoaded('details')),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'is_transition'     => $this->is_transition ? 1 : 0,
            'is_trip'           => $this->is_trip ? 1 : 0,
            'driver_asst_opt'   => $this->driver_asst_opt,

            'val'               => $this->val,
            'val_user'          => $this->val_user ?? "",
            'val_by'            => new IsUserResource($this->whenLoaded('val_by')),
            'val_at'            => $this->val_at ?? "",

            'val1'              => $this->val1,
            'val1_user'         => $this->val1_user ?? "",
            'val1_by'           => new IsUserResource($this->whenLoaded('val1_by')),
            'val1_at'           => $this->val1_at ?? "",

            'deleted'           => $this->deleted,
            'deleted_user'      => $this->deleted_user ?? "",
            'deleted_at'        => $this->deleted_at ?? "",
            'deleted_by'        => new IsUserResource($this->whenLoaded('deleted_by')),
            'deleted_reason'    => $this->deleted_reason ?? "",
        ];
    }
}
