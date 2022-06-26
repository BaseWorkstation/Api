<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retainer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Get the workstation that the retainer belongs to.
     */
    public function workstation()
    {
        return $this->belongsTo(Workstation::class);
    }

    /**
     * Get the prices for the retainer.
     */
    public function prices()
    {
        return $this->hasMany(Price::class);
    }
}
