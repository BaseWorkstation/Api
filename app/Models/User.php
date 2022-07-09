<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
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
     * The workstations owned by the user.
     */
    public function owned_workstations()
    {
        return $this->belongsToMany(Workstation::class, 'workstation_owners_pivot', 'user_id', 'workstation_id');
    }

    /**
     * The teams owned by the user.
     */
    public function owned_teams()
    {
        return $this->belongsToMany(Team::class, 'team_owners_pivot', 'user_id', 'team_id');
    }

    /**
     * The teams joined by the user.
     */
    public function joined_teams()
    {
        return $this->belongsToMany(Team::class, 'team_members_pivot', 'user_id', 'team_id')
                    ->withPivot('verified_at', 'deleted_at');
    }
}
