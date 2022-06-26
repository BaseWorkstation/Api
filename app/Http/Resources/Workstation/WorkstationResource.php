<?php

namespace App\Http\Resources\Workstation;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkstationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'country_iso' => $this->country_iso,
            'country_name' => $this->country_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'currency_code' => $this->currency_code,
        ];
    }
}
