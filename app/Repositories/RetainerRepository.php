<?php

namespace App\Repositories;

use App\Http\Resources\Retainer\RetainerResource;
use App\Http\Resources\Retainer\RetainerCollection;
use Illuminate\Http\Request;
use App\Models\Retainer;
use App\Models\Workstation;
use Carbon\Carbon;

class RetainerRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Retainer\RetainerCollection
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

        // fetch Retainers from db using filters when they are available in the request
        $retainers = Retainer::when($keywords, function ($query, $keywords) {
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
            return new RetainerCollection($retainers->paginate($request->paginate_per_page)->withPath('/'));
        }

        // return collection
        return new RetainerCollection($retainers->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Retainer\RetainerResource
     */
    public function store(Request $request)
    {
        // persist request details and store in a variable
        $retainer = Retainer::create([
            'name' => $request->name,
        ]);

        // return resource
        return new RetainerResource($retainer);
    }

    /**
     * Store a newly created resource in storage after a workstation is created
     *
     * @param  \Illuminate\Http\Request $request
     * @param  App\Models\Workstation $workstation
     * @return \Illuminate\Http\Resources\Retainer\RetainerResource
     */
    public function storeRetainerWhenWorkstationIsCreated(Request $request, Workstation $workstation)
    {
        // persist request details and store in a variable
        $retainer = Retainer::create([
            'name' => env('DEFAULT_RETAINER_NAME'),
            'category' => env('DEFAULT_RETAINER_CATEGORY'),
            'workstation_id' => $workstation->id,
        ]);

        // return resource
        return new RetainerResource($retainer);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Retainer\RetainerResource
     */
    public function show($id)
    {
        // return resource
        return new RetainerResource(Retainer::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\Retainer\RetainerResource
     */
    public function update(Request $request, $id)
    {
        // find the instance
        $retainer = $this->getRetainerById($id);

        // remove or filter null values from the request data then update the instance
        $retainer->update(array_filter($request->all()));

        // return resource
        return new RetainerResource($retainer);
    }

    /**
     * find a specific Retainer using ID.
     *
     * @param  int  $id
     * @return \App\Models\Retainer
     */
    public function getRetainerById($id)
    {
        // find and return the instance
        return Retainer::findOrFail($id);
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
        $this->getRetainerById($id)->delete();
    }
}
