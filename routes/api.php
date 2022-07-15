<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user\UserController;
use App\Http\Controllers\workstation\WorkstationController;
use App\Http\Controllers\service\ServiceController;
use App\Http\Controllers\team\TeamController;
use App\Http\Controllers\visit\VisitController;
use App\Http\Controllers\plan\PlanController;
use App\Http\Controllers\file\FileController;
use App\Http\Controllers\payment\PaymentController;
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

        // routes that do not require authentication
        Route::post('/register',[UserController::class, 'register']);
        Route::post('/login',[UserController::class, 'login']);
        Route::get('/user',[UserController::class, 'getUserByToken']);
        Route::post('/forgot-password',[UserController::class, 'sendPasswordResetLink'])->name('password.email');
        Route::post('/reset-password',[UserController::class, 'resetPassword'])->name('password.reset');

        // routes that require authentication
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

                // other payment routes
                Route::get('/payments/methods',[PaymentController::class, 'getPaymentMethods']);
                Route::post('/payments/methods',[PaymentController::class, 'addPaymentMethod']);
                Route::delete('/payments/methods/{id}',[PaymentController::class, 'deletePaymentMethod']);

                // other visit routes
                Route::post('/visits/check-in',[VisitController::class, 'checkIn']);
                Route::patch('/visits/{id}/check-out',[VisitController::class, 'checkOut']);

                // standard apiResource routes
                Route::apiResources([
                    'users' => UserController::class,
                    'workstations' => WorkstationController::class,
                    'plans' => PlanController::class,
                    'files' => FileController::class,
                    'visits' => VisitController::class,
                    'services' => ServiceController::class,
                    'teams' => TeamController::class,
                ]);
        });

});


