<?php

namespace App\Repositories;

use App\Http\Resources\Service\ServiceResource;
use App\Http\Resources\Service\ServiceCollection;
use App\Events\Service\NewServiceCreatedEvent;
use Illuminate\Http\Request;
use App\Models\Service;
use Carbon\Carbon;

class ServiceRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Service\ServiceCollection
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

        // fetch Services from db using filters when they are available in the request
        $services = Service::when($keywords, function ($query, $keywords) {
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
            return new ServiceCollection($services->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new ServiceCollection($services->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Service\ServiceResource
     */
    public function store(Request $request)
    {
        // persist request details and store in a variable
        $service = Service::create($request->all());

        // call event that a new service has been created
        event(new NewServiceCreatedEvent($request, $service));

        // return resource
        return new ServiceResource($service);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Service\ServiceResource
     */
    public function show($id)
    {
        // return resource
        return new ServiceResource(Service::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Service\ServiceResource
     */
    public function update(Request $request, $id)
    {
        // find the instance
        $service = $this->getServiceById($id);

        // filter the request of null values then update the instance
        $service->update(array_filter($request->all()));

        // return resource
        return new ServiceResource($service);
    }

    /**
     * find a specific Service using ID.
     *
     * @param  int  $id
     * @return \App\Models\Service
     */
    public function getServiceById($id)
    {
        // find and return the instance
        return Service::findOrFail($id);
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
        $this->getServiceById($id)->delete();
    }
}
