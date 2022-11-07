<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
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
            'id'                => $this->id,
            'name'              => $this->name,
            'address'           => $this->address,
            'country_id'        => $this->country_id,
            'country_name'      => optional($this->country)->name,
            'country'           => $this->country,
            'status'            => $this->status,
            'fixed_charges'     => $this->fixed_charges,
            'extra_charges'     => $this->extraChargesActive,
            'cancel_charges'    => $this->cancel_charges,
            'min_distance'      => $this->min_distance,
            'min_weight'        => $this->min_weight,
            'per_distance_charges' => $this->per_distance_charges,
            'per_weight_charges' => $this->per_weight_charges,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
            'deleted_at'         => $this->deleted_at,
        ];
    }
}
