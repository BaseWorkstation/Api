<?php

namespace App\Repositories;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use App\Events\User\NewUserCreatedEvent;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserRepository
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
        $user->unique_pin = $this->generateUniquePin('users', 'unique_pin');
        $user->save();

        // generate token
        $token = $user->createToken('API Token')->accessToken;

        // call event that a new user has been created
        event(new NewUserCreatedEvent($request, $user));

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

    /**
     * Get User using token
     *
     * @param  Request $request
     * @return App\Models\User
     */
    public function getUserByToken(Request $request)
    {
        return auth('api')->user();
    }

    /**
     * generate unique numbers for a particular table column
     * @param  string  $table_name
     * @param  string  $column_name
     * @param  int  $number_of_string
     * @return array
     */
    public function generateUniquePin($table_name, $column_name, int $number_of_string = 4)
    {
        while (true) {
            $pool = '0123456789';
            $random_string = strtolower(substr(str_shuffle(str_repeat($pool, 5)), 0, $number_of_string));
            $check_if_code_exist = DB::table($table_name)
            ->where($column_name, $random_string)
            ->count();

            if (!$check_if_code_exist) {
                return $random_string;
            }
        }
    }
}
