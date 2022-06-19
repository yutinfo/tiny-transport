<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderReceive;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->has('_token')){

            $param = $request->only(
                [
                     "db_date"
                ]
            );
            $start_data = Carbon::parse($param['db_date'])->startOfDay()->format('Y-m-d H:i:s');
            $end_data = Carbon::parse($param['db_date'])->endOfDay()->format('Y-m-d H:i:s');
            $order_model = Order::with('receivers')
                ->whereBetween('created_at', [$start_data, $end_data])
                ->orderByDesc('id')
                ->get()
                ->toArray();
        }else{
            $start_data = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
            $end_data = Carbon::now()->format('Y-m-d H:i:s');
            $order_model = Order::with('receivers')
                ->whereBetween('created_at', [$start_data, $end_data])
                ->orderByDesc('id')
                ->get()
                ->toArray();
        }


        $order_model = $this->wrapDataIndex($order_model);
        return view('ta-admin.order.list', [
            'data' => $order_model,
            'selected' =>[
                $request->all()??""
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ta-admin.order.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data_array = $this->wrapDataStore($request);
        $order_create = Order::create($data_array['sender']);
        $receiver = Arr::get($data_array, 'receivers');

        $receiver_create = [];
        //DB::enableQueryLog();
        foreach ($receiver as $value) {
            $value['order_id'] = $order_create->id;
            $receiver_create = OrderReceive::create($value);
            // DB::getQueryLog();
        }
        $order_create['receivers'] = $receiver_create;


        return json_encode($order_create);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order_model = Order::with('receivers')->find($id);

        return view('ta-admin.order.edit', [
            'data' => $order_model
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{
            $data_array = $this->wrapDataUpdate($request);
            $order_update = Order::find($id)->update($data_array['sender']);
            $receiver = Arr::get($data_array, 'receivers');

            foreach ($receiver as  $value) {
                OrderReceive::find($value['id'])->update($value);

            }

            return redirect()->route('ta-admin.orders.edit', [
                'id' => $id
            ])->with('message', 'success');
        }catch(\Exception $e){
            return redirect()->route('ta-admin.orders.edit', [
                'id' => $id
            ])->withErrors(
                $e->getMessage()

            );
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function wrapDataStore($param)
    {
        $sender = $param->only([
            "driver_car_id",
            "driver_mobile",
            "driver_name",
            "sender_address",
            "sender_mobile",
            "sender_name",
            "sender_province",
            "sender_zip_code",
            "sender_amphure_text",
            "sender_district_text",
            "sender_mobile",
            "sender_name",
            "sender_province",
            "sender_province_text",
        ]);

        $receiver_key = [
            "parcel_description",
            "parcel_pice",
            "payment_type",
            "pickup_type",
            "receive_mobile",
            "receive_name",
            "receive_address",
            "receive_amphure_text",
            "receive_district_text",
            "receive_province_text",
            "receive_province",
            "receive_amphure",
            "receive_district",
            "receive_zip_code",
        ];
        $receiver = $param->only($receiver_key);
        $receivers = [];
        $parcel_amount = 1;
        $parcel_total = 0;
        if ($param->receivers) {
            $parcel_amount = count($param->receivers);
            foreach ($param->receivers as $key => $value) {
                foreach ($value as $item_key => $item) {
                    if (in_array($item_key, $receiver_key)) {
                        $receivers[$key][$item_key] = $item;
                        if ($item_key == 'parcel_pice') {
                            $parcel_total += $item;
                        }
                    }
                }
            }
        }
        array_push($receivers, $receiver);
        $response_sender = [
            "code" => self::generateOrderCode(),
            "customer_name" => $sender["sender_name"] ?? "",
            "customer_mobile" => $sender["sender_mobile"] ?? "",
            "customer_address" => $sender["sender_address"] ?? "",
            "province_name" => $sender["sender_province_text"] ?? "",
            "amphures_name" => $sender["sender_amphure_text"] ?? "",
            "district_name" => $sender["sender_district_text"] ?? "",
            "zip_code" => $sender["sender_zip_code"] ?? "",
            "car_id" => $sender["driver_car_id"] ?? "",
            "driver_name" => $sender["driver_name"] ?? "",
            "driver_mobile" => $sender["driver_mobile"] ?? "",
            "parcel_amount" => $parcel_amount,
            "parcel_total" => $parcel_total,
            "order_status" => "waiting",
            "created_by" => Auth::user()->name,
            "updated_by" => Auth::user()->name,
        ];
        $response_receivers = [];
        foreach ($receivers as $key => $receiver) {
            $response_receivers[$key] = [
                "parcel_code" => self::generateParcelCode(),
                "parcel_description" => $receiver['parcel_description'] ?? null,
                "receive_name" => $receiver['receive_name'] ?? null,
                "receive_mobile" => $receiver['receive_mobile'] ?? null,
                "receive_address" => $receiver['receive_address'] ?? null,
                "province_id" => $receiver['receive_province'] ?? null,
                "amphures_id" => $receiver['receive_amphure'] ?? null,
                "district_id" => $receiver['receive_district'] ?? null,
                "province_name" => $receiver['receive_province_text'] ?? null,
                "amphures_name" => $receiver['receive_amphure_text'] ?? null,
                "district_name" => $receiver['receive_district_text'] ?? null,
                "zip_code" => $receiver['receive_zip_code'] ?? null,
                "parcel_pickup_type" => (Arr::get($receiver, "pickup_type", 0) == "1") ? "pickup" : "delivery",
                "payment_type" => (Arr::get($receiver, 'payment_type', "1") == "1") ? "immediately" : "on_delivery",
                "delivery_status" => "waiting",
                "payment_status" => "waiting",
                "parcel_pice" => $receiver['parcel_pice'],
                "created_by" => Auth::user()->name,
                "updated_by" => Auth::user()->name,
            ];
        }

        return [
            'sender' => $response_sender,
            'receivers' => $response_receivers,
        ];
    }

    static private function generateOrderCode()
    {
        $uuid =  substr(str_replace("-", "", Str::uuid()->toString()), 0, 5);
        return strtoupper("OR" . date('Y') . $uuid);
    }
    static private function generateParcelCode()
    {
        $uuid =  substr(str_replace("-", "", Str::uuid()->toString()), 0, 9);
        return strtoupper("P" . date('Y') . $uuid);
    }

    private function wrapDataIndex($param)
    {
        $data_set = [];
        $parcel_pickup_type = [
            'pickup' => "รับที่ร้าน",
            'delivery' => "จัดส่งปกติ"
        ];
        $payment_type = [
            'immediately' => "จ่ายเงินทันที",
            'on_delivery' => "เก็บเงินปลายทาง"
        ];
        $i = 0;
        // วันที่	รหัสพัสดุ	ชื่อผู้รับ	จังหวัด	จำนวนเงิน	รูปแบบการชำระเงิน	รูปแบบการจัดส่ง

        foreach ($param as  $value) {

            if (count($value['receivers']) >= 1) {
                foreach ($value['receivers'] as $item) {
                    $address = $item['receive_address'] ." ". $item['district_name']." ".$item['amphures_name']." ".$item['province_name']." ".$item['zip_code'];

                    $data_set[$i] = [
                        "created_at" => thaiDateFullmonth($value['created_at']),
                        "order_id" => $value['id'],
                        "order_receive_id" => $item['id'],
                        "parcel_code" => $item['parcel_code'],
                        "parcel_description" =>$item['parcel_description'],
                        "customer_name" => $value['customer_name'] . " (" . $value['customer_mobile'] . ")",
                        "receive_name" => $item['receive_name'] . " (" . $item['receive_mobile'] . ")",
                        "province_name" => $address,
                        "parcel_pice" => $item['parcel_pice'],
                        "payment_type" => $payment_type[$item['payment_type']] ?? "จัดส่งปกติ",
                        "payment_type_id" => $item['payment_type'],
                        "parcel_pickup_type" => $parcel_pickup_type[$item['parcel_pickup_type']],
                    ];
                    $i++;
                }
            }
        }
        return $data_set;
    }

    private function wrapDataUpdate($param)
    {
        $sender = $param->only([
            "driver_car_id",
            "driver_mobile",
            "driver_name",
            "sender_address",
            "sender_mobile",
            "sender_name",
            "sender_province",
            "sender_zip_code",
            "sender_mobile",
            "sender_province",
            "sender_amphure",
            "sender_district",
            "sender_name",
            "sender_province"
        ]);

        $receiver_key = [
            "parcel_description",
            "parcel_pice",
            "payment_type",
            "pickup_type",
            "receive_mobile",
            "receive_name",
            "receive_address",
            "receive_province",
            "receive_amphure",
            "receive_district",
            "receive_zip_code",
        ];
        $receiver = $param->only($receiver_key);

        $response_receiver = [];
        foreach ($receiver as $key => $value) {
            foreach ($value as $item_key => $item) {
                $response_receiver[$item_key][$key] = $item[0];
            }
        }
        $response_sender = [
            "updated_by" => Auth::user()->name,
        ];
        if (Arr::get($sender, "sender_name")) {
            $response_sender["customer_name"] = $sender["sender_name"];
        }
        if (Arr::get($sender, "sender_mobile")) {
            $response_sender["customer_mobile"] = $sender["sender_mobile"];
        }
        if (Arr::get($sender, "sender_address")) {
            $response_sender["customer_address"] = $sender["sender_address"];
        }
        if (Arr::get($sender, "driver_car_id")) {
            $response_sender["car_id"] = $sender["driver_car_id"];
        }
        if (Arr::get($sender, "driver_name")) {
            $response_sender["driver_name"] = $sender["driver_name"];
        }
        if (Arr::get($sender, "driver_mobile")) {
            $response_sender["driver_mobile"] = $sender["driver_mobile"];
        }
        if (Arr::get($sender, "sender_province")) {
            $response_sender["province_name"] = $sender["sender_province"];
        }
        if (Arr::get($sender, "sender_amphure")) {
            $response_sender["amphures_name"] = $sender["sender_amphure"];
        }
        if (Arr::get($sender, "sender_district")) {
            $response_sender["district_name"] = $sender["sender_district"];
        }
        if (Arr::get($sender, "sender_zip_code")) {
            $response_sender["zip_code"] = $sender["sender_zip_code"];
        }


        $parcel_amount = 1;
        $parcel_total = 0;
        $response_receivers = [];
        $key_num = 0;
        foreach ($response_receiver as $key => $receiver) {
            $response_receivers[$key_num] = [
                "id" => $key,
                "parcel_description" => $receiver['parcel_description'] ?? null,
                "receive_name" => $receiver['receive_name'] ?? null,
                "receive_mobile" => $receiver['receive_mobile'] ?? null,
                "receive_address" => $receiver['receive_address'] ?? null,
                "parcel_pickup_type" => (Arr::get($receiver, "pickup_type", 0) == "1") ? "pickup" : "delivery",
                "payment_type" => (Arr::get($receiver, 'payment_type', "1") == "1") ? "immediately" : "on_delivery",
                "parcel_pice" => $receiver['parcel_pice'],
                "updated_by" => Auth::user()->name,
            ];
            if (Arr::get($receiver, "receive_province")) {
                $response_receivers[$key_num]["province_name"] = $receiver["receive_province"];
            }
            if (Arr::get($receiver, "receive_amphure")) {
                $response_receivers[$key_num]["amphures_name"] = $receiver["receive_amphure"];
            }
            if (Arr::get($receiver, "receive_district")) {
                $response_receivers[$key_num]["district_name"] = $receiver["receive_district"];
            }

            if (Arr::get($receiver, "receive_zip_code")) {
                $response_receivers[$key_num]["zip_code"] = $receiver["receive_zip_code"];
            }
            $parcel_total += $receiver['parcel_pice'];
            $parcel_amount++;
            $key_num++;
        }
        $response_sender["parcel_amount"] = $parcel_total;
        $response_sender["parcel_total"] = $parcel_amount;

        return [
            'sender' => $response_sender,
            'receivers' => $response_receivers,
        ];
    }
}
