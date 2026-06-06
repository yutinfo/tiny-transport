<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;


class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders = DB::table('order_receives')
        ->select(
            DB::raw("count(id) as 'count_id', SUM(parcel_pice) as 'sum_parcel_pice'"),
            DB::raw("SUM(IF(payment_type = 'on_delivery', parcel_pice, 0)) as parcel_pice_on_delivery"),
            DB::raw("SUM(IF(payment_type = 'immediately' , parcel_pice, 0)) as parcel_pice_immediately")
        )
        ->get()
        ->toArray();

        $province = Cache::rememberForever('province', function () {
            return Province::all()->toArray();
        });

        if($request->has('_token')){

            $param = $request->only(
                [
                    "select_province", "db_date"
                ]
            );

            $order_model = Order::with('receivers')
                ->whereHas('receivers', function ($q) use ($param) {
                    $province_id = Arr::get($param, "select_province");
                    $start_date = Arr::get($param, "db_date");
                    if ($province_id) {
                        $q->where('province_id', $province_id);
                    }
                    if ($start_date) {
                        $start = Carbon::parse($start_date)->startOfDay();
                        $end = Carbon::parse($start_date)->endOfDay();
                        $q->whereBetween('created_at', [$start, $end]);
                    }
                })
                ->orderByDesc('id')
                ->get()
                ->toArray();


        }else{
            $start_data = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
            $end_data = Carbon::now()->format('Y-m-d H:i:s');
            $order_model = Order::with('receivers')
                    ->whereBetween('created_at',[$start_data, $end_data])
                    ->orderByDesc('id')
                    ->get()
                    ->toArray();
        }



        $order_model = $this->wrapDataIndex($order_model,$request);

        return view('admin.dashboard',[
            'data' =>(array)Arr::get($orders,'0',null),
            'province' =>$province,
            'dataTable' =>$order_model,
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
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

    private function wrapDataIndex($param,$request){
        $data_set =[];
        $parcel_pickup_type =[
            'pickup'=>"รับที่ร้าน",
            'delivery'=>"จัดส่งปกติ"
        ];
        $payment_type=[
            'immediately'=>"จ่ายเงินทันที",
            'on_delivery'=>"เก็บเงินปลายทาง"
        ];
        $i=0;
        // วันที่	รหัสพัสดุ	ชื่อผู้รับ	จังหวัด	จำนวนเงิน	รูปแบบการชำระเงิน	รูปแบบการจัดส่ง

        foreach ($param as  $value) {

            if(count($value['receivers'])>=1){
                foreach ($value['receivers'] as $item) {
                    if($request->has('select_province') && ($request->select_province != $item['province_id'])&& ($request->select_province != 0)){
                        continue;
                    }
                    $address = $item['receive_address'] ." ". $item['district_name']." ".$item['amphures_name']." ".$item['province_name']." ".$item['zip_code'];
                    $data_set[$i] =[
                        "created_at" =>thaiDateFullmonth($value['created_at']),
                        "order_receive_id" =>$item['id'],
                        "parcel_code" =>$item['parcel_code'],
                        "parcel_description" =>$item['parcel_description'],
                        "customer_name" =>$value['customer_name']." (".$value['customer_mobile'].")",
                        "receive_name" =>$item['receive_name']." (".$item['receive_mobile'].")",
                        "province_name" =>$address,
                        "parcel_pice" =>$item['parcel_pice'],
                        "payment_type_id" => $item['payment_type'],
                        "payment_type" =>$payment_type[$item['payment_type']]??"จัดส่งปกติ",
                        "parcel_pickup_type" =>$parcel_pickup_type[$item['parcel_pickup_type']],
                    ];
                    $i++;
                }
            }

        }
        return $data_set;
    }
}
