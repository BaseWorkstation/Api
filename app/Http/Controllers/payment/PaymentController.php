<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\PaymentRepository;
use Illuminate\Validation\Rule;
use App\Models\Payment;
use App\Models\PaymentMethod;

class PaymentController extends Controller
{
    /**
     * declaration of Payment repository
     *
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * Dependency Injection of PaymentRepository.
     *
     * @param  \App\Repositories\PaymentRepository  $paymentRepository
     * @return void
     */
    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\PaymentRepository
     */
    public function index(Request $request)
    {
        // run in the repository
        return $this->paymentRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\PaymentRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        // authorization
        $this->authorize('create', Payment::class);

        // validation
        $request->validate([
            'name' => 'required|max:255',
            'price_per_month' => 'required|integer',
            'currency_code' => ["required", Rule::in(config('enums.currency_code'))],
        ]);

        // run in the repository
        return $this->paymentRepository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \App\Http\Repositories\PaymentRepository
     */
    public function show($id)
    {
        // run in the repository
        return $this->paymentRepository->show($id);
    }

    /**
     * add payment method
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\PaymentRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addPaymentMethod(Request $request)
    {
        // authorization
        $this->authorize('create', PaymentMethod::class);

        // validation
        $request->validate([
            'paid_by_model' => ["required", Rule::in(config('enums.paidByable_type'))],
            'paid_by_id' => 'required|integer',
            'paid_for_model' => ["required", Rule::in(config('enums.paymentable_model'))],
            'paid_for_id' => 'required|integer',
            'method_type' => ["required", Rule::in(config('enums.payment_method_type'))],
            'plan_code' => 'required_if:method_type,plan',
            'card_number' => 'required_if:method_type,PAYG_card|integer',
            'card_name' => 'required_if:method_type,PAYG_card|string',
            'card_expiry_month' => 'required_if:method_type,PAYG_card|between:1,12',
            'card_expiry_year' => 'required_if:method_type,PAYG_card|date_format:Y-m-d',
            'card_cvc' => 'required_if:method_type,PAYG_card|integer',
        ]);

        // run in the repository
        return $this->paymentRepository->addPaymentMethod($request);
    }

    /**
     * get payment method
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Repositories\PaymentRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getPaymentMethods(Request $request)
    {
        // authorization
        $this->authorize('create', PaymentMethod::class);

        // validation
        $request->validate([
            'paid_for_model' => ["required", Rule::in(config('enums.paymentable_model'))],
            'paid_for_id' => 'required|integer',
        ]);

        // run in the repository
        return $this->paymentRepository->getPaymentMethods($request);
    }

    /**
     * delete payment method
     *
     * @param  int  $id
     * @return \App\Http\Repositories\PaymentRepository
     * 
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deletePaymentMethod($id)
    {
        // authorization
        $this->authorize('delete', PaymentMethod::find($id));

        // run in the repository
        return $this->paymentRepository->deletePaymentMethod($id);
    }
}
