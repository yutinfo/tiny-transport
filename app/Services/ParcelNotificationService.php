<?php

namespace App\Services;

use App\Models\OrderReceive;
use App\Models\ParcelNotification;
use App\Models\TripItem;
use Illuminate\Support\Facades\Auth;

class ParcelNotificationService
{
    /**
     * Create a pending notification log for the order receive.
     */
    public function createPendingNotification(OrderReceive $receiver, string $channel, string $templateName): ParcelNotification
    {
        $recipient = $receiver->receive_mobile ?: '';
        $message = $this->generateMessage($receiver, $templateName);

        $notification = ParcelNotification::create([
            'order_receive_id' => $receiver->id,
            'channel' => $channel,
            'recipient' => $recipient,
            'message' => $message,
            'status' => 'pending',
            'created_by' => Auth::user()->name ?? 'System',
        ]);

        // Stub/Mock provider sending: auto-send manual or background notifications
        $this->send($notification);

        return $notification;
    }

    /**
     * Simulate sending the notification through a provider.
     */
    public function send(ParcelNotification $notification): void
    {
        if ($notification->status !== 'pending') {
            return;
        }

        try {
            // For stub, we succeed immediately unless some mock fail condition is met
            $response = [
                'success' => true,
                'message_id' => 'mock_msg_' . uniqid(),
                'provider' => 'stub',
            ];

            $this->markSent($notification, json_encode($response, JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            $this->markFailed($notification, $e->getMessage());
        }
    }

    public function markSent(ParcelNotification $notification, $providerResponse = null): void
    {
        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
            'provider_response' => $providerResponse,
        ]);
    }

    public function markFailed(ParcelNotification $notification, string $errorMessage = null): void
    {
        $notification->update([
            'status' => 'failed',
            'provider_response' => json_encode(['error' => $errorMessage], JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function skip(ParcelNotification $notification): void
    {
        $notification->update([
            'status' => 'skipped',
        ]);
    }

    /**
     * Generate template message based on name.
     */
    protected function generateMessage(OrderReceive $receiver, string $templateName): string
    {
        $receiver->load('order');
        $parcelCode = $receiver->parcel_code;
        $orderCode = $receiver->order->code ?? '';

        switch ($templateName) {
            case 'assigned_to_trip':
                $latestTrip = $receiver->tripItems()->latest('id')->first()?->trip;
                $tripCode = $latestTrip ? $latestTrip->code : '';
                return "พัสดุรหัส {$parcelCode} ของออเดอร์ {$orderCode} ถูกมอบหมายจัดส่งในรอบ {$tripCode} เรียบร้อยแล้ว";
            case 'out_for_delivery':
                $latestTrip = $receiver->tripItems()->latest('id')->first()?->trip;
                $driverMobile = $latestTrip ? $latestTrip->driver_mobile : '';
                return "พัสดุรหัส {$parcelCode} กำลังนำจ่ายโดยเจ้าหน้าที่ขนส่ง เบอร์ติดต่อพนักงานขับรถ: " . ($driverMobile ?: '-');
            case 'delivered':
                return "พัสดุรหัส {$parcelCode} จัดส่งสำเร็จเรียบร้อยแล้ว ขอบคุณที่ใช้บริการค่ะ";
            case 'failed':
                $latestItem = $receiver->tripItems()->latest('id')->first();
                $reason = $latestItem ? $latestItem->failed_reason : '';
                return "พัสดุรหัส {$parcelCode} จัดส่งไม่สำเร็จ เนื่องจาก: " . ($reason ?: 'ไม่ระบุเหตุผล');
            default:
                return "แจ้งเตือนสถานะพัสดุรหัส {$parcelCode}: " . $templateName;
        }
    }
}
