<?php

namespace App\Repositories;

use App\Http\Resources\Price\PriceResource;
use App\Http\Resources\Price\PriceCollection;
use Illuminate\Http\Request;
use App\Models\Price;
use App\Models\Service;
use App\Models\Workstation;
use Carbon\Carbon;

class PriceRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Price\PriceCollection
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

        // fetch Prices from db using filters when they are available in the request
        $prices = Price::when($keywords, function ($query, $keywords) {
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
            return new PriceCollection($prices->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new PriceCollection($prices->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Service $service
     * @return \Illuminate\Http\Resources\Price\PriceResource
     */
    public function store(Request $request, Service $service)
    {
        // fetch workstation
        $workstation = Workstation::findOrFail($service->workstation_id);

        // persist request details and store in a variable
        $price = Price::create([
            'amount' => $request->price,
            'service_id' => $service->id,
            'workstation_id' => $service->workstation_id,
            'retainer_id' => $workstation->first_fee_paying_retainer->id,
        ]);

        // return resource
        return new PriceResource($price);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Price\PriceResource
     */
    public function show($id)
    {
        // return resource
        return new PriceResource(Price::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Price\PriceResource
     */
    public function update(Request $request, $id)
    {
        // find the instance
        $price = $this->getPriceById($id);

        // filter the request of null values then update the instance
        $price->update(array_filter($request->all()));

        // return resource
        return new PriceResource($price);
    }

    /**
     * find a specific Price using ID.
     *
     * @param  int  $id
     * @return \App\Models\Price
     */
    public function getPriceById($id)
    {
        // find and return the instance
        return Price::findOrFail($id);
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
        $this->getPriceById($id)->delete();
    }
}
