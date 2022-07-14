<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisitPolicy
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
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Visit $visit)
    {
        //
    }

    /**
     * Determine whether the user can check-in a visit.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function checkIn(User $user)
    {
        return $user;
    }

    /**
     * Determine whether the user can check-out a visit.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function checkOut(User $user, Visit $visit)
    {
        // if authenticated user is the same person that check-in
        return $user->id === $visit->audits->first()->user_id;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Visit $visit)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Visit $visit)
    {
        // if authenticated user is a base admin
        return $user->hasRole('base_admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Visit $visit)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Visit $visit)
    {
        //
    }
}
