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
        'street',
        'city',
        'state',
        'country_iso',
        'country_name',
        'phone',
        'email',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Get the team's logo.
     */
    public function logo()
    {
        return $this->morphOne(File::class, 'fileable');
    }

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
                    ->withPivot('verified_at', 'deleted_at')
                    ->wherePivotNull('deleted_at');
    }

    /**
     * The members that were invited to the team but do not have base account.
     */
    public function unregisteredMembers()
    {
        return DB::table('unregistered_members_invites')
                            ->where([
                                'team_id' => $this->id,
                            ])
                            ->get();
    }

    /**
     * Get the plans that the team is subscribed to.
     
    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'team_plan_pivot', 'team_id', 'plan_id');
    }
    */

    /**
     * Get all of the team's payments.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    /**
     * Get all of the team's payment methods.
     
    public function paymentMethods()
    {
        return $this->morphMany(PaymentMethod::class, 'paymentMethodable');
    }
    */

    /**
     * Get all of the visits paid for by the team.
     */
    public function visitsPaidFor()
    {
        return $this->morphMany(Visit::class, 'paidByable');
    }

    /**
     * Get all of the payment methods paid by a team.
     */
    public function paymentMethodsPaidFor()
    {
        return $this->morphMany(PaymentMethod::class, 'paidByable');
    }
}
