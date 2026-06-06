<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripCost;
use App\Services\TripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class TripCostController extends Controller
{
    public function __construct(private TripService $tripService)
    {
    }

    public function store(Trip $trip, Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(TripCost::types())],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ], [], [
            'type' => 'ประเภทค่าใช้จ่าย',
            'description' => 'รายละเอียด',
            'amount' => 'จำนวนเงิน',
        ]);

        try {
            $this->tripService->addCost($trip, $data, Auth::user()->name ?? null);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['cost' => $exception->getMessage()]);
        }

        return redirect()->route('admin.trips.show', $trip)->with('success', 'เพิ่มค่าใช้จ่ายแล้ว');
    }

    public function destroy(TripCost $tripCost)
    {
        $trip = $tripCost->trip;

        try {
            $this->tripService->deleteCost($tripCost);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withErrors(['cost' => $exception->getMessage()]);
        }

        return redirect()->route('admin.trips.show', $trip)->with('success', 'ลบค่าใช้จ่ายแล้ว');
    }
}
