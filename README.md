# Tiny Transport

ระบบจัดการขนส่งและพัสดุขนาดเล็ก พัฒนาด้วย Laravel 9 — มีหน้าเว็บสาธารณะ (landing + ติดตามพัสดุ) ธีม tech-blue ด้วย Vue 3, หน้าแอดมินบน AdminLTE 3 สำหรับสร้างใบสั่งงาน รับพัสดุ จัดรอบจัดส่ง จัดการคนขับ และสรุปข้อมูล COD/ต้นทุน พร้อม driver portal สำหรับพนักงานขับรถ

## Features

### Public (ไม่ต้องเข้าสู่ระบบ)
- **Landing Page** (`/`) - หน้าแรกบริษัท ธีม tech-blue พร้อมช่องค้นหาพัสดุ ลิงก์เข้าสู่ระบบพนักงาน (ผู้ใช้ที่ login อยู่จะถูกส่งไป dashboard ของตนเอง)
- **Parcel Tracking** (`/tracking`) - ติดตามพัสดุด้วยรหัส รองรับหลายรหัสพร้อมกัน (สูงสุด 10), ไทม์ไลน์สถานะ, แชร์ลิงก์ `?q=CODE1,CODE2` (เส้นทางเดิม `/web` redirect มาที่นี่)

### Admin (`/admin/*`)
- **Orders & Order Receive** - สร้างใบสั่งงานขนส่ง บันทึกรายการผู้รับหลายรายการต่อออเดอร์ และลบรายการรับงานได้
- **Contacts** - จัดการข้อมูลผู้ติดต่อ พร้อม API สำหรับค้นหา/แนะนำข้อมูลผู้ติดต่อ
- **Dashboard** - สรุปยอดพัสดุ รายรับ COD สถานะจัดส่ง สถานะรอบขนส่ง และรายการล่าสุด
- **Trip Management** - สร้างรอบจัดส่ง มอบหมายพัสดุเข้ารอบ เริ่มรอบ ปิดรอบ ยกเลิกรอบ และแก้ไขข้อมูลรอบ
- **Driver Management** - ฐานข้อมูลคนขับรถ (master data) ที่ `/admin/drivers`: CRUD ค้นหา/กรอง ผูกบัญชี login (สร้างใหม่/ผูกบัญชีเดิม), รีเซ็ตรหัสผ่าน, ปิด/เปิดใช้งาน (ซิงก์สถานะบัญชี login), สถิติการจัดส่ง และตอนสร้างรอบเลือกคนขับจุดเดียวด้วย Select2 พร้อมสถานะ ว่าง/ไม่ว่าง ของวันนั้น (staff ดูได้, admin จัดการได้)
- **COD & Cost Tracking** - บันทึกยอดเก็บเงินปลายทาง ยอดที่เก็บได้ ต้นทุนรอบจัดส่ง และสรุปกำไร/ขาดทุน
- **Parcel Labels & QR** - พิมพ์ใบปะหน้าพัสดุจากออเดอร์หรือรอบจัดส่ง พร้อม QR สำหรับติดตามพัสดุ
- **Parcel Tracking (ภายใน)** - ค้นหา/ดูไทม์ไลน์พัสดุจากรหัสพัสดุ และบันทึกประวัติการแจ้งเตือนลูกค้า
- **Server-side DataTables** - ตารางหลัก (ออเดอร์, รอบจัดส่ง, เลือกพัสดุเข้ารอบ, พัสดุในรอบ, ค้นหาพัสดุ) โหลด/ค้นหา/เรียงผ่าน AJAX endpoint `*/data`
- **CSV Export** - ส่งออกรายการรอบจัดส่ง รายการพัสดุในรอบ และสรุป COD เป็น CSV

### Driver Portal (`/driver/*`)
- มุมมอง mobile-first ของพนักงานขับรถ: รายการรอบที่ได้รับมอบหมาย เริ่มรอบ อัปเดตสถานะจัดส่ง/การเก็บเงิน COD และส่งยอดปิดรอบ — เห็นเฉพาะรอบของตนเอง

### อื่นๆ
- **Location API** - API จังหวัด อำเภอ และตำบล สำหรับฟอร์มที่อยู่

## Tech Stack

- **Backend:** Laravel 9, PHP 8.0.2+
- **Auth/API:** Laravel Sanctum
- **Frontend:** Laravel Mix 6, Webpack 5, Sass
- **UI (admin/driver):** AdminLTE 3.1, Bootstrap 4.6, jQuery 3.6
- **UI (public pages):** Vue 3 SPA แยก bundle ต่อหน้า (`public/js/landing.js`, `public/js/tracking.js`)
- **Database:** MySQL
- **Docker:** PHP 8.1 Apache, MySQL 8.0

ชื่อแบรนด์ทั้งระบบอ่านจาก `APP_NAME` ใน `.env` (`config('app.name')`) — เปลี่ยนชื่อบริษัทได้ที่จุดเดียว

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

ตั้งค่า `.env` ให้ตรงกับฐานข้อมูลที่ใช้งาน จากนั้น:

```bash
php artisan migrate
php artisan db:seed
npm run dev
php artisan serve
```

เปิดแอปที่ `http://localhost:8000`

> **ความปลอดภัย:** seeder สร้างบัญชีและข้อมูลตัวอย่างสำหรับ **พัฒนาเท่านั้น** (ดูได้ใน `database/seeders/`) — ห้ามรัน seeder บนระบบจริง และต้องเปลี่ยนรหัสผ่านทุกบัญชีก่อนใช้งานจริง

## Docker

```bash
docker compose up -d --build
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

เปิดแอปที่ `http://localhost:8000`

ค่าเชื่อมต่อฐานข้อมูลกำหนดใน `docker-compose.yml` / `.env` (MySQL expose ที่ host port `33306`) — ค่าเริ่มต้นเหมาะกับเครื่อง dev เท่านั้น เปลี่ยนทั้งหมดก่อนนำไป deploy

คำสั่งที่ใช้บ่อย:

```bash
docker compose logs app
docker compose exec -e APP_ENV=testing app php artisan test
docker compose down
```

## Frontend Assets

- Sass หลัก: `resources/sass/app.scss` (ธีมแอดมินอยู่ใน `_modern-ui.scss`, driver portal ใน `_driver.scss`, หน้า login ใน `_login.scss`)
- Vue public pages: `resources/js/landing/`, `resources/js/tracking/` — แต่ละหน้าเป็น Mix entry แยกของตัวเอง

```bash
npm run dev
npm run watch
npm run prod
```

หลีกเลี่ยงการแก้ไฟล์ที่ build แล้ว (`public/css/*`, `public/js/*.js`, `public/mix-manifest.json`) โดยตรง ให้แก้ source แล้ว build ใหม่แทน

## Main Web Routes

### Public

| Method | Path | Description |
| --- | --- | --- |
| GET | `/` | Landing page (guest); ผู้ใช้ที่ login จะ redirect ไป dashboard ตาม role |
| GET | `/tracking` | หน้าติดตามพัสดุ รองรับ `?q=CODE1,CODE2` |
| GET | `/web` | เส้นทางเดิม — 301 redirect ไป `/tracking` (คง query string) |
| GET/POST | `/login` | หน้าเข้าสู่ระบบและ action login |
| POST | `/logout` | ออกจากระบบ |

### Admin

| Method | Path | Description |
| --- | --- | --- |
| GET | `/admin/dashboard` | Dashboard และสถิติการจัดส่ง |
| GET/POST | `/admin/orders` | รายการออเดอร์และสร้างออเดอร์ |
| GET | `/admin/orders/{order}/labels` | พิมพ์ label ของออเดอร์ |
| DELETE | `/admin/order-receive/{id}` | ลบรายการรับพัสดุ |
| GET/POST | `/admin/contacts` | จัดการข้อมูลผู้ติดต่อ |
| GET/POST | `/admin/users` | จัดการผู้ใช้งาน เฉพาะ admin |
| GET | `/admin/drivers` | รายการคนขับรถ (admin, staff) |
| GET/POST | `/admin/drivers/create`, `/admin/drivers` | สร้างคนขับ + ผูกบัญชี login (admin) |
| GET/PUT/DELETE | `/admin/drivers/{driver}` | ดู/แก้ไข/ลบคนขับ (ดู: admin+staff, แก้/ลบ: admin) |
| POST | `/admin/drivers/{driver}/toggle-status` | เปิด/ปิดใช้งานคนขับ + ซิงก์บัญชี login (admin) |
| POST | `/admin/drivers/{driver}/reset-password` | รีเซ็ตรหัสผ่านบัญชีคนขับ (admin) |
| GET | `/admin/api/drivers/availability` | สถานะ ว่าง/ไม่ว่าง ของคนขับตามวันที่ (admin, staff) |
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

ตารางหลักของแอดมิน (ออเดอร์, รอบจัดส่ง, เลือกพัสดุเข้ารอบ, พัสดุในรอบ, ค้นหาพัสดุ) มี endpoint คู่กันรูปแบบ `GET .../data` สำหรับ DataTables server-side

### Driver Portal

| Method | Path | Description |
| --- | --- | --- |
| GET | `/driver` | หน้ารายการรอบขนส่งของคนขับ |
| GET | `/driver/trips/{trip}` | หน้ารายละเอียดรอบขนส่ง mobile สำหรับคนขับ |
| POST | `/driver/trips/{trip}/start` | คนขับเริ่มรอบขนส่งที่ได้รับมอบหมาย |
| POST | `/driver/trips/{trip}/submit` | คนขับส่งยอดและเปลี่ยนรอบเป็นรอตรวจสอบ |
| POST | `/driver/trip-items/{tripItem}/delivery-status` | คนขับอัปเดตสถานะจัดส่งพัสดุของตนเอง |
| POST | `/driver/trip-items/{tripItem}/payment-status` | คนขับบันทึกยอดเก็บเงิน COD ของตนเอง |

Role `driver` ใช้สำหรับบัญชีคนขับรถ หลังเข้าสู่ระบบจะถูกส่งไปที่ `/driver` และเห็นเฉพาะรอบขนส่งที่ `trips.driver_user_id` ตรงกับบัญชีของตนเอง

## API Routes

API สาธารณะ (ไม่ต้อง auth):

| Method | Path | Description |
| --- | --- | --- |
| GET | `/api/track?codes[]=...` | สถานะ + ไทม์ไลน์พัสดุตามรหัส (สูงสุด 10 รหัสต่อครั้ง) |
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
draft -> assigned -> in_transit -> pending_verification -> completed
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

- Seeder หลักรัน `LocationSeeder`, `UserSeeder`, `DriverSeeder` และ `SampleDataSeeder` (`DriverSeeder` สร้างคนขับตัวอย่าง 3 คน — ผูกบัญชี 2 / ไม่มีบัญชี 1)
- ตารางรอบจัดส่งประกอบด้วย `trips`, `trip_items`, `trip_costs`, `parcel_status_logs`, และ `parcel_notifications`
- ตาราง `drivers` เป็น master data ของคนขับรถ (มี `code` รูปแบบ `DRV-0001`, `mobile` unique, `user_id` unique → `users`); `trips` เพิ่มคอลัมน์ `driver_id` (FK → `drivers`) โดยยังคงคอลัมน์ snapshot เดิม (`driver_name`, `driver_mobile`, `car_id`, `area_name`, `driver_user_id`) ไว้ครบ
- Backfill ข้อมูลคนขับเดิม: `php artisan drivers:backfill` (idempotent, มี `--dry-run`) สร้างแถว `drivers` ให้ทุก user role `driver` และเติม `trips.driver_id` จาก `driver_user_id`
- `order_receives` รองรับทั้ง `parcel_price` และชื่อเดิม `parcel_pice`; model จะ sync ค่าระหว่างสอง field เพื่อรองรับโค้ดเก่าและโค้ดใหม่
- Migration ล่าสุดมี index เพิ่มเติมสำหรับ `order_receives` และ `trips` (`driver_id, trip_date`) เพื่อช่วยงานค้นหาและ dashboard

## Testing & Validation

```bash
# ใน Docker
docker compose exec -e APP_ENV=testing app php artisan test
docker compose exec app php artisan route:list

# Assets (บน host)
npm run dev
npm run prod
```

ถ้าเปลี่ยน migration หรือ seed ให้ทดสอบ lifecycle ฐานข้อมูลที่เกี่ยวข้อง เช่น `php artisan migrate:fresh --seed` หรือคำสั่ง Docker เทียบเท่า

## License

MIT
