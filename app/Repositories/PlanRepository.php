<?php

namespace App\Repositories;

use App\Http\Resources\Plan\PlanResource;
use App\Http\Resources\Plan\PlanCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Plan;
use App\Models\Team;
use Carbon\Carbon;

class PlanRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Plan\PlanCollection
     */
    public function index(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
                                    CURLOPT_URL => "https://api.paystack.co/plan?status=active",
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => "",
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 30,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => "GET",
                                    CURLOPT_HTTPHEADER => array(
                                          "Authorization: Bearer sk_live_dc4085b3a907d7e2df602a2c2a894411922212a6",
                                          "Cache-Control: no-cache",
                                        ),
                                    )
                        );

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return json_decode("cURL Error #:" . $err);
        } else {
            return new PlanCollection(json_decode($response)->data);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $plan_code
     * @return \Illuminate\Http\Resources\Plan\PlanResource
     */
    public function show($plan_code)
    {
        $plan = json_decode($this->paystackPlanDetails($plan_code))->data;

        // return resource
        return new PlanResource($plan);
    }

    /**
     * get plan details from paystack.
     *
     * @param  string  $plan_code
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function paystackPlanDetails($plan_code)
    {
        $curl = curl_init();
  
        curl_setopt_array($curl, array(
                                    CURLOPT_URL => "https://api.paystack.co/plan/". $plan_code,
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => "",
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 30,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => "GET",
                                    CURLOPT_HTTPHEADER => array(
                                          "Authorization: Bearer sk_live_dc4085b3a907d7e2df602a2c2a894411922212a6",
                                          "Cache-Control: no-cache",
                                        ),
                                    )
                        );

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
}
