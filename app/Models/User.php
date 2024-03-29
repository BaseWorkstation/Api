<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * Interact with the workstation's phone.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => str_replace( '+234' , '0'  , $value ), // remove +234 after fetching from db
            set: fn ($value) => '+234' . substr($value, 1), // append with +234 before saving to db
        );
    }

    /**
     * Route notifications for the Vonage channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForVonage($notification)
    {
        return $this->phone;
    }

    /**
     * Route notifications for the Termii channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForTermii()
    {
        return $this->phone;
    }

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
        return $this->belongsToMany(Team::class, 'team_owners_pivot', 'user_id', 'team_id')
                    ->wherePivotNull('deleted_at');
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
     * Get all of the payment methods paid by a user.
     */
    public function paymentMethodsPaidFor()
    {
        return $this->morphMany(PaymentMethod::class, 'paidByable');
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

    /**
     * the user's visit checked-in status.
     *
     */
    public function checkInStatus()
    {
        $last_visit = $this->visits()->latest()->get()->first();

        if ($last_visit && $last_visit->check_out_time === null) {
            return true;
        }

        return false;
    }

    /**
     * the user's current visit.
     *
     */
    public function currentVisit()
    {
        $last_visit = $this->visits()->latest()->get()->first();

        if ($last_visit && $last_visit->check_out_time === null) {
            return $last_visit;
        }

        return null;
    }
}
