<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'id'                    => $this->id,
            'order_id'              => $this->order_id,
            'client_id'             => $this->client_id,
            'client_name'           => optional($this->client)->name,
            'datetime'              => $this->datetime,
            'total_amount'          => $this->total_amount,
            'payment_type'          => $this->payment_type,
            'txn_id'                => $this->txn_id,
            'payment_status'        => $this->payment_status,
            'transaction_detail'    => $this->transaction_detail,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'deleted_at'            => $this->deleted_at,
        ];
    }
}