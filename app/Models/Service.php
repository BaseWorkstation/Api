<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'category',
        'workstation_id',
        'plan_id',
    ];

    /**
     * Get the workstation that the service belongs to.
     */
    public function workstation()
    {
        return $this->belongsTo(Workstation::class);
    }

    /**
     * Get the plan that the service belongs to.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the prices for the service.
     */
    public function prices()
    {
        return $this->hasMany(Price::class);
    }
}
