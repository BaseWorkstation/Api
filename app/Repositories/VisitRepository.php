<?php

namespace App\Repositories;

use App\Http\Resources\Visit\VisitResource;
use App\Http\Resources\Visit\VisitCollection;
use App\Events\Visit\VisitCheckedOutEvent;
use App\Events\Visit\VisitCheckedInEvent;
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
        $team_id = $request->team_id;
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
                        ->when($team_id, function ($query, $team_id) {
                            return $query->where('team_id', $team_id);
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

            // call event that a visit has been checked in
            event(new VisitCheckedInEvent($request, $visit));

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
            $space_price_per_minute = $this->calculateServicePriceInMinutesForVisit($space, 1, $visit)['total_price'];
            $space_data_for_duration = $this->calculateServicePriceInMinutesForVisit($space, $duration_in_minutes, $visit);
            $space_price_for_duration_in_minutes = $space_data_for_duration['total_price'];
            $workspace_share_for_duration = $space_data_for_duration['workspace_share_for_duration'];
            $base_share_for_duration = $space_data_for_duration['base_share_for_duration'];

            // update other visit details
            $visit->check_out_time = Carbon::now();
            $visit->space_price_per_minute_at_the_time = $space_price_per_minute;
            $visit->currency_code = $visit->workstation->currency_code;
            $visit->total_minutes_spent = $duration_in_minutes;
            $visit->total_value_of_minutes_spent_in_naira = $space_price_for_duration_in_minutes;
            $visit->workspace_share_for_duration = $workspace_share_for_duration;
            $visit->base_share_for_duration = $base_share_for_duration;
            $visit->base_commission = $visit->workstation->base_commission;
            $visit->base_markup = $visit->workstation->base_markup;
            $visit->naira_rate_to_currency_at_the_time = DB::table('currency_value')->where('currency_code', $currency_code)->get()->first()->naira_value;
            $visit->otp = $this->generateOTP('visits', 'otp');
            $visit->save();

            $this->sendCodeTokenBecauseOldCodeNotWorking($user,$visit,$request);
            // // call event that a visit has been checked out
            event(new VisitCheckedOutEvent($request, $visit));

            // return resource
            return new VisitResource($visit);
        }

        return response(['error' => 'can not either find visit or user'], 401);
    }



    /**
     * @param mixed $user
     * @param mixed $visit
     * @param Request $request
     * @return [type]
     */
    public function sendCodeTokenBecauseOldCodeNotWorking($user, $visit, Request $request)
    {
        $curl = curl_init();
        $data = array("api_key" => env('TERMII_API_KEY'), "to" => $visit->workstation->phone,  "from" => "BASE",
        // "sms" => "Hi there, testing Termii line (ucfirst($user->first_name).' '.ucfirst($user->last_name).' is checking out of '.ucfirst($visit->workstation->name).'. Use OTP '. $visit['otp'].' to approve. ')",  "type" => "plain",  "channel" => "sms" );

        "sms" => $visit['otp'],  "type" => "plain",  "channel" => "generic" );

        $post_data = json_encode($data);
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.ng.termii.com/api/sms/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $post_data,
        CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;
    }
    /**
     * verify OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Visit\VisitResource
     */
    public function verifyOTP(Request $request)
    {
        // fetch visit
        $visit = Visit::findOrFail($request->visit_id);

        // check if visit's otp matches
        if ($visit->otp !== $request->otp) {
            return response(['error' => 'wrong otp'], 401);
        }

        $visit->otp_verified_at = Carbon::now();
        $visit->save();

        // return resource
        return new VisitResource($visit);
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
        /**
         * calculation of workspace_share_for_duration = naira rate of the currency * service price per minute * number of minutes
         */
        $base_commission_percentage = ($visit->workstation->base_commission * $service_price_per_minute)/100;
        $workspace_share_for_duration = ($service_price_per_minute - $base_commission_percentage) * $minutes;
        $base_share_per_minute = $base_commission_percentage + $visit->workstation->base_markup;
        $base_share_for_duration = $base_share_per_minute * $minutes;
        $total_price = $workspace_share_for_duration + $base_share_for_duration;

        $data['workspace_share_for_duration'] = $workspace_share_for_duration;
        $data['base_share_for_duration'] = $base_share_for_duration;
        $data['total_price'] = $total_price;

        return $data;
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
     * pay for visit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Visit\VisitResource
     */
    public function payment(Request $request)
    {
        $visit = Visit::findOrFail($request->visit_id);

        // if user wants to pay
        $user = User::findOrFail($visit->user_id);

        // if visit has been paid, return error
        if ($visit->paid_status == true) {
            return response(['error' => 'this visit has been paid for'], 401);
        }

        // via plan, check if the they really have an active plan
        if ($request->payment_method_type === 'plan') {
            $user_plan = $user->paymentMethods()->where('method_type', 'plan')->get()->first();
            if (!$user_plan) {
                return response(['error' => 'you do not have an active plan'], 401);
            }

            // make payment using plan
            $this->makePaymentForVisit($request, $visit);
        }

        // via PAYG_cash
        if ($request->payment_method_type === 'PAYG_cash') {
            // make payment using PAYG_cash
            $this->makePaymentForVisit($request, $visit);
        }

        // via PAYG_card
        if ($request->payment_method_type === 'PAYG_card') {
            // make payment using PAYG_card
            $this->makePaymentForVisit($request, $visit);
        }

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
        $user = User::findOrFail($visit->user_id);
        // update visit details
        $user->visitsPaidFor()->save($visit);

        // if user wants to pay via plan
        if ($request->payment_method_type === 'plan') {
            // confirm user really has a plan before proceeding
            $user_plan = $user->paymentMethods()->where('method_type', 'plan')->get()->first();
            if ($user_plan) {
                $visit->payment_method_type = 'plan';
                $visit->payment_method_id = $user_plan->id;
                $visit->paid_status = true;

                // if the user_plan is paid for by a team, then update team_id
                if ($user_plan->paidByable_type === 'App\Models\Team') {
                    $visit->team_id = $user_plan->paidByable_id;
                }
                $visit->save();
            }
        }

        // if user wants to pay via PAYG_cash
        if ($request->payment_method_type === 'PAYG_cash') {
            // confirm user really has a plan before proceeding
            $visit->payment_method_type = 'PAYG_cash';
            $visit->paid_status = true;
            $visit->save();
        }

        // if user wants to pay via PAYG_card
        if ($request->payment_method_type === 'PAYG_card') {
            // confirm user really has a plan before proceeding
            $visit->payment_method_type = 'PAYG_card';
            $visit->payment_reference = $request->payment_reference;
            $visit->paid_status = true;
            $visit->save();
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

    /**
     * generate OTP for a particular table column
     * @param  string  $table_name
     * @param  string  $column_name
     * @param  int  $number_of_string
     * @return array
     */
    public function generateOTP($table_name, $column_name, int $number_of_string = 4)
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
