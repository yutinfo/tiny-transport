# TA Transport 2017

ระบบจัดการขนส่ง (Transport Management System) พัฒนาด้วย Laravel 9 พร้อม AdminLTE UI

## Features

- **Orders** — สร้าง แก้ไข และติดตามใบสั่งงานขนส่ง พร้อมระบบรับงาน (Order Receive)
- **Contacts** — จัดการข้อมูลติดต่อ (CRUD)
- **Users** — จัดการผู้ใช้งานในระบบ
- **Dashboard** — ภาพรวมและรายงาน
- **REST API** — API endpoint สำหรับ Order Receive และ Contact

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

## Routes

| Method | Path | Description |
|--------|------|-------------|
| GET | `/ta-admin/dashboard` | Dashboard |
| GET/POST | `/ta-admin/orders` | จัดการใบสั่งงาน |
| DELETE | `/ta-admin/order-receive/{id}` | ลบรายการรับงาน |
| GET/POST | `/ta-admin/contacts` | จัดการข้อมูลติดต่อ |
| GET/POST | `/ta-admin/users` | จัดการผู้ใช้งาน |

## API

| Method | Path | Description |
|--------|------|-------------|
| DELETE | `/api/order-receive/{id}` | ลบ Order Receive |
| GET | `/api/contacts` | รายการ Contact |

## License

MIT
