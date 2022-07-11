<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workstation;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkstationPolicy
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
     * @param  \App\Models\Workstation  $workstation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Workstation $workstation)
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
     * @param  \App\Models\Workstation  $workstation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Workstation $workstation)
    {
        // if authenticated user is one of the owners OR if authenticated user has role of base_admin
        return (in_array($user->id, $workstation->owners->pluck('id')->all())) || $user->hasRole('base_admin');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Workstation  $workstation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Workstation $workstation)
    {
        // if authenticated user is one of the owners OR if authenticated user has role of base_admin
        return (in_array($user->id, $workstation->owners->pluck('id')->all())) || $user->hasRole('base_admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Workstation  $workstation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Workstation $workstation)
    {
        // if authenticated user is one of the owners OR if authenticated user has role of base_admin
        return (in_array($user->id, $workstation->owners->pluck('id')->all())) || $user->hasRole('base_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Workstation  $workstation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Workstation $workstation)
    {
        return $user->hasRole('base_admin');
    }
}
