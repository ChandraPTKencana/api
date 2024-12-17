<?php

namespace App\Http\Resources\MySql;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\IsUserResource;

class SalaryPaidDtlResource extends JsonResource
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
            'employee_id'           => $this->employee_id,
            'employee'              => new EmployeeResource($this->whenLoaded('employee')),
            // 'standby_nominal'       => $this->standby_nominal,
            'sb_gaji'               => $this->sb_gaji,
            'sb_makan'              => $this->sb_makan,
            'sb_dinas'              => $this->sb_dinas,
            'salary_bonus_nominal'  => $this->salary_bonus_nominal,
        ];
    }
}
