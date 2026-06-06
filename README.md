# Tiny Transport

ระบบจัดการขนส่ง (Transport Management System) พัฒนาด้วย Laravel 9 พร้อม AdminLTE UI

## Features

- **Orders** — สร้าง แก้ไข และติดตามใบสั่งงานขนส่ง พร้อมระบบรับงาน (Order Receive)
- **Contacts** — จัดการข้อมูลติดต่อ (CRUD)
- **Users** — จัดการผู้ใช้งานในระบบ
- **Dashboard** — ภาพรวม รายงาน และสถิติต่าง ๆ
- **REST API** — API endpoint สำหรับ Order Receive, Contact และ Trips
- **Delivery Run & Trip Management** — จัดการรอบส่งสินค้า (Trips), มอบหมายพัสดุเข้ารอบ, จัดการสถานะการจัดส่ง และสรุปข้อมูลต้นทุน/กำไร
- **QR Code & Labels** — พิมพ์ใบปะหน้าพัสดุ (Labels) และ QR Code เพื่อสแกนและตรวจสอบข้อมูล
- **Customer Notifications (Stub)** — สถาปัตยกรรมสำหรับแจ้งเตือนสถานะพัสดุแก่ลูกค้าผ่านช่องทางต่าง ๆ

## Tech Stack

- **Backend:** Laravel 9, PHP 8.0+
- **Frontend:** AdminLTE 3.1, Bootstrap 4.6, jQuery 3.6
- **Database:** MySQL
- **Auth:** Laravel Sanctum

## Requirements

- PHP >= 8.0.2
- Composer
- MySQL
- Node.js + npm

## Installation

```bash
# Clone and install dependencies
composer install
npm install && npm run dev

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure .env (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# Run migrations and seed
php artisan migrate
php artisan db:seed
```

เปิดแอปที่ `http://localhost:8000`

## Docker

```bash
docker compose up -d --build
```

เปิดแอปที่ `http://localhost:8000`  
MySQL exposed ที่ port `33306`

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose logs app
docker compose down
```

## Delivery Run & Trip Management

ระบบจัดส่งรอบบริการ (Trips) ประกอบด้วยฟังก์ชันคำนวณด้านต้นทุน รายรับ กำไร และสถานะการชำระเงินของพัสดุแต่ละรายการ:

1. **Trip Lifecycle:** Draft (แบบร่าง) -> Assigned (มอบหมายพัสดุแล้ว) -> In Transit (กำลังนำส่ง) -> Completed (เสร็จสิ้น) หรือ Cancelled (ยกเลิก)
2. **Backward-compatible `parcel_price`:** ระบบรองรับฟิลด์ `parcel_price` เพื่อแก้ไขตัวสะกดที่ผิดพลาดจาก `parcel_pice` โดยโมเดล `OrderReceive` มี Accessor/Mutator ที่ช่วยประทับตราและซิงค์ข้อมูลระหว่าง 2 คอลัมน์นี้อย่างสมบูรณ์แบบโดยอัตโนมัติ เพื่อรองรับโค้ดเก่าและโค้ดใหม่ควบคู่กันไป
3. **Notification Stub:** ระบบนี้มีโครงสร้าง (Service/Table/Model) สำหรับส่งแจ้งเตือนสถานะแก่ลูกค้าผ่าน SMS/LINE/Email ไว้เรียบร้อยแล้ว ในปัจจุบันใช้ mock-up การส่งจริง โดยผู้ใช้สามารถบันทึกประวัติการแจ้งเตือนแบบแมนนวลหรือดูประวัติแจ้งเตือนได้จากหน้าติดตามพัสดุ (Parcel Tracking)

## Routes

| Method | Path | Description |
|--------|------|-------------|
| GET | `/admin/dashboard` | Dashboard และสถิติรอบจัดส่ง |
| GET/POST | `/admin/orders` | จัดการใบสั่งงาน |
| DELETE | `/admin/order-receive/{id}` | ลบรายการรับงาน |
| GET/POST | `/admin/contacts` | จัดการข้อมูลติดต่อ |
| GET/POST | `/admin/users` | จัดการผู้ใช้งาน |
| GET/POST | `/admin/trips` | จัดการรอบจัดส่งพัสดุ |
| POST | `/admin/trips/{trip}/start` | เริ่มเดินทางจัดส่ง |
| POST | `/admin/trips/{trip}/complete` | ปิดรอบจัดส่ง |
| POST | `/admin/trips/{trip}/cancel` | ยกเลิกรอบจัดส่ง |
| GET | `/admin/trips/{trip}/driver` | หน้ามุมมองพนักงานขับรถ (Driver View) |
| GET | `/admin/parcels/{orderReceive}/tracking` | หน้าไทม์ไลน์ติดตามพัสดุ พร้อมประวัติแจ้งเตือนลูกค้า |
| POST | `/admin/parcels/{orderReceive}/notifications` | บันทึกประวัติการแจ้งเตือนลูกค้าด้วยมือ |

## API

| Method | Path | Description |
|--------|------|-------------|
| DELETE | `/api/order-receive/{id}` | ลบ Order Receive |
| GET | `/api/contacts` | รายการ Contact |
| GET | `/api/trips` | รายการรอบจัดส่งพัสดุ |
| GET | `/api/trips/{trip}/items` | รายการพัสดุในรอบจัดส่ง |
| POST | `/api/trip-items/{tripItem}/delivery-status` | อัปเดตสถานะจัดส่ง |
| POST | `/api/trip-items/{tripItem}/payment-status` | อัปเดตสถานะชำระเงิน COD |

## License

MIT
