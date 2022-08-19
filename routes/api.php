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
use App\Http\Controllers\workstationReview\WorkstationReviewController;

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
        Route::get('/user/get-by-unique-pin',[UserController::class, 'getUserByUniquePin']);
        Route::post('/forgot-password',[UserController::class, 'sendPasswordResetLink'])->name('password.email');
        Route::post('/forgot-pin',[UserController::class, 'sendPin']);
        Route::post('/reset-password',[UserController::class, 'resetPassword'])->name('password.reset');

        // some apiResource routes for workstation that do not require authentication
        Route::apiResource('workstations', WorkstationController::class)->only(['index', 'show']);

        // routes prefixed with "workstations/{id}" e.g. /workstations/1/reviews
        Route::prefix('workstations/{workstation_id}')->group(function () {
            Route::apiResource('reviews', WorkstationReviewController::class)->only(['index']);
        });

        // some apiResource routes for service that do not require authentication
        Route::apiResource('services', ServiceController::class)->only(['index']);

        // other visit routes
        Route::post('/visits/check-in',[VisitController::class, 'checkIn']);
        Route::post('/visits/check-out',[VisitController::class, 'checkOut']);

        // routes that require authentication
        Route::group([
                'middleware' => 'auth:api',
            ], function () {

                // change password in user profile
                Route::post('/change-password',[UserController::class, 'changePassword']);

                // routes prefixed with "teams" e.g. /teams/members
                Route::prefix('teams')->group(function () {
                    Route::delete('/members',[TeamMemberController::class, 'remove']);

                    Route::apiResources([
                        'members' => TeamMemberController::class,
                    ]);
                });

                // routes prefixed with "workstations/{id}" e.g. /workstations/1/reviews
                Route::prefix('workstations/{workstation_id}')->group(function () {
                    Route::delete('/reviews/{review_id}',[WorkstationReviewController::class, 'remove']);

                    Route::apiResource('reviews', WorkstationReviewController::class)->only(['store']);
                });

                // other payment routes
                Route::get('/payments/methods',[PaymentController::class, 'getPaymentMethods']);
                Route::post('/payments/methods',[PaymentController::class, 'addPaymentMethod']);
                Route::delete('/payments/methods/{id}',[PaymentController::class, 'deletePaymentMethod']);

                // standard apiResource routes
                Route::apiResources([
                    'users' => UserController::class,
                    'plans' => PlanController::class,
                    'files' => FileController::class,
                    'visits' => VisitController::class,
                    'teams' => TeamController::class,
                ]);

                // some apiResource routes for workstation that require authentication
                Route::apiResource('workstations', WorkstationController::class)->only(['store', 'update', 'destroy']);

                // some apiResource routes for service that require authentication
                Route::apiResource('services', ServiceController::class)->only(['store', 'show', 'update', 'destroy']);
        });

});


