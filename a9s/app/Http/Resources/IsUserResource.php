<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IsUserResource extends JsonResource
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
            'id_user'             => $this->id_user,
            'username'            => $this->username,
            'nama_user'           => $this->nama_user,
        ];
    }
}
