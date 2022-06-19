<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Arr;
use App\Models\Order;
use App\Models\OrderReceive;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $param = $request->only(
            [
                "start_date", "province_id"
            ]
        );
        $model = Order::with('receivers')
            ->whereHas('receivers', function ($q) use ($param) {
                $province_id = Arr::get($param, "province_id");
                $start_date = Arr::get($param, "start_date");
                if ($province_id) {
                    $q->where('province_id', $province_id);
                }
                if ($start_date) {
                    $start_date = Carbon::parse($start_date)->startOfDay();
                    $end_date = Carbon::parse($start_date)->endOfDay();
                    $q->whereBetween('created_at', [$start_date, $end_date]);
                }
            })
            ->orderByDesc('id')
            ->get();
        return OrderResource::collection($model);
    }
}
