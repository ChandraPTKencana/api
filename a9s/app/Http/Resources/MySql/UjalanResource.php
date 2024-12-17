<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class UjalanResource extends JsonResource
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
            'xto'               => $this->xto,
            'tipe'              => $this->tipe,
            'asst_opt'          => $this->asst_opt,
            'jenis'             => $this->jenis,
            'harga'             => $this->harga,
            'note_for_remarks'  => $this->note_for_remarks ?? '' ,
            'transition_from'   => $this->transition_from ?? '' ,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'created_user'      => $this->created_user,
            'updated_user'      => $this->updated_user,
            'details'           => UjalanDetailResource::collection($this->whenLoaded('details')),
            'details2'          => UjalanDetail2Resource::collection($this->whenLoaded('details2')),

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
