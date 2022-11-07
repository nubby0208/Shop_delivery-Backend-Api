<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class ExtraChargeResource extends JsonResource
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
            'id'            => $this->id,
            'title'         => $this->title,
            'charges_type'  => $this->charges_type,
            'charges'       => $this->charges,
            'country_id'    => $this->country_id,
            'country_name'  => optional($this->country)->name,
            'city_id'       => $this->city_id,
            'city_name'     => optional($this->city)->name,
            'status'        => $this->status,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'deleted_at'    => $this->deleted_at,
        ];
    }
}
