<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user\UserController;
use App\Http\Controllers\workstation\WorkstationController;
use App\Http\Controllers\service\ServiceController;
use App\Http\Controllers\team\TeamController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$router->post('/register',[UserController::class, 'register']);
$router->post('/login',[UserController::class, 'login']);
$router->get('/user',[UserController::class, 'getUserByToken']);

Route::group([
        'middleware' => 'auth:api'
    ], function () {

        Route::apiResources([
            'workstations' => WorkstationController::class,
            'services' => ServiceController::class,
            'teams' => TeamController::class,
        ]);

});
