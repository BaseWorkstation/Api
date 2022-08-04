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
                            ],
            'check_in_time' => $this->check_in_time,
            'services' => new ServiceCollection($this->services),
        ];
    }
}
