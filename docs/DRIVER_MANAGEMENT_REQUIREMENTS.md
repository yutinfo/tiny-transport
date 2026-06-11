# Requirement — ระบบจัดการคนขับรถ (Driver Management)

> สถานะ: **ข้อกำหนด (Requirement) — ยังไม่เริ่มพัฒนา**
> วันที่จัดทำ: 2026-06-11
> ขอบเขต: Master data คนขับรถ + ผูกบัญชี login + เลือกคนขับตอนสร้างรอบขนส่งพร้อมสถานะ ว่าง/ไม่ว่าง

---

## 1. สรุปย่อ (TL;DR)

สร้างตาราง **`drivers`** เป็น master data ของคนขับรถ (ชื่อ เบอร์โทร ทะเบียนรถ ใบขับขี่
พื้นที่ประจำ สถานะ) พร้อมหน้า CRUD ที่ `/admin/drivers` — คนขับแต่ละคน **ผูกบัญชี
login ได้ 1 บัญชี** (`users.role_name='driver'`) เพื่อเข้า Driver Portal ส่วนหน้า
สร้าง/แก้ไขรอบขนส่งเปลี่ยนเป็น **เลือกคนขับจาก dropdown (Select2) ตัวเดียว** ระบบ
auto-fill ชื่อ/เบอร์/ทะเบียนรถให้ และแสดงสถานะ **🟢 ว่าง / 🔴 มีรอบแล้ว** ของวันนั้นๆ
แบบ real-time ตามวันที่ที่เลือก

---

## 2. สถานะปัจจุบัน (As-is)

| เรื่อง | ปัจจุบัน | ปัญหา |
|---|---|---|
| ข้อมูลคนขับ | ไม่มีตาราง master — คนขับ = `users` ที่ `role_name='driver'` ([User.php](../app/Models/User.php)) | `users` ไม่มีเบอร์โทร / ทะเบียนรถ / ใบขับขี่ |
| ข้อมูลคนขับบนรอบขนส่ง | `trips` เก็บ `driver_name`, `driver_mobile`, `car_id`, `area_name` เป็น text กรอกมือ + `driver_user_id` (nullable FK → users) | admin ต้องพิมพ์เบอร์/ทะเบียนรถซ้ำทุกครั้ง พิมพ์ผิดได้ ข้อมูลไม่ consistent |
| ฟอร์มสร้างรอบ | dropdown เลือก "บัญชีคนขับ" + ช่อง text แยกอีก 3 ช่อง ([form.blade.php](../resources/views/admin/trip/form.blade.php)) | ซ้ำซ้อน สับสน (เลือกบัญชีแล้วยังต้องกรอกชื่ออีก) |
| สถานะว่าง/ไม่ว่าง | **ไม่มี** | ไม่รู้ว่าคนขับคนไหนมีรอบในวันนั้นแล้ว อาจ assign ซ้ำซ้อน |
| การสร้างบัญชีคนขับ | ผ่านหน้า Users (`/admin/users`, admin เท่านั้น) แยกจากบริบทงานขนส่ง | สร้างบัญชีกับข้อมูลคนขับคนละที่ |
| Driver Portal | `/driver/*` scope ด้วย `trips.driver_user_id` (ห้ามกระทบ — ความเสี่ยงอันดับ 1 ของระบบ) | — |

ของที่มีอยู่แล้วและจะใช้ต่อ: Select2 (`public/plugins/select2` ของ AdminLTE — ไม่ต้องลง lib เพิ่ม),
middleware `role:admin,staff`, แพทเทิร์น CRUD จากหน้า Contacts/Users

---

## 3. เป้าหมาย / นอกขอบเขต

**เป้าหมาย**
1. มี master data คนขับรถ ครบ CRUD ค้นหา/กรองได้
2. คนขับผูกบัญชี login ได้ (สร้างใหม่จากหน้าคนขับ หรือผูกบัญชี driver เดิม) — บัญชีละ 1 คนขับ
3. สร้าง/แก้ไขรอบขนส่ง: เลือกคนขับจุดเดียว auto-fill ทุกช่อง + เห็นว่า **ว่าง/ไม่ว่าง** ในวันนั้น
4. ของเดิมต้องไม่พัง: Driver Portal, รอบขนส่งเก่า, รายงาน/CSV ใช้ได้เหมือนเดิม

**นอกขอบเขต (รอบนี้ไม่ทำ)**
- ตาราง "รถ" แยก (vehicles) — เก็บทะเบียนรถประจำตัวไว้ที่คนขับพอ
- ระบบกะ/ตารางเวร, GPS, การคิดค่าเที่ยวรายคนขับ
- Mobile app แยก (ใช้ Driver Portal เดิม)

---

## 4. การออกแบบข้อมูล

### 4.1 ตารางใหม่ `drivers`

| คอลัมน์ | ชนิด | กฎ |
|---|---|---|
| `id` | bigint PK | |
| `code` | string(20) unique | รหัสคนขับ auto-gen `DRV-0001`, `DRV-0002`, … |
| `name` | string(100) | **required** |
| `last_name` | string(100) nullable | |
| `mobile` | string(10) | **required**, ตัวเลข 9–10 หลัก |
| `license_plate` | string(20) nullable | ทะเบียนรถประจำตัว |
| `driver_license_no` | string(20) nullable | เลขใบขับขี่ |
| `area_name` | string(100) nullable | พื้นที่วิ่งประจำ (auto-fill ลงรอบขนส่ง) |
| `note` | text nullable | |
| `status` | enum `active` / `inactive` (default `active`) | inactive = ไม่แสดงใน dropdown |
| `user_id` | FK → users, nullable, **unique** | บัญชี login (1 บัญชี : 1 คนขับ) |
| `created_by` / `updated_by` | string nullable | ตามแพทเทิร์น trips |
| timestamps | | |

### 4.2 แก้ตาราง `trips`

- เพิ่ม `driver_id` (FK → drivers, nullable) + index `(driver_id, trip_date)`
- **คงคอลัมน์เดิมทั้งหมด** (`driver_name`, `driver_mobile`, `car_id`, `driver_user_id`)
  เป็น snapshot ณ วันจัดรอบ — ประวัติเก่าไม่เพี้ยนแม้ข้อมูลคนขับถูกแก้ทีหลัง
- เมื่อเลือกคนขับ ระบบ set ให้อัตโนมัติ: `driver_id`, `driver_user_id` (= `drivers.user_id`),
  `driver_name`, `driver_mobile`, `car_id`, `area_name` (แก้ทับรายรอบได้ เช่น วันนั้นใช้รถคันอื่น)

### 4.3 Backfill ข้อมูลเดิม (artisan command ครั้งเดียว)

1. ทุก `users` ที่ `role_name='driver'` → สร้างแถว `drivers` (ดึง `mobile`/`license_plate`
   จาก trip ล่าสุดของคนนั้นถ้ามี, ไม่มีให้เว้นว่างแล้วแอดมินเติมทีหลัง)
2. ทุก `trips` ที่มี `driver_user_id` → set `driver_id` ตามข้อ 1
3. Command ต้อง idempotent (รันซ้ำไม่สร้างซ้ำ) และมี `--dry-run`

---

## 5. ฟีเจอร์

### F1 — หน้า CRUD คนขับรถ (`/admin/drivers`)

**รายการ (index)**
- ตาราง: รหัส, ชื่อ-นามสกุล, เบอร์โทร (กดโทรได้ `tel:`), ทะเบียนรถ, พื้นที่,
  บัญชี login (badge `มี` / `ไม่มี`), สถานะ, จำนวนรอบทั้งหมด, ปุ่มดู/แก้ไข
- ค้นหา: ชื่อ/เบอร์/ทะเบียนรถ/รหัส · กรอง: สถานะ, มี/ไม่มีบัญชี · pagination 20 แถว

**สร้าง / แก้ไข**
- ฟิลด์ตามตาราง §4.1 + ส่วน "บัญชีเข้าสู่ระบบ" (ดู F2)
- `code` gen อัตโนมัติ แก้ไม่ได้

**ดูรายละเอียด (show)**
- ข้อมูลคนขับ + สถิติ: รอบทั้งหมด, รอบเดือนนี้, ส่งสำเร็จ %, COD เก็บสะสม
- ตารางประวัติรอบขนส่งล่าสุด (ลิงก์ไปหน้ารอบ) + ตารางวันที่มีงานช่วง 7 วันข้างหน้า

**ลบ / ปิดใช้งาน**
- มีรอบขนส่งผูกอยู่ → **ลบไม่ได้** แจ้งให้ "ปิดใช้งาน" แทน
- ไม่มีรอบเลย → ลบได้ (confirm ก่อน; ถ้ามีบัญชี login ให้เลือกว่าจะลบบัญชีด้วยหรือคงไว้)
- ปิดใช้งาน: หายจาก dropdown สร้างรอบใหม่ + บัญชี login ที่ผูกถูก set `inactive`
  (เข้าระบบไม่ได้) — มี confirm และข้อความแจ้งผลทั้งสองอย่างชัดเจน

### F2 — บัญชีเข้าสู่ระบบของคนขับ

- ในฟอร์มคนขับ มี 3 ทางเลือก:
  1. **ไม่มีบัญชี** — คนขับชั่วคราว/รายวัน ยังถูก assign รอบได้ แต่ไม่เห็น Driver Portal
  2. **สร้างบัญชีใหม่** — กรอก username / email / password → ระบบสร้าง `users` ให้โดย
     `role_name='driver'`, `status='active'` เสมอ (ห้ามเลือก role อื่น)
  3. **ผูกบัญชี driver ที่มีอยู่** — dropdown แสดงเฉพาะ users role driver ที่ยังไม่ถูกผูก
- ปลดการผูก / เปลี่ยนบัญชีได้จากหน้าแก้ไข (มี confirm — รอบเก่าไม่เปลี่ยน `driver_user_id`,
  มีผลเฉพาะรอบที่สร้างใหม่)
- ปุ่ม **รีเซ็ตรหัสผ่าน** ในหน้าแก้ไขคนขับ (ตั้งรหัสใหม่ + confirm)
- กติกาความสอดคล้อง: ปิดใช้งานคนขับ → user inactive · เปิดใช้งานคนขับ → user กลับ active
  · แก้ user role จาก driver เป็นอื่นที่หน้า Users ขณะยังถูกผูก → ห้าม (แจ้ง error)

### F3 — เลือกคนขับตอนสร้าง/แก้ไขรอบขนส่ง

แก้ [form.blade.php](../resources/views/admin/trip/form.blade.php):
- แทน dropdown + 3 ช่อง text ด้วย **Select2 ตัวเดียว** "คนขับรถ" — ค้นหาด้วยชื่อ/เบอร์/ทะเบียนรถ
- แต่ละ option แสดง: `ชื่อ (ทะเบียนรถ · เบอร์)` + badge ว่าง/ไม่ว่าง (ดู F4)
- เลือกแล้ว auto-fill: ชื่อ, เบอร์, ทะเบียนรถ, พื้นที่ ลงช่องด้านล่าง (ยัง**แก้ทับได้**รายรอบ
  เช่น สลับรถ) — ช่องเหล่านี้ยุบเป็น read-mostly แสดงใต้ dropdown
- กรณีพิเศษ: ยังเลือก "ไม่ระบุคนขับ" แล้วกรอกมือได้ (เท่ากับพฤติกรรมเดิม — รองรับคนขับ
  outsource ที่ไม่อยากเก็บเป็น master) และหน้าแก้ไขรอบที่คนขับถูกปิดใช้งานไปแล้ว
  ต้องยังแสดงคนเดิมได้ (ใส่ option พิเศษ disabled-but-selected)

### F4 — สถานะ ว่าง / ไม่ว่าง (Availability)

**นิยาม**: คนขับ "**ไม่ว่าง**" ในวันที่ D เมื่อมีรอบขนส่งที่ `trip_date = D` และ
`status ∉ {cancelled}` อย่างน้อย 1 รอบ (รวม draft/assigned/in_transit/pending_verification/completed
— มีรอบในวันนั้นแล้วถือว่าไม่ว่าง ตามนิยามของธุรกิจ)

- **API**: `GET /admin/api/drivers/availability?date=YYYY-MM-DD&exclude_trip={id?}`
  → `[{driver_id, busy, trips: [{code, status_label}]}]`
  (`exclude_trip` ใช้ตอนแก้ไขรอบ — ไม่นับรอบตัวเอง)
- **UI ฟอร์มรอบขนส่ง**: โหลด/เปลี่ยน `trip_date` → ยิง API แล้วอัปเดต badge ใน Select2:
  🟢 `ว่าง` / 🔴 `มีรอบแล้ว (RUN-xxxx)`
- เลือกคนที่ไม่ว่าง → **เตือนแบบ confirm ไม่ใช่ block** ("คนขับมีรอบ RUN-xxxx ในวันนี้แล้ว
  ยืนยันจัดรอบซ้อน?") เพราะธุรกิจจริงคนขับวิ่งได้มากกว่า 1 รอบ/วัน
- ฝั่ง server ตรวจซ้ำตอน store/update (กันยิงตรง): ถ้าไม่ว่างและไม่ได้ส่ง flag
  `confirm_busy=1` มา → ตอบ validation error เดิมกลับไปให้กดยืนยัน
- หน้า index คนขับ มีคอลัมน์ "วันนี้": ว่าง / มีรอบ (ลิงก์ไปรอบนั้น)

### F5 — เมนูและจุดเชื่อมโยง

- เพิ่มเมนู sidebar "**คนขับรถ**" (ไอคอน `fa-id-card` หรือ `fa-truck`) ใต้กลุ่มรอบขนส่ง
- หน้า trip show: ชื่อคนขับเป็นลิงก์ไปหน้า driver show (ถ้ามี `driver_id`)
- Seeder: คนขับตัวอย่าง 3 คน (มีบัญชี 2 / ไม่มี 1) สำหรับ dev

---

## 6. Routes และสิทธิ์

| Method | Path | ชื่อ route | สิทธิ์ |
|---|---|---|---|
| GET | `/admin/drivers` | `admin.drivers.index` | admin, staff |
| GET | `/admin/drivers/create` | `admin.drivers.create` | **admin** |
| POST | `/admin/drivers` | `admin.drivers.store` | **admin** |
| GET | `/admin/drivers/{driver}` | `admin.drivers.show` | admin, staff |
| GET | `/admin/drivers/{driver}/edit` | `admin.drivers.edit` | **admin** |
| PUT | `/admin/drivers/{driver}` | `admin.drivers.update` | **admin** |
| DELETE | `/admin/drivers/{driver}` | `admin.drivers.destroy` | **admin** |
| POST | `/admin/drivers/{driver}/reset-password` | `admin.drivers.reset-password` | **admin** |
| GET | `/admin/api/drivers/availability` | `admin.api.drivers.availability` | admin, staff |

เหตุผล: staff ต้องเห็นรายชื่อ/ความว่างเพื่อจัดรอบ แต่การจัดการตัวตน + บัญชี login เป็นเรื่อง
sensitive ให้ admin เท่านั้น (สอดคล้องกับ `/admin/users` เดิมที่เป็น `role:admin`)

**ห้ามกระทบ**: `routes/driver.php` และ access control ของ Driver Portal — ไม่แตะเลย

---

## 7. ไฟล์ที่จะเกิดขึ้น / ถูกแก้ (ประมาณการ)

```
สร้างใหม่
  database/migrations/xxxx_create_drivers_table.php
  database/migrations/xxxx_add_driver_id_to_trips_table.php
  app/Console/Commands/BackfillDrivers.php
  app/Models/Driver.php
  app/Http/Controllers/DriverController.php          ← ระวังชื่อชนกับ DriverTripController (portal) — คนละไฟล์
  app/Services/DriverService.php                      (สร้าง/ผูกบัญชี, availability, กติกาลบ)
  resources/views/admin/drivers/{list,create,edit,show,form}.blade.php
  database/seeders/DriverSeeder.php
  tests/Feature/DriverManagementFeatureTest.php
  tests/Feature/DriverAvailabilityFeatureTest.php
แก้ไข
  routes/admin.php                                    (กลุ่ม drivers + api availability)
  app/Http/Controllers/TripController.php             (drivers จากตารางใหม่, applySelectedDriver, ตรวจ busy)
  app/Services/TripService.php                        (รับ driver_id)
  resources/views/admin/trip/form.blade.php           (Select2 + auto-fill + badge)
  resources/views/layouts/app.blade.php (sidebar)     (เมนูคนขับรถ)
  app/Http/Controllers/UserController.php             (กันแก้ role ของ user ที่ถูกผูก)
  README.md                                           (ฟีเจอร์ + role ใหม่)
```

---

## 8. Validation หลัก

- `name` required · `mobile` required, `regex:/^\d{9,10}$/`
- `mobile` unique ในตาราง drivers (เตือนกันสร้างคนซ้ำ — override ไม่ได้)
- บัญชีใหม่: `username` required+unique(users), `password` required|min:8|confirmed,
  `email` nullable|email|unique(users)
- `user_id` ที่ผูก: ต้อง `role_name='driver'` และยังไม่ถูกคนขับคนอื่นผูก (unique constraint + ตรวจใน request)
- `driver_id` บนฟอร์มรอบ: `exists:drivers,id` และ `status='active'` (ยกเว้นค่าเดิมของรอบที่กำลังแก้ไข)

---

## 9. ผลกระทบต่อระบบเดิม

| จุด | ผลกระทบ | การรับมือ |
|---|---|---|
| Driver Portal `/driver/*` | ไม่มี — ยัง scope ด้วย `driver_user_id` เหมือนเดิม | ระบบใหม่แค่ "เติมค่า" `driver_user_id` ให้ถูกต้องอัตโนมัติ |
| รอบขนส่งเก่า | `driver_id` เป็น null จนกว่า backfill | command §4.3 |
| หน้า Users เดิม | ยังสร้าง user driver ตรงๆ ได้ (จะกลายเป็นบัญชี "ยังไม่ผูก" รอผูกจากหน้าคนขับ) | เพิ่ม hint ในหน้า Users + กันแก้ role ขณะถูกผูก |
| Filter รายการรอบ (`driver_name` text) | ใช้ได้เหมือนเดิม | ภายหลังค่อยอัปเกรดเป็น filter ด้วย `driver_id` (ไม่บังคับรอบนี้) |
| CSV export | คอลัมน์เดิมครบ (snapshot ยังอยู่) | — |

---

## 10. แผนทดสอบ (Feature tests)

1. admin สร้าง/แก้/ลบคนขับได้ · staff เปิดดู list/show ได้ แต่ create/edit/delete = 403
2. สร้างคนขับพร้อมบัญชี → users มีแถวใหม่ `role_name='driver'` และ login เข้า `/driver` ได้
3. ผูกบัญชี driver เดิมได้ · บัญชีที่ถูกผูกแล้วไม่โผล่ใน dropdown ผูกของคนอื่น
4. ปิดใช้งานคนขับ → user ที่ผูก login ไม่ได้ และไม่อยู่ใน dropdown รอบใหม่
5. ลบคนขับที่มีรอบ → ถูกปฏิเสธ · ไม่มีรอบ → ลบได้
6. Availability API: มีรอบ active วันนั้น → `busy=true` · รอบ cancelled → `busy=false` ·
   `exclude_trip` ไม่นับรอบตัวเอง · ต้อง login (guest = 401/redirect)
7. สร้างรอบด้วย `driver_id` → trips ได้ `driver_user_id`/`driver_name`/`driver_mobile`/`car_id` ครบ
8. สร้างรอบให้คนขับที่ไม่ว่างโดยไม่ส่ง `confirm_busy` → validation error · ส่งแล้ว → ผ่าน
9. Backfill command: idempotent (รัน 2 ครั้ง จำนวนแถวเท่าเดิม)
10. ของเดิม: `DriverParcelActionFeatureTest`, `TripOperationsFeatureTest`,
    `DriverHistoryProfileFeatureTest` ต้องผ่านครบโดยไม่แก้ behavior

---

## 11. แผนงานเป็นเฟส

| เฟส | งาน | ผลลัพธ์ที่ตรวจรับได้ |
|---|---|---|
| P1 | Migrations + Model + Backfill command + Seeder | `php artisan migrate` ผ่าน, backfill dry-run แสดงผลถูก |
| P2 | CRUD + ผูกบัญชี + reset password + เมนู sidebar | จัดการคนขับครบจาก UI, กติกาลบ/ปิดใช้งานทำงาน |
| P3 | Trip form ใหม่ (Select2 + auto-fill) + Availability API + ตรวจ busy ฝั่ง server | สร้างรอบโดยเลือกคนขับจุดเดียว เห็น 🟢/🔴 ตามวันที่ |
| P4 | หน้า driver show (สถิติ+ประวัติ) + ลิงก์เชื่อม + tests ทั้งชุด + README | tests เขียว, click-test ผ่าน |

**Acceptance สุดท้าย**: สร้างคนขับใหม่พร้อมบัญชี → login `/driver` ได้ → สร้างรอบวันพรุ่งนี้
เลือกคนนั้น (เห็น 🟢) → สร้างรอบที่สองวันเดียวกัน (เห็น 🔴 + confirm) → คนขับเห็นทั้ง
2 รอบใน portal → ปิดใช้งานคนขับ → login ไม่ได้ + ไม่โผล่ใน dropdown

---

## 12. ข้อตัดสินใจที่ปักไว้ (พร้อมเหตุผล)

1. **แยกตาราง `drivers` ไม่ยัดฟิลด์ใส่ `users`** — ข้อมูลรถ/ใบขับขี่ไม่ใช่ข้อมูล auth,
   รองรับคนขับที่ไม่มีบัญชี และไม่เสี่ยงกระทบระบบ login เดิม
2. **ไม่ว่าง = เตือน ไม่ใช่ห้าม** — ธุรกิจจริงวิ่งได้หลายรอบ/วัน แต่ต้อง confirm ทั้ง UI และ server
3. **คง snapshot บน trips** — ประวัติ/CSV เก่าไม่เพี้ยนเมื่อข้อมูลคนขับเปลี่ยน
4. **ไม่ลง lib ใหม่** — ใช้ Select2 ที่มากับ AdminLTE ใน `public/plugins/select2`
5. **ลบจริงได้เฉพาะคนขับที่ไม่มีประวัติรอบ** — ที่เหลือใช้ปิดใช้งาน (ไม่ใช้ soft delete
   เพราะทั้งโปรเจ็คไม่มีแพทเทิร์นนี้)
