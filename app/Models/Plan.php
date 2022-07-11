<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Plan extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'price_per_month', 'price_currency'
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [];

    /**
     * The user that have subscribed to a Plan.
     */
    public function userSubscribers()
    {
        return $this->hasMany(User::class);
    }

    /**
     * The teams that have subscribed to a Plan.
     */
    public function teamSubscribers()
    {
        return $this->belongsToMany(Team::class, 'team_plan_pivot', 'plan_id', 'team_id');
    }

    /**
     * Get the services for the plan.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
