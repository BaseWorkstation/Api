<?php

namespace App\Http\Resources\Service;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Price\PriceResource;

class ServiceResource extends JsonResource
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
            'category' => $this->category,
            'price_per_minute' => new PriceResource($this->prices->first()),
            'price_per_hour' => [
                                    'amount' => $this->prices->first()->amount * 60,
                                    'retainer_category' => $this->prices->first()->retainer->category,
                                ],
        ];
    }
}
