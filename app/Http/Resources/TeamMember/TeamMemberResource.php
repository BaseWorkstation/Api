<?php

namespace App\Http\Resources\TeamMember;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PaymentMethod\PaymentMethodCollection;

class TeamMemberResource extends JsonResource
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
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'email' => $this->email,
            'verified_at' => $this->joinedTeams()
                                    ->wherePivot('team_id', $request->team_id)
                                    ->wherePivot('user_id', $this->id)
                                    ->get()->first()->pivot->verified_at,
            'deleted_at' => $this->joinedTeams()
                                    ->wherePivot('team_id', $request->team_id)
                                    ->wherePivot('user_id', $this->id)
                                    ->get()->first()->pivot->deleted_at,
            'last_active' => null,
            'payment_methods' => new PaymentMethodCollection($this->paymentMethods),
        ];
    }
}
