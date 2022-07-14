<?php

namespace App\Repositories;

use App\Http\Resources\Payment\PaymentResource;
use App\Http\Resources\Payment\PaymentCollection;
use App\Http\Resources\PaymentMethod\PaymentMethodResource;
use App\Http\Resources\PaymentMethod\PaymentMethodCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

class PaymentRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Payment\PaymentCollection
     */
    public function index(Request $request)
    {
        // save request details in variables
        $keywords = $request->keywords;
        $request->from_date? 
            $from_date = $request->from_date."T00:00:00.000Z": 
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $request->to_date? 
            $to_date = $request->to_date."T23:59:59.000Z": 
            $to_date = Carbon::now();

        // fetch Payments from db using filters when they are available in the request
        $payments = Payment::when($keywords, function ($query, $keywords) {
                                        return $query->where("name", "like", "%{$keywords}%");
                                    })
                                    ->when($from_date, function ($query, $from_date) {
                                        return $query->whereDate('created_at', '>=', $from_date );
                                    })
                                    ->when($to_date, function ($query, $to_date) {
                                        return $query->whereDate('created_at', '<=', $to_date );
                                    })
                                    ->latest();

        // if user asks that the result be paginated
        if ($request->filled('paginate') && $request->paginate) {
            return new PaymentCollection($payments->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new PaymentCollection($payments->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Payment\PaymentResource
     */
    public function store(Request $request)
    {
        // persist request details and store in a variable
        $payment = Payment::firstOrCreate($request->all());

        // return resource
        return new PaymentResource($payment);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Payment\PaymentResource
     */
    public function show($id)
    {
        // return resource
        return new PaymentResource(Payment::findOrFail($id));
    }

    /**
     * find a specific Payment using ID.
     *
     * @param  int  $id
     * @return \App\Models\Payment
     */
    public function getPaymentById($id)
    {
        // find and return the instance
        return Payment::findOrFail($id);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\PaymentMethod\PaymentMethodCollection
     */
    public function getPaymentMethods(Request $request)
    {
        // get model instance (e.g. whether User or Team)
        if ($request->paymentable_model === "User") {
            $model = User::findOrFail($request->paymentable_id);
        }
        if ($request->paymentable_model === "Team") {
            $model = Team::findOrFail($request->paymentable_id);
        }

        // return payment method resource
        return new PaymentMethodCollection($model->paymentMethods);
    }

    /**
     * add payment method
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Resources\PaymentMethod\PaymentMethodCollection
     */
    public function addPaymentMethod(Request $request)
    {
        // get model instance (e.g. whether User or Team)
        if ($request->paymentable_model === "User") {
            $model = User::findOrFail($request->paymentable_id);
        }
        if ($request->paymentable_model === "Team") {
            $model = Team::findOrFail($request->paymentable_id);
        }

        // if model exists then proceed to save payment methods
        if ($model) {
            // save PAYG_card details
            if ($request->method_type == 'PAYG_card') {
                $payment_method = PaymentMethod::create([
                    "method_type" => $request->method_type,
                    "card_number" => $request->card_number,
                    "card_name" => $request->card_name,
                    "card_expiry_month" => Carbon::parse($request->card_expiry_month)->month,
                    "card_expiry_year" => Carbon::parse($request->card_expiry_year)->year,
                    "card_cvc" => $request->card_cvc,
                ]);
            }

            // save PAYG_cash details
            if ($request->method_type == 'PAYG_cash') {
                $payment_method = PaymentMethod::create([
                    "method_type" => $request->method_type,
                ]);
            }

            // save plan details
            if ($request->method_type == 'plan') {
                $payment_method = PaymentMethod::create([
                    "method_type" => $request->method_type,
                    "plan_id" => $request->plan_id,
                ]);
            }

            // attach payment methods relationship to the User or Team
            $model->paymentMethods()->save($payment_method);

            // return payment method resource
            return new PaymentMethodCollection($model->paymentMethods);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function deletePaymentMethod($id)
    {
        // softdelete instance
        PaymentMethod::findOrFail($id)->delete();
    }
}
