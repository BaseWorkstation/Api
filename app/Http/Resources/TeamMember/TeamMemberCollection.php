<?php

namespace App\Http\Resources\TeamMember;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Team;

class TeamMemberCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'unregistered_members' => Team::find($request->team_id)->unregisteredMembers()->all(),
        ];
    }
}
