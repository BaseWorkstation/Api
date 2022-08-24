<?php

namespace App\Http\Controllers\visit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\VisitRepository;
use Illuminate\Validation\Rule;
use App\Models\Visit;

class VisitController extends Controller
{
    /**
     * declaration of Visit repository
     *
     * @var visitRepository
     */
    private $visitRepository;

    /**
     * Dependency Injection of VisitRepository.
     *
     * @param  \App\Repositories\VisitRepository  $visitRepository
     * @return void
     */
    public function __construct(VisitRepository $visitRepository)
    {
        $this->visitRepository = $visitRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\VisitRepository
     */
    public function index(Request $request)
    {
        // run in the repository
        return $this->visitRepository->index($request);
    }

    /**
     * check-in a new visit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\VisitRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function checkIn(Request $request)
    {
        // authorization
        //$this->authorize('checkIn', Visit::class);

        // validation
        $request->validate([
            'service_id' => 'required|integer',
            'unique_pin' => 'required|exists:users,unique_pin',
        ],[
            'unique_pin.exists' => 'wrong unique pin'
        ]);

        // run in the repository
        return $this->visitRepository->checkIn($request);
    }

    /**
     * check-out a new visit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\VisitRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function checkOut(Request $request)
    {
        // authorization
        //$this->authorize('checkOut', Visit::findOrFail($id));

        // validation
        $request->validate([
            'user_id' => 'required|integer',
            'unique_pin' => 'required',
        ]);

        // run in the repository
        return $this->visitRepository->checkOut($request);
    }

    /**
     * verify OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\VisitRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function verifyOTP(Request $request)
    {
        // authorization
        //$this->authorize('verifyOTP', Visit::findOrFail($id));

        // validation
        $request->validate([
            'visit_id' => 'required|integer',
            'otp' => 'required',
        ]);

        // run in the repository
        return $this->visitRepository->verifyOTP($request);
    }

    /**
     * pay for a visit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\VisitRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function payment(Request $request)
    {
        // validation
        $request->validate([
            'visit_id' => 'required|integer',
            //'payer' => ["required", Rule::in(config('enums.paidByable_type'))],
            'payment_method_type' => ["required_if:payer,User|integer", Rule::in(config('enums.payment_method_type'))],
            'payment_method_id' => 'required_if:paymentable_method_type,plan|integer',
            'team_id' => 'required_if:payer,Team|integer',
        ]);

        // run in the repository
        return $this->visitRepository->payment($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\VisitRepository
     */
    public function show($id)
    {
        // run in the repository
        return $this->visitRepository->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \App\Http\Repositories\VisitRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $id)
    {
        // authorization
        $this->authorize('update', Visit::findOrFail($id));

        // validation
        $request->validate([
            'name' => 'required|max:255',
            'price_per_month' => 'required|integer',
            'currency_code' => ["required", Rule::in(config('enums.currency_code'))],
        ]);

        // run in the repository
        return $this->visitRepository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\VisitRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        // authorization
        $this->authorize('delete', Visit::findOrFail($id));

        // run in the repository
        return $this->visitRepository->destroy($id);
    }
}
