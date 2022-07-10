<?php

namespace App\Events\User;

use Illuminate\Http\Request;
use App\Models\User;

class NewUserCreatedEvent
{

    /**
     * Public declaration of variables.
     *
     * @var Request $request
     * @var  User $user
     */
    public $request;
    public $user;

    /**
     * Dependency Injection of variables
     *
     * @param Request $request
     * @param User $user
     * @return void
     */
    public function __construct(Request $request, User $user)
    {
        $this->request = $request;
        $this->user = $user;
    }
}