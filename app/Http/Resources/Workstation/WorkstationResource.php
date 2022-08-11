<?php

namespace App\Http\Resources\Workstation;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Retainer\RetainerResource;
use App\Http\Resources\Service\ServiceResource;
use App\Http\Resources\File\FileResource;
use App\Http\Resources\File\FileCollection;
use App\Http\Resources\Amenity\AmenityCollection;

class WorkstationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'street' => $this->street,
            'city' => $this->city,
            'state' => $this->state,
            'country_iso' => $this->country_iso,
            'country_name' => $this->country_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'currency_code' => $this->currency_code,
            'bank_details' => $this->bank_details,
            //'retainers' => RetainerResource::collection($this->retainers),
            'default_service' => new ServiceResource($this->services->first()),
            'logo' => new FileResource($this->logo),
            'images' => new FileCollection($this->images),
            'qr_code_path' => $this->qr_code_path,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'about' => $this->about,
            'other_policies' => $this->other_policies,
            'coordinates' => $this->coordinates,
            'amenities' => new AmenityCollection($this->amenities),
            'schedule' => $this->settings()->get()['schedule'],
        ];
    }
}
