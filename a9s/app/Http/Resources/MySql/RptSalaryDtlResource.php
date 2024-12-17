<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class RptSalaryDtlResource extends JsonResource
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
            'employee'                  => new EmployeeResource($this->whenLoaded('employee')),
            'employee_id'               => $this->employee_id,
            'employee_name'             => $this->employee_name,
            'employee_role'             => $this->employee_role,
            'employee_birth_place'      => $this->employee_birth_place,
            'employee_birth_date'       => $this->employee_birth_date,
            'employee_tmk'              => $this->employee_tmk,
            'employee_ktp_no'           => $this->employee_ktp_no,
            'employee_address'          => $this->employee_address,
            'employee_status'           => $this->employee_status,
            'employee_rek_no'           => $this->employee_rek_no,
            'employee_rek_name'         => $this->employee_rek_name,
            'employee_bank_name'        => $this->employee_bank_name,
            'employee_bpjs_kesehatan'   => $this->employee_bpjs_kesehatan,
            'employee_bpjs_jamsos'      => $this->employee_bpjs_jamsos,
            // 'standby_nominal'       => $this->standby_nominal,
            'sb_gaji'                   => $this->sb_gaji,
            'sb_makan'                  => $this->sb_makan,
            'sb_dinas'                  => $this->sb_dinas,

            'sb_gaji_2'                 => $this->sb_gaji_2,
            'sb_makan_2'                => $this->sb_makan_2,
            'sb_dinas_2'                => $this->sb_dinas_2,

            'uj_gaji'                   => $this->uj_gaji,
            'uj_makan'                  => $this->uj_makan,
            'uj_dinas'                  => $this->uj_dinas,

            'nominal_cut'               => $this->nominal_cut,
            'salary_bonus_nominal'      => $this->salary_bonus_nominal,
            'salary_bonus_nominal_2'      => $this->salary_bonus_nominal_2,
            'kerajinan'                 => $this->kerajinan,
        ];
    }
}
