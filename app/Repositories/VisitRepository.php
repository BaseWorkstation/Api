<?php

namespace App\Repositories;

use App\Http\Resources\Visit\VisitResource;
use App\Http\Resources\Visit\VisitCollection;
use App\Events\Visit\VisitCheckedOutEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Visit;
use App\Models\Team;
use App\Models\User;
use App\Models\Service;
use App\Models\Workstation;
use Carbon\Carbon;

class VisitRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Visit\VisitCollection
     */
    public function index(Request $request)
    {
        // save request details in variables
        $user_id = $request->user_id;
        $workstation_id = $request->workstation_id;
        $request->from_date? 
            $from_date = $request->from_date."T00:00:00.000Z": 
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $request->to_date? 
            $to_date = $request->to_date."T23:59:59.000Z": 
            $to_date = Carbon::now();

        // fetch Visits from db using filters when they are available in the request
        $visits = Visit::when($from_date, function ($query, $from_date) {
                            return $query->whereDate('created_at', '>=', $from_date );
                        })
                        ->when($to_date, function ($query, $to_date) {
                            return $query->whereDate('created_at', '<=', $to_date );
                        })
                        ->when($user_id, function ($query, $user_id) {
                            return $query->where('user_id', $user_id);
                        })
                        ->when($workstation_id, function ($query, $workstation_id) {
                            return $query->where('workstation_id', $workstation_id);
                        })
                        ->latest();

        // if user asks that the result be paginated
        if ($request->filled('paginate') && $request->paginate) {
            return new VisitCollection($visits->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new VisitCollection($visits->get());
    }

    /**
     * check-in a new visit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Visit\VisitResource
     */
    public function checkIn(Request $request)
    {
        // fetch user
        $user = User::where('unique_pin', $request->unique_pin)->get()->first();
        // fetch service
        $service = Service::findOrFail($request->service_id);
        // fetch workstation
        $workstation = $service->workstation;

        // if user has previously checked-in but didn't check out, return 401
        $visit = Visit::where('user_id', $user->id)->whereNull('check_out_time')->latest()->get()->first();
        if ($visit) {
            return response(['error' => 'you still have a visit that is not checked out yet'], 401);
        }

        // check if user and workstation exists
        if ($user && $workstation) {
            // persist request details and store in a variable
            $visit = Visit::firstOrCreate([
                "user_id" => $user->id,
                "workstation_id" => $workstation->id,
                "check_in_time" => Carbon::now(),
            ]);

            // add service to visit
            $visit->services()->syncWithoutDetaching($service->id);

            // return resource
            return new VisitResource($visit);
        }

        return response(['error' => 'can not verify user'], 401);
    }

    /**
     * check-out a visit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Visit\VisitResource
     */
    public function checkOut(Request $request)
    {
        // fetch user
        $user = User::findOrFail($request->user_id);
        // check if user's pin matches
        if ($user->unique_pin !== $request->unique_pin) {
            return response(['error' => 'wrong pin'], 401);
        }

        // if user does not have previously checked-in visit, return 401
        $visit = Visit::where('user_id', $user->id)
                        //->whereNull('check_out_time')
                        ->latest()->get()->first();
        if (!$visit) {
            return response(['error' => 'you do not have a checked-in visit'], 401);
        }

        // if user wants to pay using team plan, check if the team has an active plan
        if ($request->payer === 'Team') {
            $team = Team::findOrFail($request->team_id);
            $team_plan = $team->paymentMethods()->where('method_type', 'plan')->get()->first();
            if (!$team_plan) {
                return response(['error' => 'the team selected does not have an active team plan'], 401);
            }
        }

        // if user wants to pay
        if ($request->payer === 'User') {
            $user = User::findOrFail($request->user_id);

            // via plan, check if the they really have an active plan
            if ($request->payment_method_type === 'plan') {
                $user_plan = $user->paymentMethods()->where('method_type', 'plan')->get()->first();
                if (!$user_plan) {
                    return response(['error' => 'you do not have an active plan'], 401);
                }
            }
        }

        // check if user and visit exists before proceeding
        if ($user && $visit) {
            // update visit check out time
            $visit->check_out_time = Carbon::now();
            $visit->save();
            $visit->refresh();

            // find duration in minutes
            $start_time = Carbon::parse($visit->check_in_time);
            $end_time = Carbon::parse($visit->check_out_time);
            $duration_in_minutes = $end_time->diffInMinutes($start_time);

            // get other variables needed to update visit details
            $currency_code = $visit->workstation->currency_code;
            $space = $visit->services()->where('category', 'space')->get()->first();
            $space_price_per_minute = $this->calculateServicePriceInMinutesForVisit($space, 1, $visit);
            $space_price_for_duration_in_minutes = $this->calculateServicePriceInMinutesForVisit($space, $duration_in_minutes, $visit);

            // update other visit details
            $visit->check_out_time = Carbon::now();
            $visit->space_price_per_minute_at_the_time = $space_price_per_minute;
            $visit->currency_code = $visit->workstation->currency_code;
            $visit->total_minutes_spent = $duration_in_minutes;
            $visit->total_value_of_minutes_spent_in_naira = $space_price_for_duration_in_minutes;
            $visit->naira_rate_to_currency_at_the_time = DB::table('currency_value')->where('currency_code', $currency_code)->get()->first()->naira_value;
            $visit->save();

            // call event that a visit has been checked out
            event(new VisitCheckedOutEvent($request, $visit));

            // return resource
            return new VisitResource($visit);
        }

        return response(['error' => 'can not either find visit or user'], 401);
    }

    /**
     * calculate the price of a service per minute in a visit.
     *
     * @param  Service  $service
     * @param  int  $minutes
     * @param  Visit  $visit
     * @return int
     */
    public function calculateServicePriceInMinutesForVisit(Service $service, int $minutes, Visit $visit)
    {
        $service_price_per_minute = $service->prices->first()->amount;
        $currency_code = $visit->workstation->currency_code;
        $naira_rate = DB::table('currency_value')->where('currency_code', $currency_code)->get()->first()->naira_value;
        // price = naira rate of the currency * service price per minute * number of minutes
        $price = $naira_rate * $service_price_per_minute * $minutes;
        return $price;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Visit\VisitResource
     */
    public function show($id)
    {
        // return resource
        return new VisitResource(Visit::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Visit\VisitResource
     */
    public function update(Request $request, $id)
    {
        // find the instance
        $visit = $this->getVisitById($id);

        // remove or filter null values from the request data then update the instance
        $visit->update(array_filter($request->all()));

        // return resource
        return new VisitResource($visit);
    }

    /**
     * make payment for a visit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Visit  $visit
     * @return \Illuminate\Http\Resources\Visit\VisitResource
     */
    public function makePaymentForVisit(Request $request, Visit $visit)
    {
        // set who the payer is
        if ($request->payer === 'User') {
            $user = User::findOrFail($request->user_id);
            // update visit details
            $user->visitsPaidFor()->save($visit);

            // if user wants to pay via plan
            if ($request->payment_method_type === 'plan') {
                // confirm user really has a plan before proceeding
                $user_plan = $user->paymentMethods()->where('method_type', 'plan')->get()->first();
                if ($user_plan) {
                    $visit->payment_method_type = 'plan';
                    $visit->payment_method_id = $user_plan->id;
                    $visit->save();
                }
            }
        }
        if ($request->payer === 'Team') {
            $team = Team::findOrFail($request->team_id);
            // update visit details
            $team->visitsPaidFor()->save($visit);

            // if team plans exist, then set the payment_method
            $team_plan = $team->paymentMethods()->where('method_type', 'plan')->get()->first();
            if ($team_plan) {
                $visit->payment_method_type = 'plan';
                $visit->payment_method_id = $team_plan->id;
                $visit->save();
            }
        }

        

        // return resource
        return new VisitResource($visit);
    }

    /**
     * find a specific Visit using ID.
     *
     * @param  int  $id
     * @return \App\Models\Visit
     */
    public function getVisitById($id)
    {
        // find and return the instance
        return Visit::findOrFail($id);
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
        $this->getVisitById($id)->delete();
    }
}
