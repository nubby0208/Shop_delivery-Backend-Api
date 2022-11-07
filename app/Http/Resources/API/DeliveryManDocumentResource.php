<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryManDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'delivery_man_id'       => $this->delivery_man_id,
            'document_id'           => $this->document_id,
            'document_name'         => optional($this->document)->name,
            'delivery_man_name'     => optional($this->delivery_man)->name,
            'is_verified'           => $this->is_verified,
            'delivery_man_document' => getSingleMedia($this, 'delivery_man_document',null),
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'deleted_at'            => $this->deleted_at,
        ];
    }
}