# Tiny Transport

ระบบจัดการขนส่งและพัสดุขนาดเล็ก พัฒนาด้วย Laravel 9 พร้อมหน้าแอดมินบน AdminLTE 3 สำหรับสร้างใบสั่งงาน รับพัสดุ จัดรอบจัดส่ง ติดตามสถานะ และสรุปข้อมูล COD/ต้นทุนการจัดส่ง

## Features

- **Orders & Order Receive** - สร้างใบสั่งงานขนส่ง บันทึกรายการผู้รับหลายรายการต่อออเดอร์ และลบรายการรับงานได้
- **Contacts** - จัดการข้อมูลผู้ติดต่อ พร้อม API สำหรับค้นหา/แนะนำข้อมูลผู้ติดต่อ
- **Dashboard** - สรุปยอดพัสดุ รายรับ COD สถานะจัดส่ง สถานะรอบขนส่ง และรายการล่าสุด
- **Trip Management** - สร้างรอบจัดส่ง มอบหมายพัสดุเข้ารอบ เริ่มรอบ ปิดรอบ ยกเลิกรอบ และแก้ไขข้อมูลรอบ
- **Driver View** - มุมมองพนักงานขับรถสำหรับอัปเดตสถานะจัดส่งและสถานะชำระเงิน
- **COD & Cost Tracking** - บันทึกยอดเก็บเงินปลายทาง ยอดที่เก็บได้ ต้นทุนรอบจัดส่ง และสรุปกำไร/ขาดทุน
- **Parcel Labels & QR** - พิมพ์ใบปะหน้าพัสดุจากออเดอร์หรือรอบจัดส่ง พร้อม QR สำหรับติดตามพัสดุ
- **Parcel Tracking** - ค้นหา/ดูไทม์ไลน์พัสดุจากรหัสพัสดุ และบันทึกประวัติการแจ้งเตือนลูกค้า
- **CSV Export** - ส่งออกรายการรอบจัดส่ง รายการพัสดุในรอบ และสรุป COD เป็น CSV
- **Location API** - API จังหวัด อำเภอ และตำบล สำหรับฟอร์มที่อยู่

## Tech Stack

- **Backend:** Laravel 9, PHP 8.0.2+
- **Auth/API:** Laravel Sanctum
- **Frontend:** Laravel Mix 6, Webpack 5, Sass
- **UI:** AdminLTE 3.1, Bootstrap 4.6, jQuery 3.6, Font Awesome 4
- **Database:** MySQL
- **Docker:** PHP 8.1 Apache, MySQL 8.0, Node 16 สำหรับ build assets

## Requirements

- PHP 8.0.2+
- Composer
- MySQL
- Node.js + npm
- Docker + Docker Compose ถ้ารันด้วย Docker

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

ตั้งค่า `.env` ให้ตรงกับฐานข้อมูลที่ใช้งาน ตัวอย่างจาก `.env.example` ใช้ MySQL ที่ `127.0.0.1:33306`, database `tiny_transport`

```bash
php artisan migrate
php artisan db:seed
npm run dev
php artisan serve
```

เปิดแอปที่ `http://localhost:8000`

บัญชีเริ่มต้นจาก seeder:

```text
username: admin
password: password
```

## Docker

```bash
docker compose up -d --build
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

เปิดแอปที่ `http://localhost:8000`

ข้อมูลฐานข้อมูลใน Docker:

```text
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=tiny_transport
DB_USERNAME=tatransport
DB_PASSWORD=tatransport
```

MySQL ถูก expose มาที่เครื่อง host ที่ port `33306`

คำสั่งที่ใช้บ่อย:

```bash
docker compose logs app
docker compose down
```

## Frontend Assets

ไฟล์ Sass หลักอยู่ที่ `resources/sass/app.scss` และ import UI refinement จาก `resources/sass/_modern-ui.scss`

```bash
npm run dev
npm run watch
npm run prod
```

หลีกเลี่ยงการแก้ไฟล์ที่ build แล้วใน `public/css`, `public/js`, และ `public/mix-manifest.json` โดยตรง ให้แก้ source แล้ว build ใหม่แทน

## Main Web Routes

หน้าเว็บหลักใช้ `/login` สำหรับเข้าสู่ระบบ และพื้นที่จัดการอยู่ใต้ prefix `/admin`

| Method | Path | Description |
| --- | --- | --- |
| GET/POST | `/login` | หน้าเข้าสู่ระบบและ action login |
| POST | `/logout` | ออกจากระบบ |
| GET | `/admin/dashboard` | Dashboard และสถิติการจัดส่ง |
| GET/POST | `/admin/orders` | รายการออเดอร์และสร้างออเดอร์ |
| GET | `/admin/orders/{order}/labels` | พิมพ์ label ของออเดอร์ |
| DELETE | `/admin/order-receive/{id}` | ลบรายการรับพัสดุ |
| GET/POST | `/admin/contacts` | จัดการข้อมูลผู้ติดต่อ |
| GET/POST | `/admin/users` | จัดการผู้ใช้งาน เฉพาะ admin |
| GET/POST | `/admin/trips` | รายการและสร้างรอบจัดส่ง |
| GET | `/admin/trips/export/csv` | Export รายการรอบจัดส่ง |
| GET/POST | `/admin/trips/{trip}` | ดูและบันทึกข้อมูลรอบจัดส่ง |
| GET | `/admin/trips/{trip}/assign` | เลือกพัสดุเข้ารอบจัดส่ง |
| POST | `/admin/trips/{trip}/assign-items` | เพิ่มพัสดุเข้ารอบจัดส่ง |
| POST | `/admin/trips/{trip}/assign-status` | เปลี่ยนสถานะรอบเป็น assigned |
| POST | `/admin/trips/{trip}/start` | เริ่มรอบจัดส่ง |
| POST | `/admin/trips/{trip}/complete` | ปิดรอบจัดส่ง |
| POST | `/admin/trips/{trip}/cancel` | ยกเลิกรอบจัดส่ง |
| GET | `/admin/trips/{trip}/driver` | Driver View |
| GET | `/admin/trips/{trip}/labels` | พิมพ์ label ของพัสดุในรอบ |
| POST | `/admin/trips/{trip}/costs` | บันทึกต้นทุนรอบจัดส่ง |
| GET | `/admin/trips/{trip}/items/export/csv` | Export พัสดุในรอบ |
| GET | `/admin/trips/{trip}/cod/export/csv` | Export สรุป COD |
| POST | `/admin/trip-items/{tripItem}/delivery-status` | อัปเดตสถานะจัดส่ง |
| POST | `/admin/trip-items/{tripItem}/payment-status` | อัปเดตสถานะชำระเงิน |
| GET | `/admin/parcels/search` | ค้นหาพัสดุ |
| GET | `/admin/parcels/code/{parcelCode}` | เปิดหน้าติดตามจากรหัสพัสดุ |
| GET | `/admin/parcels/{orderReceive}/tracking` | ไทม์ไลน์ติดตามพัสดุ |
| POST | `/admin/parcels/{orderReceive}/notifications` | บันทึกประวัติแจ้งเตือนลูกค้า |

## API Routes

Location API เปิดให้เรียกได้โดยไม่ต้อง auth:

| Method | Path | Description |
| --- | --- | --- |
| GET | `/api/province` | รายการจังหวัด |
| GET | `/api/province/{id}` | รายละเอียดจังหวัด |
| GET | `/api/amphure` | รายการอำเภอ |
| GET | `/api/amphure/{id}` | รายละเอียดอำเภอ |
| GET | `/api/district` | รายการตำบล |
| GET | `/api/district/{id}` | รายละเอียดตำบล |

API ต่อไปนี้อยู่หลัง `auth:sanctum`:

| Method | Path | Description |
| --- | --- | --- |
| GET | `/api/order` | รายการออเดอร์ |
| GET | `/api/contacts/suggest` | แนะนำข้อมูลผู้ติดต่อ |
| GET | `/api/contacts/search` | ค้นหาข้อมูลผู้ติดต่อ |
| GET | `/api/trips` | รายการรอบจัดส่ง |
| GET | `/api/trips/{trip}` | รายละเอียดรอบจัดส่ง |
| GET | `/api/trips/{trip}/items` | พัสดุในรอบจัดส่ง |
| POST | `/api/trip-items/{tripItem}/delivery-status` | อัปเดตสถานะจัดส่ง |
| POST | `/api/trip-items/{tripItem}/payment-status` | อัปเดตสถานะชำระเงิน COD |
| GET | `/api/parcels/{parcelCode}` | รายละเอียดและ timeline ของพัสดุ |

## Trip Statuses

รอบจัดส่งรองรับสถานะ:

```text
draft -> assigned -> in_transit -> completed
cancelled
```

สถานะพัสดุในรอบจัดส่ง:

```text
waiting, picked_up, in_transit, delivered, failed, returned
```

สถานะการชำระเงิน:

```text
waiting, paid, unpaid, waived
```

## Database Notes

- Seeder หลักรัน `LocationSeeder` และ `UserSeeder`
- ตารางรอบจัดส่งประกอบด้วย `trips`, `trip_items`, `trip_costs`, `parcel_status_logs`, และ `parcel_notifications`
- `order_receives` รองรับทั้ง `parcel_price` และชื่อเดิม `parcel_pice`; model จะ sync ค่าระหว่างสอง field เพื่อรองรับโค้ดเก่าและโค้ดใหม่
- Migration ล่าสุดมี index เพิ่มเติมสำหรับ `order_receives` เพื่อช่วยงานค้นหาและ dashboard

## Validation

ใช้คำสั่งที่ตรงกับประเภทงาน:

```bash
php artisan test
npm run dev
npm run prod
php artisan route:list
php artisan migrate
```

ถ้าเปลี่ยน migration หรือ seed ให้ทดสอบ lifecycle ฐานข้อมูลที่เกี่ยวข้อง เช่น `php artisan migrate:fresh --seed` หรือคำสั่ง Docker เทียบเท่า

## License

MIT
