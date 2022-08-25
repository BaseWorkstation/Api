<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PaymentMethod extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'method_type', 
        'plan_id',
        'plan_code',
        'payment_reference',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Get the parent paymentmethodable model (e.g user).
     */
    public function paymentMethodable()
    {
        return $this->morphTo();
    }

    /**
     * Get whoever paid for the payment method (e.g user or Team).
     */
    public function paidByable()
    {
        return $this->morphTo();
    }

    /**
     * Get the plan that the PaymentMethod belongs to.
     
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }*/
}
