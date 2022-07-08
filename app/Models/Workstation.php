<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workstation extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['retainers'];

    /**
     * Get the services for the workstation.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the retainers for the workstation.
     */
    public function retainers()
    {
        return $this->hasMany(Retainer::class);
    }

    /**
     * Get first fee-paying retainer of the workstation
     */
    public function getFirstFeePayingRetainerAttribute()
    {
        return $this->retainers->where('category', 'fee-paying')->first();
    }

    /**
     * The owners of the workstation.
     */
    public function owners()
    {
        return $this->belongsToMany(User::class, 'workstation_owners_pivot', 'workstation_id', 'user_id');
    }
}
