<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Http\Request;

class UserSeeder extends Seeder
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
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create new request
        $request = new Request([
                "last_name" => "Oyeniyi",
                "first_name" => "Gbenga",
                "email" => "oyeniyiemmanuel@gmail.com",
                "phone" => "08062680306",
                "password" => "pass",
                "password_confirmation" => "pass",
            ]);

        // validated data
        $data = $request->validate([
            'last_name' => 'required|max:255',
            'first_name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required|confirmed'
        ]);

        // call method to register user 
        $result = $this->userRepository->register($request, $data);

        // get newly created user
        $user = User::find(json_decode($result->getContent())->user->id);

        // assign admin role to user
        //$user->assignRole('admin');
    }
}
