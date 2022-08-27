<?php

namespace App\Http\Resources\Visit;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Service\ServiceCollection;
use App\Models\User;

class VisitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // create token for user that owns the visit
        $token = User::findOrFail($this->user_id)->createToken('API Token')->accessToken;

        return [
            'id' => $this->id,
            'user' => [
                        'id' => $this->user_id,
                        'last_name' => $this->user->last_name,
                        'first_name' => $this->user->first_name,
                    ],
            'token' => $this->when($request->getRequestUri() === '/api/visits/check-in', $token), // return only when user is checking in
            'workstation' => [
                                'id' => $this->workstation_id,
                                'name' => $this->workstation->name,
                                'street' => $this->workstation->street,
                                'city' => $this->workstation->city,
                                'state' => $this->workstation->state,
                            ],
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'currency_code' => $this->currency_code,
            'payment_method_type' => $this->payment_method_type,
            'payment_reference' => $this->payment_reference,
            'paid_status' => $this->paid_status,
            'naira_rate_to_currency_at_the_time' => (int) $this->naira_rate_to_currency_at_the_time,
            'space_price_per_minute_at_the_time' => (int) $this->space_price_per_minute_at_the_time,
            'total_minutes_spent' => (int) $this->total_minutes_spent,
            'workspace_share_for_duration' => $this->workspace_share_for_duration,
            'total_value_of_minutes_spent_in_naira' => $this->total_value_of_minutes_spent_in_naira,
            'otp_verified_at' => $this->otp_verified_at,
            'services' => new ServiceCollection($this->services),
        ];
    }
}
