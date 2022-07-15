<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * declaration of user repository
     *
     * @var userRepository
     */
    private $userRepository;

    /**
     * Dependency Injection of userRepository.
     *
     * @param  \App\Repositories\UserRepository $userRepository
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user.
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Repositories\UserRepository
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'last_name' => 'required|max:255',
            'first_name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required|confirmed'
        ]);

        return $this->userRepository->register($request, $data);
    }

    /**
     * Login user.
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Repositories\UserRepository
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        return $this->userRepository->login($data);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\UserRepository
     */
    public function index(Request $request)
    {
        // run in the repository
        return $this->userRepository->index($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\UserRepository
     */
    public function show($id)
    {
        // run in the repository
        return $this->userRepository->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Repositories\UserRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $id)
    {
        // authorization
        $this->authorize('update', User::find($id));

        // validation
        $request->validate([
            'last_name' => 'sometimes|max:255',
            'first_name' => 'sometimes|max:255',
            'phone' => ['sometimes', Rule::unique('users')->ignore($id)]
        ]);

        // run in the repository
        return $this->userRepository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\UserRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        // authorization
        $this->authorize('delete', User::find($id));

        // run in the repository
        return $this->userRepository->destroy($id);
    }

    /**
     * get user using Token
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Repositories\UserRepository
     */
    public function getUserByToken(Request $request)
    {
        return $this->userRepository->getUserByToken($request);
    }

    /**
     * send password-reset link
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Repositories\UserRepository
     */
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        return $this->userRepository->sendPasswordResetLink($request);
    }

    /**
     * reset password
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Repositories\UserRepository
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        return $this->userRepository->resetPassword($request);
    }
}
