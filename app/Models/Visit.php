<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visit extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', 
        'workstation_id',
        'check_in_time',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Get the parent paidByable model (e.g user or team).
     */
    public function paidByable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that checked-in on a visit.
     */
    public function user()
    {
        return $this->belongsTo(user::class);
    }

    /**
     * Get the workstation that checked-in was done in.
     */
    public function workstation()
    {
        return $this->belongsTo(Workstation::class);
    }

    /**
     * The services that were attached to a visit.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_visit_pivot', 'visit_id', 'service_id')
                    ->wherePivotNull('deleted_at');
    }
}
