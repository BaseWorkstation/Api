<?php

namespace App\Http\Resources\PaymentMethod;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Plan\PlanResource;

class PaymentMethodResource extends JsonResource
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
            'method' => $this->method_type,
            'card_name' => $this->when($this->method_type === 'PAYG_card', $this->card_name),
            'card_number' => $this->when($this->method_type === 'PAYG_card', $this->card_number),
            'card_expiry' => $this->when($this->method_type === 'PAYG_card', $this->card_expiry),
            'plan' => $this->when($this->method_type === 'plan', new PlanResource($this->plan)),
        ];
    }
}
