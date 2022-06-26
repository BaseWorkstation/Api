<?php

namespace App\Repositories\Interfaces;
use Illuminate\Http\Request;

interface UserInterface
{
    public function register(Request $request, $data);

    public function login($data);
}