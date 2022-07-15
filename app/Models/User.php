<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements Auditable
{
    use \OwenIt\Auditing\Auditable, HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * tells what default gaurd to use
     */
    protected $guard_name = 'web';

    /**
     * Get the user's avatar.
     */
    public function avatar()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    /**
     * Get the plan that the user is subscribed to.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * The workstations owned by the user.
     */
    public function ownedWorkstations()
    {
        return $this->belongsToMany(Workstation::class, 'workstation_owners_pivot', 'user_id', 'workstation_id');
    }

    /**
     * The teams owned by the user.
     */
    public function ownedTeams()
    {
        return $this->belongsToMany(Team::class, 'team_owners_pivot', 'user_id', 'team_id');
    }

    /**
     * The teams joined by the user.
     */
    public function joinedTeams()
    {
        return $this->belongsToMany(Team::class, 'team_members_pivot', 'user_id', 'team_id')
                    ->withPivot('verified_at', 'deleted_at');
    }

    /**
     * Get all of the user's payments.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    /**
     * Get all of the user's payment methods.
     */
    public function paymentMethods()
    {
        return $this->morphMany(PaymentMethod::class, 'paymentMethodable');
    }

    /**
     * Get all of the visits paid for by the user.
     */
    public function visitsPaidFor()
    {
        return $this->morphMany(Visit::class, 'paidByable');
    }

    /**
     * Get the check-in visits made by a user.
     */
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
