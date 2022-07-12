<?php

namespace App\Repositories;

use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use Illuminate\Http\Request;
use App\Events\User\NewUserCreatedEvent;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

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
            return response(['error_message' => 'Incorrect Details. Please try again'], 401);
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

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\User\UserCollection
     */
    public function index(Request $request)
    {
        // save request details in variables
        $keywords = $request->keywords;
        $request->from_date? 
            $from_date = $request->from_date."T00:00:00.000Z": 
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $request->to_date? 
            $to_date = $request->to_date."T23:59:59.000Z": 
            $to_date = Carbon::now();

        // fetch users from db using filters when they are available in the request
        $users = User::when($keywords, function ($query, $keywords) {
                                        return $query->where("first_name", "like", "%{$keywords}%")
                                                    ->orWhere("last_name", "like", "%{$keywords}%");
                                    })
                                    ->when($from_date, function ($query, $from_date) {
                                        return $query->whereDate('created_at', '>=', $from_date );
                                    })
                                    ->when($to_date, function ($query, $to_date) {
                                        return $query->whereDate('created_at', '<=', $to_date );
                                    })
                                    ->latest();

        // if user asks that the result be paginated
        if ($request->filled('paginate') && $request->paginate) {
            return new UserCollection($users->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new UserCollection($users->get());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\User\UserResource
     */
    public function show($id)
    {
        // return resource
        return new UserResource(User::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\User\UserResource
     */
    public function update(Request $request, $id)
    {
        // find the instance
        $user = $this->getUserById($id);

        // remove or filter null values from the request data then update the instance
        $user->update(array_filter($request->all()));

        // return resource
        return new UserResource($user);
    }

    /**
     * find a specific user using ID.
     *
     * @param  int  $id
     * @return \App\Models\User
     */
    public function getUserById($id)
    {
        // find and return the instance
        return User::findOrFail($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function destroy($id)
    {
        // softdelete instance
        $this->getUserById($id)->delete();
    }
}
