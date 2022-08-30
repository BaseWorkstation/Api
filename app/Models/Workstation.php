<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Digikraaft\ReviewRating\Traits\HasReviewRating;
use Glorand\Model\Settings\Traits\HasSettingsTable;

class Workstation extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, Notifiable, SoftDeletes, HasReviewRating, HasSettingsTable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['price_per_minute'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['retainers'];

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
     * Get the workstation's logo.
     */
    public function logo()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    /**
     * Get the workstation's images.
     */
    public function images()
    {
        return $this->morphMany(File::class, 'fileable');
    }

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

    /**
     * Get the check-in visits made to a user.
     */
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * The amenities offered by a workstation.
     */
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_workstation_pivot', 'workstation_id', 'amenity_id');
    }

    /**
     * Default settings that each of this model instance should have.
     *
     * @var array
     */
    public $defaultSettings = [
        'schedule' => [
            'weekdays' => [
                'open_time' => null,
                'close_time' => null,
            ],
            'weekends' => [
                'open_time' => null,
                'close_time' => null,
            ],
        ],
    ];
}
