<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Price extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Get the workstation that owns the price.
     */
    public function workstation()
    {
        return $this->belongsTo(Workstation::class);
    }

    /**
     * Get the service that owns the price.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the retainer that owns the price.
     */
    public function retainer()
    {
        return $this->belongsTo(Retainer::class);
    }
}
