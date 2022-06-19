<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            "id" => $this->id,
            "code" => $this->code,
            "customer_name" => $this->customer_name,
            "customer_mobile" => $this->customer_mobile,
            "customer_address" => $this->customer_address,
            "province_name" => $this->province_name,
            "amphures_name" => $this->amphures_name,
            "district_name" => $this->district_name,
            "zip_code" => $this->zip_code,
            "car_id" => $this->car_id,
            "driver_name" => $this->driver_name,
            "driver_mobile" => $this->driver_mobile,
            "parcel_amount" => $this->parcel_amount,
            "parcel_total" => $this->parcel_total,
            "order_status" => $this->order_status,
            "created_by" => $this->created_by,
            "updated_by" => $this->updated_by,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "receivers" => OrderReceiverResource::collection($this->receivers)


        ];
    }
}
