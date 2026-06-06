<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderReceiverResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "order_id" => $this->order_id,
            "id" => $this->id,
            "parcel_code" => $this->parcel_code,
            "parcel_description" => $this->parcel_description,
            "receive_name" => $this->receive_name,
            "receive_mobile" => $this->receive_mobile,
            "receive_address" => $this->receive_address,
            "province_id" => $this->province_id,
            "amphures_id" => $this->amphures_id,
            "district_id" => $this->district_id,
            "province_name" => $this->province_name,
            "amphures_name" => $this->amphures_name,
            "district_name" => $this->district_name,
            "zip_code" => $this->zip_code,
            "parcel_pickup_type" => $this->parcel_pickup_type,
            "payment_type" => $this->payment_type,
            "delivery_status" => $this->delivery_status,
            "payment_status" => $this->payment_status,
            "parcel_pice" => $this->getParcelPriceValue(),
            "parcel_price" => $this->getParcelPriceValue(),
            "created_by" => $this->created_by,
            "updated_by" => $this->updated_by,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,


        ];
    }
}
