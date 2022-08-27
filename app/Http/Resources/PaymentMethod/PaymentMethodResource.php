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
        // fetch plan details from paystack api
        $plan = json_decode($this->paystackPlanDetails($this->plan_code))->data;

        // format returned response
        return [
            'id' => $this->id,
            'method' => $this->method_type,
            'payment_reference' => $this->payment_reference,
            'plan' => $this->when(($this->method_type === 'plan') && ($this->plan_code !== null), $this->internalPlanResource($plan)),
        ];
    }

    /**
     * get plan details from paystack.
     *
     * @param  json  $plan
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function internalPlanResource($plan)
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'plan_code' => $plan->plan_code,
            'price_per_month' => $plan->amount/100,
            'currency_code' => 'NGN',
        ];
    }

    /**
     * get plan details from paystack.
     *
     * @param  string  $plan_code
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function paystackPlanDetails($plan_code)
    {
        $curl = curl_init();
  
        curl_setopt_array($curl, array(
                                    CURLOPT_URL => "https://api.paystack.co/plan/". $plan_code,
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => "",
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 30,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => "GET",
                                    CURLOPT_HTTPHEADER => array(
                                          "Authorization: Bearer sk_live_dc4085b3a907d7e2df602a2c2a894411922212a6",
                                          "Cache-Control: no-cache",
                                        ),
                                    )
                        );

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
}
