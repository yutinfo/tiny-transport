# Tiny Transport — Codex Implementation Prompts

Repository: `https://github.com/yutinfo/tiny-transport`  
Project context: Laravel 9 + AdminLTE 3 transport management system. Existing core modules include Orders, OrderReceives, Contacts, Users, Dashboard, and basic APIs.

Goal: Extend the current system into a practical transport operation system with Delivery Run / Trip Management, parcel tracking, COD collection, dashboard, exports, QR/barcode support, contact reuse, and cost/profit reporting.

---

## How to Use This File

Use the prompts below sequentially in Codex.

Recommended workflow:

1. Create one branch per prompt or per phase.
2. Run tests / manual checks after each prompt.
3. Do not merge a later prompt before the previous core data model is stable.
4. Keep the existing tech stack: Laravel 9, Blade, AdminLTE 3, jQuery/AJAX, Bootstrap, MySQL.
5. Avoid large framework rewrites.
6. Preserve backward compatibility with existing order creation and edit flow.
7. Prefer small, reviewable commits.

Suggested branch names:

```bash
feature/trip-data-model
feature/trip-crud
feature/trip-status-workflow
feature/driver-view
feature/parcel-qr-export
feature/cost-profit-report
```

---

# Global Codex Rules

Use this rule block at the beginning of every Codex task if possible.

```text
You are working on the repository `yutinfo/tiny-transport`.

This is a Laravel 9 + AdminLTE 3 project. Keep the current stack and coding style. Do not rewrite the project to another framework. Do not introduce heavy frontend frameworks such as React/Vue unless already present. Prefer Blade, Bootstrap/AdminLTE components, jQuery/AJAX, Laravel controllers, Form Requests where helpful, Eloquent models, migrations, seeders, and feature tests.

Important existing domain:
- `orders` represents sender/order-level information.
- `order_receives` represents individual receiver/parcel items under an order.
- One order can have many order_receives.
- Existing parcel status fields include `delivery_status`, `payment_status`, `payment_type`, `parcel_pickup_type`.
- Existing price field is currently misspelled as `parcel_pice`; preserve compatibility unless explicitly instructed otherwise.
- Existing contacts are synced from sender/receiver data.

General implementation requirements:
- Keep existing routes and screens working.
- Add new routes under `/ta-admin/...`.
- Use auth middleware if the current admin routes use it.
- Add proper validation.
- Use DB transactions for multi-table writes.
- Use readable Thai labels in UI.
- Keep English names for code, classes, methods, migrations, and columns.
- Add indexes for common query fields.
- Add tests for important business rules where practical.
- Do not remove existing columns or break existing data.
- Use soft business validation rather than destructive changes.
- After implementation, summarize changed files, database migrations, new routes, and manual test steps.
```

---

# Prompt 01 — Data Model Foundation: Trips, Trip Items, Status Logs

## Objective

Add the database foundation for Delivery Run / Trip Management.

This is the base for all other features.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement the data model foundation for Delivery Run / Trip Management.

Context:
The current app has:
- `orders`
- `order_receives`
- `App\Models\Order`
- `App\Models\OrderReceive`
- `Order` already has many receivers.
- `OrderReceive` belongs to Order.
- Existing status fields include `delivery_status`, `payment_status`, `payment_type`, and `parcel_pickup_type`.
- Existing price column is misspelled as `parcel_pice`; do not remove it.

Required new tables:

1. `trips`
Columns:
- id
- code string unique
- trip_date date indexed
- driver_name nullable string
- driver_mobile nullable string
- car_id nullable string
- area_name nullable string
- status string indexed default `draft`
- total_parcels unsigned integer default 0
- total_cod_amount decimal(12,2) default 0
- collected_amount decimal(12,2) default 0
- started_at nullable timestamp
- completed_at nullable timestamp
- created_by nullable string
- updated_by nullable string
- timestamps

2. `trip_items`
Columns:
- id
- trip_id foreignId constrained trips cascadeOnDelete
- order_id foreignId constrained orders cascadeOnDelete
- order_receive_id foreignId constrained order_receives cascadeOnDelete
- parcel_code nullable string indexed
- delivery_status string indexed default `waiting`
- payment_status string indexed default `waiting`
- cod_amount decimal(12,2) default 0
- collected_amount decimal(12,2) default 0
- failed_reason nullable string
- note nullable text
- delivered_at nullable timestamp
- created_by nullable string
- updated_by nullable string
- timestamps

Constraints:
- unique pair: trip_id + order_receive_id

3. `parcel_status_logs`
Columns:
- id
- order_receive_id foreignId constrained order_receives cascadeOnDelete
- trip_id nullable foreignId constrained trips nullOnDelete
- from_status nullable string
- to_status string
- note nullable text
- created_by nullable string
- created_at timestamp nullable/useCurrent
- no updated_at required unless project convention prefers timestamps

Models:
- Add `App\Models\Trip`
- Add `App\Models\TripItem`
- Add `App\Models\ParcelStatusLog`

Relationships:
- Trip hasMany TripItem
- TripItem belongsTo Trip
- TripItem belongsTo Order
- TripItem belongsTo OrderReceive
- OrderReceive hasMany TripItem
- OrderReceive hasMany ParcelStatusLog
- ParcelStatusLog belongsTo OrderReceive
- ParcelStatusLog belongsTo Trip

Add constants or helper methods for statuses:
Trip statuses:
- draft
- assigned
- in_transit
- completed
- cancelled

Delivery statuses:
- waiting
- picked_up
- in_transit
- delivered
- failed
- returned

Payment statuses:
- waiting
- paid
- unpaid
- waived

Add Thai label helpers for statuses, for example `statusLabels()` and accessor if suitable.

Code generation:
- Trip code format: `RUN-YYYYMMDD-XXXX`, where XXXX is a running number for the day.
- Implement a static method or service method to generate the next trip code safely enough for current app usage.
- Avoid changing existing order code generation.

Also update existing models:
- Add `OrderReceive::tripItems()`
- Add `OrderReceive::statusLogs()`

Acceptance criteria:
- Migrations run successfully on a fresh database.
- Existing order screens still work.
- Models can create a Trip and TripItems from tinker.
- Trip item cannot duplicate the same order_receive_id in the same trip.
- Status label helpers return Thai labels.
- Provide a summary of changed files and manual test commands.
```

---

# Prompt 02 — Trip Service Layer and Business Rules

## Objective

Create a service layer so future controllers do not become too large.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Create a service layer for Trip / Delivery Run business logic.

Required service:
- `app/Services/TripService.php`

Responsibilities:
1. Create trip
2. Assign order_receive parcels to a trip
3. Recalculate trip totals
4. Start trip
5. Complete trip
6. Cancel trip
7. Update trip item delivery status
8. Update trip item payment collection
9. Create parcel status logs

Important business rules:
- A trip starts as `draft`.
- A trip can move:
  - draft -> assigned
  - assigned -> in_transit
  - in_transit -> completed
  - draft/assigned -> cancelled
- Do not allow completed/cancelled trips to be modified.
- A trip item can be added only when trip is draft or assigned.
- Do not allow the same order_receive_id to be assigned twice in the same trip.
- It is acceptable for a parcel to appear in another trip only if its previous trip item is failed or returned. Otherwise, block duplicate active assignment.
- When assigning a parcel:
  - copy `order_id`
  - copy `order_receive_id`
  - copy `parcel_code`
  - set cod_amount from `order_receives.parcel_pice` if payment_type is `on_delivery`; otherwise 0
  - set default statuses from current order_receive values if present, otherwise waiting
- Recalculate:
  - total_parcels = trip_items count
  - total_cod_amount = sum cod_amount
  - collected_amount = sum collected_amount
- When updating delivery status:
  - write to trip_items.delivery_status
  - also update order_receives.delivery_status to keep old screens consistent
  - create parcel_status_logs record
  - if status is delivered, set delivered_at
  - if status is failed, require failed_reason or note
- When updating payment:
  - update trip_items.payment_status
  - update trip_items.collected_amount
  - update order_receives.payment_status if field exists and app currently uses it
  - collected_amount cannot be greater than cod_amount unless explicitly marked as waived or note explains it
- Completing a trip:
  - all trip items must be in final delivery status: delivered, failed, or returned
  - failed items must have failed_reason or note
  - recalculate totals before completion
  - set completed_at
  - status = completed

Use DB transactions for write operations.

Add unit/feature tests where practical:
- Assign parcel to trip
- Prevent duplicate assignment
- Update status creates log
- Complete trip fails if item is still waiting
- Complete trip succeeds when all items are final

Acceptance criteria:
- Service has clean public methods.
- Controllers can call service without duplicating business rules.
- Existing order workflow remains working.
- Tests pass or manual test notes are provided if test setup is limited.
```

---

# Prompt 03 — Admin Trip CRUD Screens

## Objective

Add admin screens for creating and managing trips.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement Admin Trip CRUD screens using Laravel Blade + AdminLTE 3.

Routes under `/ta-admin/trips`:
- GET `/ta-admin/trips` => trip list
- GET `/ta-admin/trips/create` => create form
- POST `/ta-admin/trips` => store
- GET `/ta-admin/trips/{trip}` => detail
- GET `/ta-admin/trips/{trip}/edit` => edit form
- PUT/PATCH or POST `/ta-admin/trips/{trip}` => update
- POST `/ta-admin/trips/{trip}/start` => start trip
- POST `/ta-admin/trips/{trip}/cancel` => cancel trip
- POST `/ta-admin/trips/{trip}/complete` => complete trip

Controller:
- Create `TripController`
- Use `TripService` from the previous task for business operations.
- Keep controller thin.

Views:
- `resources/views/ta-admin/trip/list.blade.php`
- `resources/views/ta-admin/trip/create.blade.php`
- `resources/views/ta-admin/trip/edit.blade.php`
- `resources/views/ta-admin/trip/show.blade.php`

List page columns:
- trip_date
- code
- driver_name
- car_id
- area_name
- total_parcels
- total_cod_amount
- collected_amount
- status label
- action buttons

Filters:
- date from
- date to
- status
- driver name
- car id
- area name

Create/edit form:
- trip_date required
- driver_name nullable
- driver_mobile nullable with Thai mobile validation
- car_id nullable
- area_name nullable
- status should not be manually edited except via action buttons

Detail page:
- summary cards:
  - total parcels
  - delivered count
  - failed count
  - returned count
  - total COD
  - collected amount
  - remaining COD
- parcel table:
  - parcel_code
  - order code
  - receiver name
  - receiver mobile
  - address
  - cod_amount
  - collected_amount
  - delivery status
  - payment status
  - action buttons

UI requirements:
- Thai labels.
- Use AdminLTE cards, badges, tables, and buttons.
- Use Bootstrap badge colors for statuses.
- Keep design consistent with existing admin pages.
- Add navigation menu item: `รอบขนส่ง`.

Validation:
- trip_date required date
- driver_mobile nullable digits 9-10
- prevent editing completed/cancelled trips except viewing

Acceptance criteria:
- Admin can create/edit/list/view trips.
- Trip actions follow status rules.
- Completed/cancelled trips are read-only in UI.
- Existing admin menu and screens still work.
- Provide changed files and manual test steps.
```

---

# Prompt 04 — Assign Parcels to Trip

## Objective

Allow admin to select waiting parcels and assign them into a trip.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement parcel assignment into trips.

Add route:
- GET `/ta-admin/trips/{trip}/assign` => assign parcel screen
- POST `/ta-admin/trips/{trip}/assign-items` => assign selected parcels
- DELETE or POST `/ta-admin/trip-items/{tripItem}/remove` => remove item from trip if trip is draft/assigned

Assign screen:
- Show trip summary at top.
- Show searchable/filterable list of candidate `order_receives`.

Candidate parcel rules:
- Include order_receives with delivery_status waiting OR null.
- Exclude order_receives already assigned to an active trip item where status is not failed/returned and trip is not cancelled.
- Include receiver/order data using eager loading.
- Support filters:
  - created date from/to using orders.created_at
  - province_name
  - amphures_name
  - payment_type
  - pickup type
  - keyword search: parcel_code, receiver name, receiver mobile, order code

Table columns:
- checkbox
- parcel_code
- order code
- sender name
- receiver name
- receiver mobile
- destination address
- payment_type
- parcel_pickup_type
- parcel_pice
- created_at

Actions:
- Select multiple parcels and assign.
- On submit, use `TripService::assignItems`.
- After assign, redirect back to trip detail with success message.
- Display validation errors if some selected parcels cannot be assigned.

Trip detail page:
- Add button `เพิ่มพัสดุเข้ารอบ`.
- Add remove button per trip item if trip is draft/assigned.

Important:
- Do not duplicate business rule logic in controller; use TripService.
- Use pagination for candidate parcels.
- Use DB transactions.

Acceptance criteria:
- Admin can search and assign multiple parcels to a trip.
- Duplicate active assignment is blocked.
- Removing items recalculates trip totals.
- Completed/cancelled trip cannot assign/remove items.
- UI remains usable with many parcels via pagination.
```

---

# Prompt 05 — Trip Item Status, COD Collection, and Close Trip

## Objective

Make the trip detail page operational: update delivery status, collect COD, and close trip.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement operational actions on Trip Detail.

Routes:
- POST `/ta-admin/trip-items/{tripItem}/delivery-status`
- POST `/ta-admin/trip-items/{tripItem}/payment-status`
- POST `/ta-admin/trips/{trip}/complete`

Delivery status update:
Fields:
- delivery_status required in: waiting,picked_up,in_transit,delivered,failed,returned
- failed_reason required if delivery_status is failed
- note nullable

Behavior:
- Use TripService to update status.
- Update `trip_items.delivery_status`.
- Update related `order_receives.delivery_status`.
- Create `parcel_status_logs`.
- If delivered, set delivered_at.
- If failed/returned, keep payment unresolved unless explicitly set.
- Recalculate trip totals if necessary.

Payment update:
Fields:
- payment_status required in: waiting,paid,unpaid,waived
- collected_amount nullable numeric min 0
- note nullable

Behavior:
- Use TripService.
- If payment_status is paid, collected_amount should default to cod_amount if empty.
- If cod_amount is 0, payment_status can be paid or waived but collected_amount should be 0.
- Update related order_receive payment_status if column exists.
- Recalculate trip totals.

UI:
On trip detail page, each item should have:
- Delivery status select
- Failed reason field shown when failed
- Payment status select
- Collected amount input
- Save buttons
- Small status timeline link or expandable row if logs exist

Close trip behavior:
- Add a prominent `ปิดรอบขนส่ง` button.
- On complete, validate all trip items are final statuses:
  - delivered
  - failed
  - returned
- Failed items must have failed_reason or note.
- Recalculate totals.
- Mark trip completed and set completed_at.
- Show success or validation error list.

UX:
- Use normal form submit first.
- Optional AJAX enhancement is fine but not required.
- Show clear Thai error messages.
- Completed/cancelled trips must be read-only.

Acceptance criteria:
- Admin can update item delivery status.
- Admin can update COD/payment status.
- Status log is created every time delivery status changes.
- Trip cannot complete while any item is waiting/picked_up/in_transit.
- Trip completes correctly when all items are final.
```

---

# Prompt 06 — Parcel Timeline and Tracking Page

## Objective

Add tracking history for each parcel.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement parcel tracking timeline based on `parcel_status_logs`.

Routes:
- GET `/ta-admin/parcels/{orderReceive}/tracking` => admin tracking page
- Optional public-like route disabled by default or protected:
  - GET `/tracking/{parcel_code}`

Admin tracking page:
Show:
- parcel_code
- order code
- sender name/mobile
- receiver name/mobile
- destination address
- current delivery_status
- current payment_status
- current trip code if assigned
- timeline of parcel_status_logs ordered by created_at asc

Timeline UI:
Use AdminLTE timeline component if available.
Each timeline item should show:
- datetime
- from_status -> to_status
- Thai status label
- note
- created_by
- trip code if present

Also add a `ดูประวัติ` button from:
- trip detail parcel table
- order list parcel row if easy
- order edit page if easy

When a parcel is assigned to a trip, optionally create an initial status log:
- from_status null/current
- to_status waiting or assigned note
- note: `เพิ่มเข้ารอบขนส่ง {trip_code}`

Acceptance criteria:
- Admin can view parcel status history.
- Timeline uses Thai labels.
- Links from trip detail work.
- Existing order pages remain working.
```

---

# Prompt 07 — Driver Mobile-Friendly View

## Objective

Add a simple mobile-friendly page for drivers without building a mobile app.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement a mobile-friendly Driver View for a trip.

Routes:
- GET `/ta-admin/trips/{trip}/driver` => driver view
- POST `/ta-admin/driver/trip-items/{tripItem}/delivery-status`
- POST `/ta-admin/driver/trip-items/{tripItem}/payment-status`

Scope:
This is still under admin auth for now. Do not build a public unauthenticated driver portal unless instructed.

Driver View UI:
- Mobile-first layout.
- Show trip code, date, driver, car, area, status.
- Summary:
  - total parcels
  - delivered
  - failed
  - remaining
  - COD total
  - collected
- Parcel cards instead of wide table:
  - parcel_code
  - receiver name
  - receiver mobile clickable tel link
  - destination address
  - COD amount
  - delivery status badge
  - payment status badge
  - buttons:
    - โทร
    - เปิดแผนที่ using Google Maps query URL from address
    - ส่งสำเร็จ
    - ส่งไม่สำเร็จ
    - ตีกลับ
    - เก็บเงินแล้ว

For failed status:
- Show modal or inline form to choose failed_reason:
  - ติดต่อไม่ได้
  - ไม่มีผู้รับ
  - ที่อยู่ผิด
  - เลื่อนส่ง
  - ลูกค้าปฏิเสธรับ
  - อื่น ๆ

For payment:
- If COD > 0 and delivered, allow mark paid and input collected amount.
- Default collected_amount to cod_amount.

Technical:
- Reuse TripService.
- Keep completed/cancelled trips read-only.
- Use simple Blade and Bootstrap/AdminLTE responsive classes.
- Avoid heavy JS.

Acceptance criteria:
- Driver view works well on mobile width.
- Driver can update delivery status.
- Driver can mark COD paid.
- Phone and map links work.
- Business rules match admin status updates.
```

---

# Prompt 08 — Dashboard Enhancement

## Objective

Add operation KPIs to the dashboard.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Enhance the dashboard with transport operation KPIs.

Add dashboard cards for selected date range, default today:
- Trips today
- Parcels assigned today
- Delivered parcels
- Failed parcels
- Returned parcels
- Waiting/in transit parcels
- Total COD amount
- Collected COD amount
- Remaining COD amount
- Delivery success rate

Add charts if the project already has Chart.js or similar; otherwise keep cards and tables:
- Delivery status breakdown
- COD collection summary
- Trips by status

Add recent trips table:
- trip date
- trip code
- driver
- car
- total parcels
- delivered/failed/remaining
- COD collected
- status
- link to detail

Filters:
- date from
- date to
- driver
- status

Implementation:
- Keep existing dashboard route working.
- Query trips/trip_items efficiently.
- Use aggregate queries where practical.
- Add indexes only if missing and needed.
- Use Thai labels.
- Avoid breaking existing dashboard content; either append a new section or integrate cleanly.

Acceptance criteria:
- Dashboard shows meaningful operational KPIs.
- Default view shows today's data.
- Date filters work.
- Recent trips link to trip detail.
- Existing dashboard still loads.
```

---

# Prompt 09 — QR Code / Barcode and Parcel Labels

## Objective

Generate printable parcel labels and allow parcel lookup by code.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement QR code / barcode support for parcel labels.

Context:
`order_receives` already has `parcel_code`. Use it as the tracking identity.

Required features:
1. Parcel label page
2. Printable labels for one order or one trip
3. QR code generated from parcel_code or tracking URL
4. Search parcel by parcel_code

Routes:
- GET `/ta-admin/orders/{order}/labels` => print labels for all receivers in an order
- GET `/ta-admin/trips/{trip}/labels` => print labels for all trip items
- GET `/ta-admin/parcels/search` => search parcel form/result
- GET `/ta-admin/parcels/code/{parcelCode}` => redirect/show parcel detail/tracking

QR implementation:
- Use a lightweight Laravel-compatible QR package only if not already installed.
- If adding a package, update composer.json and document install command.
- If avoiding dependency is better, generate QR via simple SVG package or server-side library.
- Do not use external QR API services.

Label content:
- Parcel code
- QR code
- Sender name/mobile
- Receiver name/mobile
- Destination address
- Payment type
- COD amount if any
- Pickup/delivery type
- Created date

Print CSS:
- Create print-friendly Blade view.
- Labels should fit common A6/A7 style or multi-label A4 layout.
- Hide admin navigation when printing.
- Add `window.print()` button.

Parcel lookup:
- Search by parcel_code.
- Show parcel detail and tracking timeline link.
- If not found, show clear Thai message.

Acceptance criteria:
- Admin can print labels by order.
- Admin can print labels by trip.
- QR code is scannable.
- Searching parcel_code opens parcel detail/tracking.
- No external API dependency for QR generation.
```

---

# Prompt 10 — Contact Reuse and Smart Auto-Fill

## Objective

Improve order creation by reusing existing sender/receiver contacts.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Improve contact reuse in order creation and editing.

Context:
The app already has `contacts` and syncs sender/receiver data from orders.

Required features:
1. Search contact by name/mobile
2. Select contact to auto-fill sender form
3. Select contact to auto-fill receiver form
4. Show recent usage/history on contact detail if easy
5. Avoid duplicate contacts where possible

Routes/API:
- GET `/api/contacts/search?type=sender|receiver|both&q=...`
- Return JSON suitable for autocomplete:
  - id
  - type
  - name
  - mobile
  - address
  - province_id
  - amphure_id
  - district_id
  - province_name
  - amphure_name
  - district_name
  - zip_code

UI:
- On order create page:
  - Add sender contact search box.
  - Add receiver contact search box.
  - Selecting contact fills form fields.
- On order edit page:
  - Add same behavior if practical.
- Keep existing manual input working.
- Use jQuery autocomplete/select2 only if already installed or easy to add; otherwise use simple AJAX dropdown.

Duplicate handling:
- Current sync uses type + mobile. Improve carefully:
  - If mobile matches but type differs, consider type `both`.
  - Do not destroy existing records.
  - Normalize mobile by stripping non-digits.

Validation:
- Keep existing order validation.
- Do not make contact selection required.

Acceptance criteria:
- Admin can search contact by name/mobile.
- Selecting contact fills sender/receiver fields.
- Manual entry still works.
- Contact sync still works after order creation.
- Duplicate contact behavior is safer and documented.
```

---

# Prompt 11 — Delivery Cost and Profit per Trip

## Objective

Track trip-level cost and profit.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement Delivery Cost and Profit per Trip.

Add table: `trip_costs`
Columns:
- id
- trip_id foreignId constrained trips cascadeOnDelete
- type string indexed
- description nullable string
- amount decimal(12,2) default 0
- created_by nullable string
- updated_by nullable string
- timestamps

Cost types:
- fuel
- driver_wage
- toll
- parking
- maintenance
- other

Model:
- `TripCost`
- Trip hasMany TripCost

Trip detail page:
- Add Cost section.
- Add cost form:
  - type
  - description
  - amount
- Show cost table.
- Allow delete cost if trip is not completed/cancelled.
- Show summary:
  - revenue = sum parcel price or total COD + prepaid amount if available
  - total cost
  - estimated profit = revenue - total cost

Important:
The existing system has `parcel_pice` per order_receive and `parcel_total` on order.
Use a conservative calculation:
- trip revenue = sum related order_receives.parcel_pice for trip items
- total cost = sum trip_costs.amount
- estimated profit = revenue - total cost

Dashboard:
- Add optional cost/profit summary if not too invasive.

Validation:
- amount numeric min 0.01
- type required valid type

Acceptance criteria:
- Admin can add/list/delete trip costs.
- Trip detail shows revenue, cost, estimated profit.
- Completed/cancelled trips are read-only for cost changes.
- Calculations are clear and documented in UI.
```

---

# Prompt 12 — Export Excel/CSV/PDF for Trips and COD

## Objective

Add export reports for operation and accounting.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement exports for trips, parcel list, and COD summary.

Preferred:
- CSV export first because it is simple and reliable.
- Excel/PDF can be added only if package already exists or is easy to add.

Routes:
- GET `/ta-admin/trips/export/csv`
- GET `/ta-admin/trips/{trip}/items/export/csv`
- GET `/ta-admin/trips/{trip}/cod/export/csv`
- Optional PDF:
  - GET `/ta-admin/trips/{trip}/delivery-sheet`
  - GET `/ta-admin/trips/{trip}/cod-sheet`

Export 1: Trips summary CSV
Columns:
- trip_date
- trip_code
- driver_name
- car_id
- area_name
- status
- total_parcels
- total_cod_amount
- collected_amount
- remaining_amount
- completed_at

Support filters:
- date from
- date to
- status
- driver
- car_id

Export 2: Trip items CSV
Columns:
- trip_code
- parcel_code
- order_code
- sender_name
- sender_mobile
- receiver_name
- receiver_mobile
- destination_address
- delivery_status
- payment_status
- cod_amount
- collected_amount
- failed_reason
- delivered_at

Export 3: COD summary CSV
Columns:
- trip_code
- driver_name
- parcel_code
- receiver_name
- cod_amount
- collected_amount
- payment_status
- delivery_status

Implementation:
- Use streamed downloads for CSV.
- UTF-8 BOM for Thai compatibility with Excel.
- Thai column headers are preferred.
- Keep memory safe for large exports.
- Add export buttons on trip list/detail pages.

Acceptance criteria:
- CSV opens correctly in Excel with Thai text.
- Filters affect trips export.
- Trip item export works.
- COD export works.
- Existing screens still work.
```

---

# Prompt 13 — Basic API for Trips and Driver Operations

## Objective

Expose JSON APIs for future mobile app or external integration.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement basic JSON API endpoints for trips and parcel operations.

Keep authentication consistent with the existing API approach in this repository. If API auth is not clearly implemented, use existing middleware conventions and document the limitation.

Routes under `/api`:
- GET `/api/trips`
- GET `/api/trips/{trip}`
- GET `/api/trips/{trip}/items`
- POST `/api/trip-items/{tripItem}/delivery-status`
- POST `/api/trip-items/{tripItem}/payment-status`
- GET `/api/parcels/{parcelCode}`

Responses:
Use consistent JSON shape:
{
  "success": true,
  "data": {},
  "message": null
}

For errors:
{
  "success": false,
  "data": null,
  "message": "..."
}

Trip list filters:
- date_from
- date_to
- status
- driver_name
- car_id

Trip detail should include:
- trip fields
- summary counts
- items

Parcel detail should include:
- order
- order_receive
- current trip item if any
- status logs

Validation:
- Use Laravel validator/FormRequest.
- Return 422 JSON for validation errors.
- Return 403/400 when business rule fails.

Important:
- Reuse TripService.
- Do not duplicate logic between web and API controllers.

Acceptance criteria:
- API returns trip list/detail.
- API can update item status and payment.
- API returns useful validation errors.
- Business rules match admin UI.
```

---

# Prompt 14 — Data Cleanup: Introduce `parcel_price` Safely

## Objective

Fix the typo `parcel_pice` gradually without breaking old code.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Implement a backward-compatible migration from misspelled `parcel_pice` to `parcel_price`.

Important:
Do not remove `parcel_pice` yet because existing code uses it.

Steps:
1. Add nullable decimal column `parcel_price` to `order_receives`.
2. Backfill:
   - parcel_price = parcel_pice where parcel_price is null.
3. Update `OrderReceive` model fillable/casts to include `parcel_price`.
4. Add accessor/mutator/helper to keep compatibility:
   - When reading price, prefer parcel_price if not null, else parcel_pice.
   - When writing new records, write both parcel_price and parcel_pice for now.
5. Update new Trip-related code to use a helper method like `getParcelPriceValue()` instead of reading raw `parcel_pice`.
6. Carefully update order create/update code to write both fields if safe.
7. Do not break existing forms that submit `parcel_pice`.
8. Add comments/TODO explaining that `parcel_pice` is deprecated.

Acceptance criteria:
- Existing order create/edit still works.
- Old rows have parcel_price backfilled.
- New rows save both parcel_price and parcel_pice.
- Trip calculations use the new helper.
- No destructive schema changes.
```

---

# Prompt 15 — Tests, Security, Permissions, and UX Polish

## Objective

Stabilize the system after all major features.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Do a stabilization pass for the new Trip / Delivery Run features.

Areas to cover:

1. Tests
Add or improve tests for:
- create trip
- assign parcel
- prevent duplicate active assignment
- update delivery status
- update payment status
- complete trip validation
- cost/profit calculation
- exports return downloadable CSV

2. Authorization / access
- Ensure all `/ta-admin/trips...` pages require the same auth middleware as other admin pages.
- Ensure API routes use existing API auth conventions.
- Prevent unauthorized access if current project has role/permission logic.
- If no role system exists, document that auth middleware is the current protection.

3. Validation
Review validation messages and make them Thai-friendly.
Ensure:
- driver_mobile is valid
- collected_amount cannot be negative
- failed reason is required when failed
- completed/cancelled trips are read-only
- trip_date is required

4. Database performance
Check common queries:
- trip list by date/status
- candidate parcels by created date/province/payment status
- trip detail eager loading
Add indexes if needed.

5. UX polish
- Add status badges with consistent colors.
- Add confirmation dialogs for cancel/complete/remove.
- Add empty states for no trips/no parcels.
- Add clear success/error flash messages.
- Ensure mobile driver page is readable.
- Ensure print pages hide admin navigation.

6. Documentation
Update README with:
- New features
- Migration command
- Usage flow
- New routes overview
- Manual test checklist

Acceptance criteria:
- New features are protected by auth.
- Important business rules have tests or manual verification notes.
- README documents the Delivery Run feature.
- UI is consistent and usable.
- No obvious N+1 query issues in trip detail/list.
```

---

# Prompt 16 — Optional: LINE/SMS Notification Design Stub

## Objective

Prepare notification architecture without committing to a provider yet.

## Codex Prompt

```text
You are working on `yutinfo/tiny-transport`.

Prepare a notification architecture stub for future customer notifications.

Do not integrate a real SMS/LINE provider yet.

Add:
- `notifications` table or `parcel_notifications` table
- model `ParcelNotification`
- service `ParcelNotificationService`

Use cases:
- Notify customer when parcel is assigned to trip
- Notify customer when parcel is out for delivery
- Notify customer when delivered
- Notify customer when failed

Fields:
- order_receive_id
- channel enum/string: sms,line,email,manual
- recipient
- message
- status: pending,sent,failed,skipped
- provider_response nullable json/text
- sent_at nullable timestamp
- created_by nullable string
- timestamps

Service should support:
- createPendingNotification(orderReceive, channel, templateName)
- markSent
- markFailed
- skip

Add admin UI section in parcel tracking page:
- Show notification history.
- Add button to create manual notification log.

Important:
- Do not send real messages.
- Do not require external credentials.
- Keep provider implementation as TODO/interface.

Acceptance criteria:
- Notification logs can be created manually.
- Parcel tracking shows notification history.
- No external provider dependency.
- README explains this is a stub for future integration.
```

---

# Recommended Implementation Order

## Core MVP

1. Prompt 01 — Data Model Foundation
2. Prompt 02 — Trip Service Layer
3. Prompt 03 — Admin Trip CRUD
4. Prompt 04 — Assign Parcels
5. Prompt 05 — Status / COD / Close Trip

## Operation Upgrade

6. Prompt 06 — Parcel Timeline
7. Prompt 07 — Driver View
8. Prompt 08 — Dashboard
9. Prompt 12 — CSV Exports

## Business Upgrade

10. Prompt 09 — QR / Labels
11. Prompt 10 — Contact Reuse
12. Prompt 11 — Cost / Profit
13. Prompt 13 — API

## Cleanup / Future

14. Prompt 14 — `parcel_price` compatibility cleanup
15. Prompt 15 — Tests / Security / UX Polish
16. Prompt 16 — Notification Stub

---

# One-Shot Codex Prompt for MVP Only

Use this only if you want Codex to implement the core MVP in one large pass.

```text
You are working on `yutinfo/tiny-transport`, a Laravel 9 + AdminLTE 3 transport management system.

Implement the MVP for Delivery Run / Trip Management.

Must include:
1. Database tables:
   - trips
   - trip_items
   - parcel_status_logs
2. Models and relationships:
   - Trip
   - TripItem
   - ParcelStatusLog
   - update OrderReceive relationships
3. TripService with business rules:
   - create trip
   - assign parcels
   - prevent duplicate active assignment
   - update delivery status
   - update payment/COD
   - complete trip
   - recalculate totals
   - create status logs
4. Admin routes under `/ta-admin/trips`
5. Admin screens:
   - trip list
   - create/edit
   - detail
   - assign parcels
6. Trip item actions:
   - update delivery status
   - update payment status
   - failed reason
   - close trip
7. Dashboard summary section for trips today
8. Thai UI labels and validation messages
9. Tests or manual verification notes

Keep the existing stack. Do not rewrite the project. Do not remove or rename `parcel_pice`. Use it for compatibility. Keep existing order and contact screens working.

After finishing, provide:
- Changed files
- Migrations added
- Routes added
- Manual test checklist
- Known limitations
```

---

# One-Shot Codex Prompt for Full Feature Set

Use this only if you want Codex to attempt everything in a large implementation. This is riskier and should be done on a separate branch.

```text
You are working on `yutinfo/tiny-transport`, a Laravel 9 + AdminLTE 3 transport management system.

Implement the full Delivery Run / Transport Operation feature set.

Required modules:
1. Trip Management
2. Assign Parcels to Trip
3. Delivery Status Workflow
4. COD Collection
5. Trip Close Validation
6. Parcel Tracking Timeline
7. Driver Mobile-Friendly View
8. Dashboard KPIs
9. QR Code / Printable Parcel Labels
10. Contact Search and Auto-Fill
11. Trip Cost and Profit
12. CSV Exports
13. Basic Trip API
14. Backward-compatible `parcel_price` cleanup
15. Tests, security, and README update

Constraints:
- Keep Laravel 9 + AdminLTE 3.
- Use Blade, jQuery/AJAX, Bootstrap/AdminLTE.
- Do not introduce React/Vue.
- Do not remove existing fields or break existing screens.
- Preserve `parcel_pice` compatibility.
- Use DB transactions for important write flows.
- Keep controllers thin and put business logic in services.
- Use Thai UI labels.
- Add validation and clear error messages.
- Add indexes for common queries.
- Add tests where practical.

Expected output:
- Working migrations
- Models and relationships
- Services
- Controllers
- Routes
- Blade views
- Dashboard updates
- Export endpoints
- API endpoints
- README update
- Summary of changed files
- Manual test checklist
- Known limitations and next steps
```

---

# Manual Test Checklist

Use after implementation.

```text
[ ] Run composer install if new packages were added
[ ] Run php artisan migrate
[ ] Login to admin
[ ] Create an order with at least 2 receivers
[ ] Confirm contacts sync still works
[ ] Create a new trip
[ ] Assign both parcels to the trip
[ ] Confirm trip totals update
[ ] Try assigning the same parcel again and confirm it is blocked
[ ] Start the trip
[ ] Update first parcel to delivered
[ ] Mark first parcel COD as paid
[ ] Update second parcel to failed with failed reason
[ ] Confirm status logs are created
[ ] Try closing trip before all items are final and confirm it fails
[ ] Close trip after all items are final
[ ] Confirm completed trip is read-only
[ ] Open parcel tracking timeline
[ ] Open driver mobile view
[ ] Print labels by order
[ ] Print labels by trip
[ ] Export trip CSV
[ ] Export COD CSV
[ ] Check dashboard KPIs
[ ] Check old order list/edit pages still work
```

---

# Suggested Labels for GitHub Issues

```text
feature:trip-management
feature:parcel-tracking
feature:driver-view
feature:cod
feature:dashboard
feature:exports
feature:qr-label
tech-debt:parcel-price
test
security
documentation
```

---

# Suggested Milestones

## Milestone 1 — Delivery Run MVP

- Data model
- Trip service
- Trip CRUD
- Assign parcels
- Update status/payment
- Close trip

## Milestone 2 — Operation Tools

- Tracking timeline
- Driver view
- Dashboard KPIs
- CSV exports

## Milestone 3 — Business Tools

- QR labels
- Contact reuse
- Cost/profit
- API

## Milestone 4 — Stabilization

- parcel_price cleanup
- tests
- security
- README
- UX polish
