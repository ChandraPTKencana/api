<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;
use App\Models\MySql\StandbyDtl;

class PermissionGroupUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $array = new IsUserResource($this->whenLoaded('user'));
        return $array;

        // // return parent::toArray($request);
        // return [
        //     'user_id'          => $this->user_id,
        // ];
    }
}
