<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class StandbyTrxResource extends JsonResource
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

            'transition_target' => $this->transition_target??'',
            'transition_type'   => $this->transition_type??'',
            
            'standby_mst_id'    => $this->standby_mst_id,
            // 'standby_mst_name'  => $this->standby_mst_name,
            // 'standby_mst_type'  => $this->standby_mst_type,
            // 'standby_mst_amount'=> $this->standby_mst_amount,
            'standby_mst_'       => new StandbyMstResource($this->whenLoaded('standby_mst')),


            'supir_id'          => $this->supir_id ?? "",
            'supir'             => $this->supir,
            'supir_rek_no'      => $this->supir_rek_no ?? "",
            'supir_rek_name'    => $this->supir_rek_name ?? "",
            'kernet_id'         => $this->kernet_id ?? "",
            'kernet'            => $this->kernet ?? "",
            'kernet_rek_no'     => $this->kernet_rek_no ?? "",
            'kernet_rek_name'   => $this->kernet_rek_name ?? "",
            'no_pol'            => $this->no_pol ?? "",
            
            'xto'               => $this->xto ?? "",
            'note_for_remarks'  => $this->note_for_remarks ?? '',
            'ref'               => $this->ref ?? '',
            'trx_trp_id'        => $this->trx_trp_id ?? '',

            'cost_center_code'  => $this->cost_center_code ?? "",
            'cost_center_desc'  => $this->cost_center_desc ?? "",

            'pvr_id'            => $this->pvr_id ?? "",
            'pvr_no'            => $this->pvr_no ?? "",
            'pvr_total'         => $this->pvr_total ?? "",
            'pvr_had_detail'    => $this->pvr_had_detail ?? "",

            'pv_id'             => $this->pv_id ?? "",
            'pv_no'             => $this->pv_no ?? "",
            'pv_total'          => $this->pv_total ?? "",
            'pv_datetime'       => $this->pv_datetime ?? "",

            'rv_id'            => $this->rv_id ?? "",
            'rv_no'            => $this->rv_no ?? "",
            'rv_total'         => $this->rv_total ?? "",
            'rv_had_detail'    => $this->rv_had_detail ?? "",

            'val'               => $this->val,
            'val_user'          => $this->val_user ?? "",
            'val_by'            => new IsUserResource($this->whenLoaded('val_by')),
            'val_at'            => $this->val_at ?? "",
            
            'val1'              => $this->val1,
            'val1_user'         => $this->val1_user ?? "",
            'val1_by'           => new IsUserResource($this->whenLoaded('val1_by')),
            'val1_at'           => $this->val1_at ?? "",

            'val2'              => $this->val2,
            'val2_user'         => $this->val2_user ?? "",
            'val2_by'           => new IsUserResource($this->whenLoaded('val2_by')),
            'val2_at'           => $this->val2_at ?? "",

            'deleted'           => $this->deleted,
            'deleted_user'      => $this->deleted_user ?? "",
            'deleted_at'        => $this->deleted_at ?? "",
            'deleted_by'        => new IsUserResource($this->whenLoaded('deleted_by')),
            'deleted_reason'    => $this->deleted_reason ?? "",

            'req_deleted'       => $this->req_deleted,
            'req_deleted_user'  => $this->req_deleted_user ?? "",
            'req_deleted_at'    => $this->req_deleted_at ?? "",
            'req_deleted_by'    => new IsUserResource($this->whenLoaded('req_deleted_by')),
            'req_deleted_reason'=> $this->req_deleted_reason ?? "",

            'created_user'      => $this->created_user,
            'updated_user'      => $this->updated_user,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'details'           => StandbyTrxDtlResource::collection($this->whenLoaded('details')),
            'details_count'     => $this->details_count,

            'salary_paid_id'    => $this->salary_paid_id,
            'salary_paid'       => new SalaryPaidResource($this->whenLoaded('salary_paid')),
        ];
    }
}
