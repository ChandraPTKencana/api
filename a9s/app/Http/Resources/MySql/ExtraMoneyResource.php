<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class ExtraMoneyResource extends JsonResource
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
            'xto'               => $this->xto ?? "",
            'jenis'             => $this->jenis ?? "",
            'transition_target' => $this->transition_target??'',
            'transition_type'   => $this->transition_type??'',

            'ac_account_id'     => $this->ac_account_id ?? "",
            'ac_account_code'   => $this->ac_account_code ?? "",
            'ac_account_name'   => $this->ac_account_name ?? "",
            'nominal'           => $this->nominal ?? "",
            'qty'               => $this->qty ?? "",
            'total'             => $this->qty * $this->nominal ?? "",
            'description'       => $this->description ?? "",
            
            'val1'              => $this->val1,
            'val1_user'         => $this->val1_user ?? "",
            'val1_by'           => new IsUserResource($this->whenLoaded('val1_by')),
            'val1_at'           => $this->val1_at ?? "",

            'val2'              => $this->val2,
            'val2_user'         => $this->val2_user ?? "",
            'val2_by'           => new IsUserResource($this->whenLoaded('val2_by')),
            'val2_at'           => $this->val2_at ?? "",
            
            'req_deleted'       => $this->req_deleted,
            'req_deleted_user'  => $this->req_deleted_user ?? "",
            'req_deleted_at'    => $this->req_deleted_at ?? "",
            'req_deleted_by'    => new IsUserResource($this->whenLoaded('req_deleted_by')),
            'req_deleted_reason'=> $this->req_deleted_reason ?? "",

            'deleted'           => $this->deleted,
            'deleted_user'      => $this->deleted_user ?? "",
            'deleted_at'        => $this->deleted_at ?? "",
            'deleted_by'        => new IsUserResource($this->whenLoaded('deleted_by')),
            'deleted_reason'    => $this->deleted_reason ?? "",

            'created_user'      => $this->created_user,
            'updated_user'      => $this->updated_user,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
