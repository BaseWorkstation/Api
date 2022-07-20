<?php

namespace App\Http\Resources\Team;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\File\FileResource;
use App\Http\Resources\PaymentMethod\PaymentMethodCollection;

class TeamResource extends JsonResource
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
            'logo' => new FileResource($this->logo),
            'payment_methods' => new PaymentMethodCollection($this->paymentMethods),
        ];
    }
}
