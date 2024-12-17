<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;
use App\Models\MySql\Employee;
use App\Models\MySql\PotonganTrx;
use App\Models\MySql\StandbyDtl;

class PotonganMstResource extends JsonResource
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
            'id'            => $this->id,
            'kejadian'      => $this->kejadian,
            'employee'      => new EmployeeResource($this->whenLoaded('employee')),
            'employee_id'   => $this->employee_id,
            'no_pol'        => $this->no_pol,
            'nominal'       => $this->nominal,
            'nominal_cut'   => $this->nominal_cut,
            'remaining_cut' => $this->remaining_cut,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'status'        => $this->status,

            'val'           => $this->val,
            'val_user'      => $this->val_user ?? "",
            'val_by'        => new IsUserResource($this->whenLoaded('val_by')),
            'val_at'        => $this->val_at ?? "",

            'val1'          => $this->val1,
            'val1_user'     => $this->val1_user ?? "",
            'val1_by'       => new IsUserResource($this->whenLoaded('val1_by')),
            'val1_at'       => $this->val1_at ?? "",
            
            'trxs'          => PotonganTrxResource::collection($this->whenLoaded('trxs')),

            'attachment_1'          => "",
            'attachment_1_preview'  => $this->attachment_1 ? "data:".$this->attachment_1_type.";base64,".$this->attachment_1 : "",

            'attachment_2'          => "",
            'attachment_2_preview'  => $this->attachment_2 ? "data:".$this->attachment_2_type.";base64,".$this->attachment_2 : "",

        ];
    }
}
