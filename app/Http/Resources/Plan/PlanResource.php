<?php

namespace App\Http\Resources\Plan;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public $plan;

    /**
     * Create a new notification instance.
     *
     * @param  json  $plan
     * @return void
     */
    public function __construct($plan)
    {
        $this->plan = $plan;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->plan->id,
            'name' => $this->plan->name,
            'plan_code' => $this->plan->plan_code,
            'price_per_month' => $this->plan->amount/100,
            'currency_code' => 'NGN',
        ];
    }
}
