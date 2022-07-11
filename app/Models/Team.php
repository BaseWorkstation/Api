<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Team extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [];

    /**
     * The owners of the team.
     */
    public function owners()
    {
        return $this->belongsToMany(User::class, 'team_owners_pivot', 'team_id', 'user_id');
    }

    /**
     * The members of the team.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_members_pivot', 'team_id', 'user_id')
                    ->withPivot('verified_at', 'deleted_at');
    }

    /**
     * The members that were invited to the team but do not have base account.
     */
    public function unregistered_members()
    {
        return DB::table('unregistered_members_invites')
                            ->where([
                                'team_id' => $this->id,
                            ])
                            ->get();
    }

    /**
     * Get the plans that the team is subscribed to.
     */
    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'team_plan_pivot', 'team_id', 'plan_id');
    }
}
