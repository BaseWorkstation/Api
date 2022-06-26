<?php

namespace App\Repositories;

use App\Repositories\Interfaces\UserInterface;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use App\Models\User;

class UserRepository implements UserInterface
{
    /**
     * logic to register a user
     *
     * @param Request $request
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request, $data)
    {
        // encrypt password
        $data['password'] = bcrypt($request->password);

        // strip some variables to lowercase
        $request->first_name? $data['first_name'] = strtolower($data['first_name']): null ;
        $request->last_name? $data['last_name'] = strtolower($data['last_name']): null ;

        // persist new user into db
        $user = User::create($data);

        // generate token
        $token = $user->createToken('API Token')->accessToken;

        // return response
        return response([ 'user' => $user, 'token' => $token]);
    }

    /**
     * logic to login a user
     *
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    public function login($data)
    {
        if (!auth()->attempt($data)) {
            return response(['error_message' => 'Incorrect Details. Please try again']);
        }

        $token = auth()->user()->createToken('API Token')->accessToken;

        return response(['user' => auth()->user(), 'token' => $token]);
    }
}
