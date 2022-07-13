<?php

namespace App\Policies;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentMethodPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, PaymentMethod $paymentMethod)
    {
        // if authenticated user is the same person that created the model instance
        return $user->id === $paymentMethod->audits->first()->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, PaymentMethod $paymentMethod)
    {
        //
    }
}
