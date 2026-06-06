<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Province;
use App\Models\Trip;
use App\Models\TripItem;
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
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:' . implode(',', Trip::statuses())],
        ]);

        $operationFilters = $this->operationFilters($request);
        $operationData = $this->operationDashboardData($operationFilters);

        $orders = DB::table('order_receives')
        ->select(
            DB::raw("count(id) as 'count_id', SUM(COALESCE(parcel_price, parcel_pice)) as 'sum_parcel_pice'"),
            DB::raw("SUM(CASE WHEN payment_type = 'on_delivery' THEN COALESCE(parcel_price, parcel_pice) ELSE 0 END) as parcel_pice_on_delivery"),
            DB::raw("SUM(CASE WHEN payment_type = 'immediately' THEN COALESCE(parcel_price, parcel_pice) ELSE 0 END) as parcel_pice_immediately")
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
            'operationFilters' => $operationFilters,
            'operationKpis' => $operationData['kpis'],
            'deliveryBreakdown' => $operationData['delivery_breakdown'],
            'codSummary' => $operationData['cod_summary'],
            'tripsByStatus' => $operationData['trips_by_status'],
            'recentTrips' => $operationData['recent_trips'],
            'dailyTrend' => $operationData['daily_trend'],
            'tripStatusLabels' => Trip::statusLabels(),
            'deliveryStatusLabels' => TripItem::deliveryStatusLabels(),
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
                        "parcel_pice" =>isset($item['parcel_price']) ? $item['parcel_price'] : $item['parcel_pice'],
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

    private function operationFilters(Request $request): array
    {
        $dateFrom = $request->input('date_from') ?: Carbon::now()->toDateString();
        $dateTo = $request->input('date_to') ?: $dateFrom;

        if (Carbon::parse($dateTo)->lt(Carbon::parse($dateFrom))) {
            $dateTo = $dateFrom;
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'driver_name' => trim((string) $request->input('driver_name', '')),
            'status' => $request->input('status'),
        ];
    }

    private function operationDashboardData(array $filters): array
    {
        $tripQuery = $this->filteredTripQuery($filters);
        $itemQuery = $this->filteredTripItemQuery($filters);

        $tripSummary = (clone $tripQuery)
            ->selectRaw('COUNT(*) as trips_count')
            ->first();

        $itemSummary = (clone $itemQuery)
            ->selectRaw(
                'COUNT(*) as assigned_count,
                COALESCE(SUM(CASE WHEN delivery_status = ? THEN 1 ELSE 0 END), 0) as delivered_count,
                COALESCE(SUM(CASE WHEN delivery_status = ? THEN 1 ELSE 0 END), 0) as failed_count,
                COALESCE(SUM(CASE WHEN delivery_status = ? THEN 1 ELSE 0 END), 0) as returned_count,
                COALESCE(SUM(CASE WHEN delivery_status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) as waiting_transit_count,
                COALESCE(SUM(cod_amount), 0) as total_cod_amount,
                COALESCE(SUM(collected_amount), 0) as collected_amount',
                [
                    TripItem::DELIVERY_STATUS_DELIVERED,
                    TripItem::DELIVERY_STATUS_FAILED,
                    TripItem::DELIVERY_STATUS_RETURNED,
                    TripItem::DELIVERY_STATUS_WAITING,
                    TripItem::DELIVERY_STATUS_PICKED_UP,
                    TripItem::DELIVERY_STATUS_IN_TRANSIT,
                ]
            )
            ->first();

        $assignedCount = (int) ($itemSummary->assigned_count ?? 0);
        $deliveredCount = (int) ($itemSummary->delivered_count ?? 0);
        $totalCodAmount = (float) ($itemSummary->total_cod_amount ?? 0);
        $collectedAmount = (float) ($itemSummary->collected_amount ?? 0);

        $deliveryBreakdown = (clone $itemQuery)
            ->select('delivery_status', DB::raw('COUNT(*) as total'))
            ->groupBy('delivery_status')
            ->pluck('total', 'delivery_status')
            ->toArray();

        $tripsByStatus = (clone $tripQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $recentTrips = (clone $tripQuery)
            ->with('tripItems')
            ->orderByDesc('trip_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $dailyTrendRaw = (clone $itemQuery)
            ->join('trips', 'trip_items.trip_id', '=', 'trips.id')
            ->select(
                'trips.trip_date',
                DB::raw('COUNT(*) as total_items'),
                DB::raw('SUM(CASE WHEN trip_items.delivery_status = "' . TripItem::DELIVERY_STATUS_DELIVERED . '" THEN 1 ELSE 0 END) as delivered_items')
            )
            ->groupBy('trips.trip_date')
            ->orderBy('trips.trip_date')
            ->get();

        $dailyTrend = [];
        foreach ($dailyTrendRaw as $row) {
            $dailyTrend[] = [
                'date' => Carbon::parse($row->trip_date)->format('Y-m-d'),
                'total_items' => (int) $row->total_items,
                'delivered_items' => (int) $row->delivered_items,
            ];
        }

        return [
            'kpis' => [
                'trips_count' => (int) ($tripSummary->trips_count ?? 0),
                'assigned_count' => $assignedCount,
                'delivered_count' => $deliveredCount,
                'failed_count' => (int) ($itemSummary->failed_count ?? 0),
                'returned_count' => (int) ($itemSummary->returned_count ?? 0),
                'waiting_transit_count' => (int) ($itemSummary->waiting_transit_count ?? 0),
                'total_cod_amount' => $totalCodAmount,
                'collected_amount' => $collectedAmount,
                'remaining_cod_amount' => max(0, $totalCodAmount - $collectedAmount),
                'delivery_success_rate' => $assignedCount > 0 ? round(($deliveredCount / $assignedCount) * 100, 2) : 0,
            ],
            'delivery_breakdown' => $deliveryBreakdown,
            'cod_summary' => [
                'total_cod_amount' => $totalCodAmount,
                'collected_amount' => $collectedAmount,
                'remaining_cod_amount' => max(0, $totalCodAmount - $collectedAmount),
            ],
            'trips_by_status' => $tripsByStatus,
            'recent_trips' => $recentTrips,
            'daily_trend' => $dailyTrend,
        ];
    }

    private function filteredTripQuery(array $filters)
    {
        $query = Trip::query()
            ->whereDate('trip_date', '>=', $filters['date_from'])
            ->whereDate('trip_date', '<=', $filters['date_to']);

        if (! blank($filters['driver_name'])) {
            $query->where('driver_name', 'like', '%' . $filters['driver_name'] . '%');
        }

        if (! blank($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    private function filteredTripItemQuery(array $filters)
    {
        return TripItem::query()
            ->whereHas('trip', function ($query) use ($filters) {
                $query->whereDate('trip_date', '>=', $filters['date_from'])
                    ->whereDate('trip_date', '<=', $filters['date_to']);

                if (! blank($filters['driver_name'])) {
                    $query->where('driver_name', 'like', '%' . $filters['driver_name'] . '%');
                }

                if (! blank($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
            });
    }
}
