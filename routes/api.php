<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user\UserController;
use App\Http\Controllers\workstation\WorkstationController;
use App\Http\Controllers\service\ServiceController;
use App\Http\Controllers\team\TeamController;
use App\Http\Controllers\teamMember\TeamMemberController;

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

Route::group([
        'middleware' => 'xssSanitizer',
    ], function () {

        Route::post('/register',[UserController::class, 'register']);
        Route::post('/login',[UserController::class, 'login']);
        Route::get('/user',[UserController::class, 'getUserByToken']);

        Route::group([
                'middleware' => 'auth:api',
            ], function () {

                // routes prefixed with "teams" e.g. /teams/members
                Route::prefix('teams')->group(function () {
                    Route::delete('/members',[TeamMemberController::class, 'remove']);

                    Route::apiResources([
                        'members' => TeamMemberController::class,
                    ]);
                });

                Route::apiResources([
                    'users' => UserController::class,
                    'workstations' => WorkstationController::class,
                    'plans' => PlanController::class,
                    'services' => ServiceController::class,
                    'teams' => TeamController::class,
                ]);
        });

});


