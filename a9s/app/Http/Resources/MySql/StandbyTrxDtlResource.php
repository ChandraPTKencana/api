<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class StandbyTrxDtlResource extends JsonResource
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
            'ordinal'               => $this->ordinal,
            'tanggal'               => $this->tanggal,
            'waktu'                 => $this->waktu ?? "",
            'note'                  => $this->note ?? "",
            'be_paid'               => $this->be_paid,
            'attachment_1'          => null,
            'attachment_1_preview'  => $this->attachment_1 ? "data:".$this->attachment_1_type.";base64,".$this->attachment_1 : "",
            'attachment_1_type'     => $this->attachment_1_type,
            
        ];
    }
}
