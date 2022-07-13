<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PaymentMethod\PaymentMethodCollection;
use Auth;

class UserResource extends JsonResource
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
            'phone' => $this->phone,
            'pending_team_invites' => $this->mergeTeamInvites(),
            'payment_methods' => new PaymentMethodCollection($this->paymentMethods),
        ];
    }

    /**
     * merge both invites that were sent before and after user had authentication
     *
     * @return array
     */
    public function mergeTeamInvites()
    {
        $team_invites_after_auth = $this->joinedTeams()
                                        ->wherePivot('verified_at', null)
                                        ->wherePivot('user_id', $this->id)
                                        ->pluck('team_id')->all();

        $team_invites_before_auth = DB::table('unregistered_members_invites')
                                        ->where('email', $this->email)
                                        ->pluck('team_id')->all();

        return array_unique(array_merge($team_invites_before_auth, $team_invites_after_auth));
    }
}
