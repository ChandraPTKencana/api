<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;
use App\Models\MySql\SalaryPaid;

class SalaryBonusResource extends JsonResource
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
            'id'                    => $this->id,
            'tanggal'               => $this->tanggal,
            'type'                  => $this->type,
            'employee'              => new EmployeeResource($this->whenLoaded('employee')),
            'nominal'               => $this->nominal,
            'note'                  => $this->note ?? "",
            
            'val1'                  => $this->val1,
            'val1_user'             => $this->val1_user ?? "",
            'val1_by'               => new IsUserResource($this->whenLoaded('val1_by')),
            'val1_at'               => $this->val1_at ?? "",

            'val2'                  => $this->val2,
            'val2_user'             => $this->val2_user ?? "",
            'val2_by'               => new IsUserResource($this->whenLoaded('val2_by')),
            'val2_at'               => $this->val2_at ?? "",

            'val3'                  => $this->val3,
            'val3_user'             => $this->val3_user ?? "",
            'val3_by'               => new IsUserResource($this->whenLoaded('val3_by')),
            'val3_at'               => $this->val3_at ?? "",

            'created_user'          => $this->created_user,
            'updated_user'          => $this->updated_user,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'attachment_1'          => null,
            'attachment_1_preview'  => $this->attachment_1 ? "data:".$this->attachment_1_type.";base64,".$this->attachment_1 : "",
            'attachment_1_type'     => $this->attachment_1_type,
        ];
    }
}
