<?php

namespace App\Services;

use App\Models\OrderReceive;
use App\Models\ParcelStatusLog;
use App\Models\Trip;
use App\Models\TripCost;
use App\Models\TripItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TripService
{
    public function createTrip(array $data): Trip
    {
        return DB::transaction(function () use ($data) {
            $tripDate = $data['trip_date'] ?? now()->toDateString();

            return Trip::create(array_merge($data, [
                'code' => $data['code'] ?? Trip::generateCode($tripDate),
                'trip_date' => $tripDate,
                'status' => $data['status'] ?? Trip::STATUS_DRAFT,
            ]));
        });
    }

    public function assignTrip(Trip $trip, ?string $updatedBy = null): Trip
    {
        return DB::transaction(function () use ($trip, $updatedBy) {
            $trip = $this->freshTrip($trip);
            $this->ensureTripStatus($trip, [Trip::STATUS_DRAFT], 'เปลี่ยนสถานะเป็นมอบหมายได้เฉพาะรอบจัดส่งแบบร่าง');

            $trip->fill([
                'status' => Trip::STATUS_ASSIGNED,
                'updated_by' => $updatedBy,
            ]);
            $trip->save();

            return $trip->refresh();
        });
    }

    public function assignParcel(Trip $trip, OrderReceive $receiver, ?string $createdBy = null): TripItem
    {
        return DB::transaction(function () use ($trip, $receiver, $createdBy) {
            $trip = $this->freshTrip($trip);
            $receiver = $receiver->fresh();

            $this->ensureTripModifiable($trip);
            $this->ensureTripStatus($trip, [Trip::STATUS_DRAFT, Trip::STATUS_ASSIGNED], 'เพิ่มพัสดุได้เฉพาะรอบจัดส่งแบบร่างหรือมอบหมายแล้ว');
            $this->ensureReceiverCanBeAssigned($trip, $receiver);

            $deliveryStatus = $receiver->delivery_status ?: TripItem::DELIVERY_STATUS_WAITING;
            $paymentStatus = $receiver->payment_status ?: TripItem::PAYMENT_STATUS_WAITING;

            $tripItem = TripItem::create([
                'trip_id' => $trip->id,
                'order_id' => $receiver->order_id,
                'order_receive_id' => $receiver->id,
                'parcel_code' => $receiver->parcel_code,
                'delivery_status' => $deliveryStatus,
                'payment_status' => $paymentStatus,
                'cod_amount' => $receiver->payment_type === 'on_delivery' ? $receiver->getParcelPriceValue() : 0,
                'collected_amount' => 0,
                'created_by' => $createdBy,
            ]);

            $this->createStatusLog(
                $receiver,
                $trip,
                $receiver->delivery_status ?: null,
                $deliveryStatus,
                'เพิ่มเข้ารอบขนส่ง ' . $trip->code,
                $createdBy
            );

            // Hook for notification stub
            try {
                $notificationService = app(\App\Services\ParcelNotificationService::class);
                $notificationService->createPendingNotification($receiver, 'sms', 'assigned_to_trip');
            } catch (\Exception $e) {
                logger()->error('Failed to create assigned_to_trip notification: ' . $e->getMessage());
            }

            $this->recalculateTotals($trip);

            return $tripItem->refresh();
        });
    }

    public function assignItems(Trip $trip, array $orderReceiveIds, ?string $createdBy = null)
    {
        return DB::transaction(function () use ($trip, $orderReceiveIds, $createdBy) {
            $items = collect();
            $receivers = OrderReceive::query()
                ->whereIn('id', array_values(array_unique($orderReceiveIds)))
                ->orderBy('id')
                ->get();

            foreach ($receivers as $receiver) {
                $items->push($this->assignParcel($trip, $receiver, $createdBy));
            }

            return $items;
        });
    }

    public function removeItem(TripItem $tripItem): void
    {
        DB::transaction(function () use ($tripItem) {
            $tripItem = $this->freshTripItem($tripItem);
            $trip = $tripItem->trip;

            $this->ensureTripStatus($trip, [Trip::STATUS_DRAFT, Trip::STATUS_ASSIGNED], 'ลบพัสดุได้เฉพาะรอบจัดส่งแบบร่างหรือมอบหมายแล้ว');

            $tripItem->delete();
            $this->recalculateTotals($trip);
        });
    }

    public function recalculateTotals(Trip $trip): Trip
    {
        $trip = $this->freshTrip($trip);

        $totals = TripItem::query()
            ->where('trip_id', $trip->id)
            ->selectRaw('COUNT(*) as total_parcels, COALESCE(SUM(cod_amount), 0) as total_cod_amount, COALESCE(SUM(collected_amount), 0) as collected_amount')
            ->first();

        $trip->fill([
            'total_parcels' => (int) $totals->total_parcels,
            'total_cod_amount' => $totals->total_cod_amount,
            'collected_amount' => $totals->collected_amount,
        ]);
        $trip->save();

        return $trip->refresh();
    }

    public function addCost(Trip $trip, array $data, ?string $createdBy = null): TripCost
    {
        return DB::transaction(function () use ($trip, $data, $createdBy) {
            $trip = $this->freshTrip($trip);
            $this->ensureTripModifiable($trip);

            return TripCost::create([
                'trip_id' => $trip->id,
                'type' => $data['type'],
                'description' => $data['description'] ?? null,
                'amount' => $data['amount'],
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);
        });
    }

    public function deleteCost(TripCost $cost): void
    {
        DB::transaction(function () use ($cost) {
            $cost = $cost->fresh(['trip']) ?: $cost;
            $this->ensureTripModifiable($cost->trip);
            $cost->delete();
        });
    }

    public function financialSummary(Trip $trip): array
    {
        $trip = $trip->fresh(['tripItems.orderReceive', 'costs']) ?: $trip;

        $revenue = $trip->tripItems->sum(function (TripItem $item) {
            return (float) ($item->orderReceive ? $item->orderReceive->getParcelPriceValue() : 0);
        });
        $totalCost = $trip->costs->sum(fn (TripCost $cost) => (float) $cost->amount);

        return [
            'revenue' => round($revenue, 2),
            'total_cost' => round($totalCost, 2),
            'estimated_profit' => round($revenue - $totalCost, 2),
        ];
    }

    public function startTrip(Trip $trip, ?string $updatedBy = null): Trip
    {
        return DB::transaction(function () use ($trip, $updatedBy) {
            $trip = $this->freshTrip($trip);
            $this->ensureTripStatus($trip, [Trip::STATUS_ASSIGNED], 'เริ่มจัดส่งได้เฉพาะรอบจัดส่งที่มอบหมายแล้ว');

            $trip->fill([
                'status' => Trip::STATUS_IN_TRANSIT,
                'started_at' => $trip->started_at ?: now(),
                'updated_by' => $updatedBy,
            ]);
            $trip->save();

            // Hook for notification stub: notify for all items
            try {
                $notificationService = app(\App\Services\ParcelNotificationService::class);
                foreach ($trip->tripItems as $item) {
                    if ($item->orderReceive) {
                        $notificationService->createPendingNotification($item->orderReceive, 'sms', 'out_for_delivery');
                    }
                }
            } catch (\Exception $e) {
                logger()->error('Failed to create out_for_delivery notifications: ' . $e->getMessage());
            }

            return $trip->refresh();
        });
    }

    public function completeTrip(Trip $trip, ?string $updatedBy = null): Trip
    {
        return DB::transaction(function () use ($trip, $updatedBy) {
            $trip = $this->freshTrip($trip);
            $this->ensureTripStatus($trip, [Trip::STATUS_IN_TRANSIT, Trip::STATUS_PENDING_VERIFICATION], 'ปิดรอบจัดส่งได้เฉพาะรอบที่กำลังจัดส่งหรือรอตรวจสอบยอด');

            $items = $trip->tripItems()->get();

            foreach ($items as $item) {
                if (! in_array($item->delivery_status, $this->finalDeliveryStatuses(), true)) {
                    throw new InvalidArgumentException('ยังมีพัสดุที่ยังไม่จบสถานะจัดส่ง');
                }

                if ($item->delivery_status === TripItem::DELIVERY_STATUS_FAILED && ! $this->hasFailureDetail($item->failed_reason, $item->note)) {
                    throw new InvalidArgumentException('พัสดุที่จัดส่งไม่สำเร็จต้องระบุเหตุผล');
                }
            }

            $trip = $this->recalculateTotals($trip);
            $trip->fill([
                'status' => Trip::STATUS_COMPLETED,
                'completed_at' => now(),
                'updated_by' => $updatedBy,
            ]);
            $trip->save();

            return $trip->refresh();
        });
    }

    public function submitTrip(Trip $trip, ?string $updatedBy = null): Trip
    {
        return DB::transaction(function () use ($trip, $updatedBy) {
            $trip = $this->freshTrip($trip);
            $this->ensureTripStatus($trip, [Trip::STATUS_IN_TRANSIT], 'ส่งยอดได้เฉพาะรอบที่กำลังจัดส่ง');

            $items = $trip->tripItems()->get();

            foreach ($items as $item) {
                if (! in_array($item->delivery_status, $this->finalDeliveryStatuses(), true)) {
                    throw new InvalidArgumentException('ยังมีพัสดุที่ยังไม่จบสถานะจัดส่ง');
                }

                if ($item->delivery_status === TripItem::DELIVERY_STATUS_FAILED && ! $this->hasFailureDetail($item->failed_reason, $item->note)) {
                    throw new InvalidArgumentException('พัสดุที่จัดส่งไม่สำเร็จต้องระบุเหตุผล');
                }
            }

            $trip = $this->recalculateTotals($trip);
            $trip->fill([
                'status' => Trip::STATUS_PENDING_VERIFICATION,
                'updated_by' => $updatedBy,
            ]);
            $trip->save();

            return $trip->refresh();
        });
    }

    public function cancelTrip(Trip $trip, ?string $updatedBy = null): Trip
    {
        return DB::transaction(function () use ($trip, $updatedBy) {
            $trip = $this->freshTrip($trip);
            $this->ensureTripStatus($trip, [Trip::STATUS_DRAFT, Trip::STATUS_ASSIGNED], 'ยกเลิกรอบจัดส่งได้เฉพาะแบบร่างหรือมอบหมายแล้ว');

            $trip->fill([
                'status' => Trip::STATUS_CANCELLED,
                'updated_by' => $updatedBy,
            ]);
            $trip->save();

            return $trip->refresh();
        });
    }

    public function updateDeliveryStatus(
        TripItem $tripItem,
        string $status,
        ?string $note = null,
        ?string $failedReason = null,
        ?string $updatedBy = null
    ): TripItem {
        return DB::transaction(function () use ($tripItem, $status, $note, $failedReason, $updatedBy) {
            if (! in_array($status, TripItem::deliveryStatuses(), true)) {
                throw new InvalidArgumentException('สถานะจัดส่งไม่ถูกต้อง');
            }

            $tripItem = $this->freshTripItem($tripItem);
            $this->ensureTripModifiable($tripItem->trip);

            if ($status === TripItem::DELIVERY_STATUS_FAILED && ! $this->hasFailureDetail($failedReason, $note)) {
                throw new InvalidArgumentException('พัสดุที่จัดส่งไม่สำเร็จต้องระบุเหตุผล');
            }

            $fromStatus = $tripItem->delivery_status;
            $tripItem->fill([
                'delivery_status' => $status,
                'failed_reason' => $failedReason ?: $tripItem->failed_reason,
                'note' => $note ?: $tripItem->note,
                'delivered_at' => $status === TripItem::DELIVERY_STATUS_DELIVERED ? now() : $tripItem->delivered_at,
                'updated_by' => $updatedBy,
            ]);
            $tripItem->save();

            $tripItem->orderReceive()->update([
                'delivery_status' => $status,
                'updated_by' => $updatedBy,
            ]);

            $this->createStatusLog($tripItem->orderReceive, $tripItem->trip, $fromStatus, $status, $note, $updatedBy);

            // Hook for notification stub
            try {
                $notificationService = app(\App\Services\ParcelNotificationService::class);
                if ($status === TripItem::DELIVERY_STATUS_DELIVERED) {
                    $notificationService->createPendingNotification($tripItem->orderReceive, 'sms', 'delivered');
                } elseif ($status === TripItem::DELIVERY_STATUS_FAILED) {
                    $notificationService->createPendingNotification($tripItem->orderReceive, 'sms', 'failed');
                } elseif ($status === TripItem::DELIVERY_STATUS_IN_TRANSIT) {
                    $notificationService->createPendingNotification($tripItem->orderReceive, 'sms', 'out_for_delivery');
                }
            } catch (\Exception $e) {
                logger()->error('Failed to create status update notification: ' . $e->getMessage());
            }

            return $tripItem->refresh();
        });
    }

    public function updatePaymentCollection(
        TripItem $tripItem,
        string $paymentStatus,
        $collectedAmount,
        ?string $note = null,
        ?string $updatedBy = null
    ): TripItem {
        return DB::transaction(function () use ($tripItem, $paymentStatus, $collectedAmount, $note, $updatedBy) {
            if (! in_array($paymentStatus, TripItem::paymentStatuses(), true)) {
                throw new InvalidArgumentException('สถานะชำระเงินไม่ถูกต้อง');
            }

            $tripItem = $this->freshTripItem($tripItem);
            $this->ensureTripModifiable($tripItem->trip);

            $codAmount = round((float) $tripItem->cod_amount, 2);

            if ($paymentStatus === TripItem::PAYMENT_STATUS_PAID && blank($collectedAmount)) {
                $collectedAmount = $codAmount;
            }

            if ($codAmount <= 0 && in_array($paymentStatus, [TripItem::PAYMENT_STATUS_PAID, TripItem::PAYMENT_STATUS_WAIVED], true)) {
                $collectedAmount = 0;
            }

            $collectedAmount = round((float) $collectedAmount, 2);

            if ($collectedAmount < 0) {
                throw new InvalidArgumentException('ยอดเก็บเงินต้องไม่ติดลบ');
            }

            if ($collectedAmount > $codAmount && $paymentStatus !== TripItem::PAYMENT_STATUS_WAIVED && blank($note)) {
                throw new InvalidArgumentException('ยอดเก็บเงินมากกว่ายอด COD ต้องระบุหมายเหตุ');
            }

            $tripItem->fill([
                'payment_status' => $paymentStatus,
                'collected_amount' => $collectedAmount,
                'note' => $note ?: $tripItem->note,
                'updated_by' => $updatedBy,
            ]);
            $tripItem->save();

            $tripItem->orderReceive()->update([
                'payment_status' => $paymentStatus,
                'updated_by' => $updatedBy,
            ]);

            $this->recalculateTotals($tripItem->trip);

            return $tripItem->refresh();
        });
    }

    public function createStatusLog(
        OrderReceive $receiver,
        ?Trip $trip,
        ?string $fromStatus,
        string $toStatus,
        ?string $note = null,
        ?string $createdBy = null
    ): ParcelStatusLog {
        return ParcelStatusLog::create([
            'order_receive_id' => $receiver->id,
            'trip_id' => $trip?->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
            'created_by' => $createdBy,
        ]);
    }

    private function ensureReceiverCanBeAssigned(Trip $trip, OrderReceive $receiver): void
    {
        $sameTripExists = TripItem::query()
            ->where('trip_id', $trip->id)
            ->where('order_receive_id', $receiver->id)
            ->exists();

        if ($sameTripExists) {
            throw new InvalidArgumentException('พัสดุนี้อยู่ในรอบจัดส่งนี้แล้ว');
        }

        $activeAssignmentExists = TripItem::query()
            ->where('order_receive_id', $receiver->id)
            ->whereNotIn('delivery_status', [
                TripItem::DELIVERY_STATUS_FAILED,
                TripItem::DELIVERY_STATUS_RETURNED,
            ])
            ->whereHas('trip', function ($query) {
                $query->whereNotIn('status', [
                    Trip::STATUS_CANCELLED,
                ]);
            })
            ->exists();

        if ($activeAssignmentExists) {
            throw new InvalidArgumentException('พัสดุนี้ถูกจัดเข้ารอบจัดส่งที่ยังใช้งานอยู่แล้ว');
        }
    }

    private function ensureTripModifiable(Trip $trip): void
    {
        if (in_array($trip->status, [Trip::STATUS_COMPLETED, Trip::STATUS_CANCELLED], true)) {
            throw new InvalidArgumentException('ไม่สามารถแก้ไขรอบจัดส่งที่เสร็จสิ้นหรือยกเลิกแล้ว');
        }
    }

    private function ensureTripStatus(Trip $trip, array $allowedStatuses, string $message): void
    {
        if (! in_array($trip->status, $allowedStatuses, true)) {
            throw new InvalidArgumentException($message);
        }
    }

    private function finalDeliveryStatuses(): array
    {
        return [
            TripItem::DELIVERY_STATUS_DELIVERED,
            TripItem::DELIVERY_STATUS_FAILED,
            TripItem::DELIVERY_STATUS_RETURNED,
        ];
    }

    private function hasFailureDetail(?string $failedReason, ?string $note): bool
    {
        return filled($failedReason) || filled($note);
    }

    private function freshTrip(Trip $trip): Trip
    {
        return $trip->fresh() ?: $trip;
    }

    private function freshTripItem(TripItem $tripItem): TripItem
    {
        return $tripItem->fresh(['trip', 'orderReceive']) ?: $tripItem;
    }
}
