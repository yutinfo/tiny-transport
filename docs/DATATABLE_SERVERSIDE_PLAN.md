# Server-side DataTables Plan — 4 parcel tables

> **Goal:** Convert the 4 parcel **table-list** screens to jQuery **DataTables with
> server-side processing** (AJAX paging / search / sort done in MySQL, not in PHP
> memory or the browser).
> **Constraint:** **No new library.** DataTables (Bootstrap 4) is already bundled
> with AdminLTE under `public/plugins/datatables*` — front-end needs nothing new.
> Server side is **hand-rolled** (no `yajra/laravel-datatables`, no composer
> change — the `app` container has no composer anyway). Edit Blade + controllers +
> routes only; rebuild assets only if SCSS changes (none expected).
> **Status:** PLAN ONLY — no application code changed by this document.

---

## 0. Decisions (locked)

- **Hand-rolled** server-side endpoints returning the DataTables JSON contract.
- Use the **bundled** DataTables BS4 assets (no npm/composer additions).
- Apply to **all 4** table-list screens (see scope).

---

## 1. Target screens

| # | Screen | Route | View | Row unit |
|---|---|---|---|---|
| 1 | รายการออเดอร์/พัสดุ | `admin.orders.index` (`/admin/orders`) | `admin/order/list.blade.php` | `order_receives` (+order) |
| 2 | เพิ่มพัสดุเข้ารอบ | `admin.trips.assign` (`/admin/trips/{trip}/assign`) | `admin/trip/assign.blade.php` | assignable `order_receives` pool |
| 3 | รายการพัสดุในรอบ | `admin.trips.show` (`/admin/trips/{trip}`) | `admin/trip/show.blade.php` (items table) | `trip_items` of one trip |
| 4 | ค้นหาพัสดุ | `admin.parcels.search` (`/admin/parcels/search`) | `admin/parcel/search.blade.php` | `order_receives` |

---

## 2. Architecture — the hand-rolled server-side contract

DataTables (serverSide) sends a GET/POST with these params per draw:

```
draw, start, length,
search[value], search[regex],
order[0][column], order[0][dir],
columns[i][data], columns[i][name], columns[i][searchable], columns[i][orderable], columns[i][search][value]
```

The endpoint must reply:

```json
{ "draw": <int>, "recordsTotal": <int>, "recordsFiltered": <int>, "data": [ {row}, ... ] }
```

### Shared helper (new) — `app/Support/DataTable.php`
One small class keeps the 4 endpoints DRY (this is what `yajra` would do, minus the dependency):

```php
namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DataTable
{
    /**
     * @param array  $columns  ordered to match DataTables columns; each:
     *   ['key' => 'parcel_code', 'db' => 'order_receives.parcel_code',
     *    'orderable' => true, 'searchable' => true]
     * @param callable $rowMapper  fn($model): array  -> the JSON row (keys = column 'key' + extras)
     */
    public static function respond(Request $request, Builder $base, array $columns, callable $rowMapper): array
    {
        $recordsTotal = (clone $base)->count();

        $query = clone $base;

        // global search across searchable columns
        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $c) {
                    if (($c['searchable'] ?? false) && !empty($c['db'])) {
                        $q->orWhere($c['db'], 'like', "%{$search}%");
                    }
                }
            });
        }

        $recordsFiltered = (clone $query)->count();

        // ordering (validate against whitelist of orderable db columns)
        $orderColIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';
        $col = $columns[$orderColIdx] ?? null;
        if ($col && ($col['orderable'] ?? false) && !empty($col['db'])) {
            $query->orderBy($col['db'], $orderDir);
        } else {
            $query->orderByDesc($base->getModel()->getQualifiedKeyName());
        }

        // paging
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 20);
        if ($length > 0) {
            $query->skip($start)->take(min($length, 200)); // cap page size
        }

        $data = $query->get()->map($rowMapper)->all();

        return [
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }
}
```

Notes:
- **Whitelist** ordering by the column's `db` (never trust `columns[i][data]` directly) → no SQL injection via order.
- Per-page cap (`min($length,200)`) guards against abuse.
- Extra page-level **filters** (date, province, status …) are applied to `$base`
  *before* calling `respond()` — they are NOT part of the generic helper.

### Front-end init (per view)
Load bundled assets via stacks (admin layout already has `@stack('page_css')` /
`@stack('page_scripts')`):

```blade
@push('page_css')
<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
@endpush

@push('page_scripts')
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(function () {
    const table = $('#orders-table').DataTable({
      processing: true, serverSide: true, responsive: true,
      ajax: {
        url: @json(route('admin.orders.data')),
        data: d => { d.db_date = $('#filter-date').val(); }   // page filters
      },
      order: [[1, 'desc']],
      columns: [
        { data: 'row', orderable: false, searchable: false },
        { data: 'created_at' },
        { data: 'parcel_code' },
        { data: 'parcel_description', searchable: false },
        { data: 'customer_name' },
        { data: 'receive_name' },
        { data: 'province_name', searchable: false },
        { data: 'parcel_pice', className: 'text-right', searchable: false },
        { data: 'payment_type', searchable: false },
        { data: 'parcel_pickup_type', searchable: false },
        { data: 'actions', orderable: false, searchable: false },
      ],
      language: { url: '/plugins/datatables/th.json' }  // optional Thai i18n (see Risks)
    });
    $('#filter-date').on('change', () => table.ajax.reload());
  });
</script>
@endpush
```

The Blade table becomes just the `<thead>` (+ empty `<tbody>`); DataTables fills rows.

---

## 3. Per-table specification

### 3.1 Orders — `admin.orders.index`
- **New route:** `GET /admin/orders/data` → `OrderController@data` (name `admin.orders.data`).
- **Base query:** `OrderReceive::query()->with('order')` (row = one parcel/receiver).
- **Columns / mapper keys:** `row` (index), `created_at` (Thai date), `parcel_code`,
  `parcel_description`, `customer_name` (`order.customer_name (mobile)`),
  `receive_name` (`receive_name (mobile)`), `province_name` (full address),
  `parcel_pice` (formatted), `payment_type` (label), `parcel_pickup_type` (label),
  `actions` (edit/label/delete buttons HTML).
- **Searchable db columns:** `order_receives.parcel_code`,
  `order_receives.receive_name`, `orders.customer_name` (join needed — see below).
- **Orderable:** `created_at`, `parcel_code`, `parcel_pice`.
- **Page filter:** `db_date` (single date) → `whereBetween('order_receives.created_at', [startOfDay,endOfDay])` applied to `$base`.
- **Join for customer search/sort:** `->join('orders','orders.id','=','order_receives.order_id')->select('order_receives.*')` so `orders.customer_name` is searchable.
- **⚠ Subtotals:** the current view interleaves `รวมจ่ายทันที` / `รวมเก็บปลายทาง`
  rows inside the table. Server-side paged rows **cannot** carry interleaved
  subtotals. **Plan:** move totals to a **summary card above the table** (or the
  table `<tfoot>`), computed for the **current filter** by a tiny separate
  aggregate (sum of `parcel_pice` grouped by `payment_type`), refreshed on
  `ajax.reload`. Drop the interleaved subtotal rows from the row stream.
- `OrderController@index` keeps returning the view (now just the shell + filters);
  the heavy `->get()->toArray()` + `wrapDataIndex` move into `@data` per-page only.

### 3.2 Assign pool — `admin.trips.assign`
- **New route:** `GET /admin/trips/{trip}/assign/data` → `TripController@assignData` (name `admin.trips.assign.data`).
- **Base query:** the existing assignable-pool query from `TripController@assign`
  (lines 159–187) — `order_receives` with `delivery_status = waiting|null` and
  `whereDoesntHave` non-FAILED active trip items. Reuse it verbatim.
- **Columns:** `select` (checkbox HTML), `parcel_code`, `order_code`,
  `customer_name`, `receive_name`, `receive_mobile`, `destination` (address),
  `payment_type`, `parcel_pickup_type`, `parcel_pice`, `created_at`.
- **Searchable:** `parcel_code`, `receive_name`, `receive_mobile`.
- **Page filters:** `date_from`, `date_to`, `province_name`, `amphures_name`,
  `payment_type`, `parcel_pickup_type`, `keyword` — applied to `$base` (move the
  existing filter block from `assign()` into a shared private method used by both
  `assign()` and `assignData()`).
- **⚠ Multi-select across pages:** the "เพิ่มพัสดุที่เลือก" form posts
  `order_receive_ids[]`. With paged AJAX, checked rows on page 1 vanish on page 2.
  **Plan:** track selected ids in a JS `Set`; the checkbox column renders
  `<input class="row-select" value="{id}">`; on submit, write the Set into hidden
  `order_receive_ids[]` inputs (or post via fetch). A "เลือก N รายการ" counter is
  shown. (DataTables `select` plugin is also bundled if we prefer it later — but
  the JS Set keeps it dependency-light and explicit.)
- The submit still targets `admin.trips.assign-items` (POST) — unchanged.

### 3.3 Trip items — `admin.trips.show`
- **New route:** `GET /admin/trips/{trip}/items/data` → `TripController@itemsData` (name `admin.trips.items.data`).
- **Base query:** `$trip->tripItems()->with(['order','orderReceive'])`.
- **Columns:** `parcel_code`, `order_code`, `receive_name`, `address`,
  `cod_amount` (right), `collected_amount` (right), `delivery_status` (badge),
  `payment_status` (badge), `actions`.
- **Searchable:** `trip_items.parcel_code`, `order_receives.receive_name`.
- **Orderable:** `parcel_code`, `cod_amount`, `delivery_status`.
- **No page filter** (scoped to one trip; ownership via the route's trip binding).
- **⚠ Action cell:** each row has status-update **forms** (deliver / fail / return /
  COD). Render them server-side as an HTML string via a partial
  (`@include('admin.trip._item-actions', ['item'=>$item])->render()`) returned in
  the `actions` key. Must include `@csrf`. Keep the existing
  `admin.driver.trip-items.*` POST routes unchanged. DataTables draws the HTML as-is.
- **Note:** a trip rarely has many items, so server-side here is mostly for
  consistency; it is still correct and cheap.

### 3.4 Parcel search — `admin.parcels.search`
- **New route:** `GET /admin/parcels/search/data` → `ParcelTrackingController@searchData` (name `admin.parcels.search.data`).
- **Base query:** `OrderReceive::query()->with(['order','tripItems.trip'])`.
  (Currently only runs when `q` is present and returns `->get()`; server-side
  shows all and searches in MySQL.)
- **Columns:** `parcel_code`, `order_code`, `receive_name`, `destination`,
  `delivery_status` (label), `actions` (เปิด → `admin.parcels.code`).
- **Searchable:** `parcel_code`, `receive_name` (the page's `q` box can map to the
  DataTables global search, or stay as an extra `q` filter pre-applied to `$base`).
- **Orderable:** `parcel_code`, `created_at`.

---

## 4. Files touched

**New**
- `app/Support/DataTable.php` — shared server-side helper.
- `resources/views/admin/trip/_item-actions.blade.php` — extracted action-cell partial (for 3.3).
- (optional) `public/plugins/datatables/th.json` — Thai i18n (or use inline `language`).

**Edited — routes** (`routes/admin.php`): add 4 `*.data` GET routes (assign/items data scoped under the existing `{trip}` where relevant).

**Edited — controllers**
- `OrderController` → add `data()`; slim `index()` to view-shell + summary.
- `TripController` → add `assignData()`, `itemsData()`; extract assign-filter into a private method.
- `ParcelTrackingController` → add `searchData()`.

**Edited — views** (4): replace the Blade row loops with a `<thead>`-only table +
`@push` DataTables init; keep/adjust the filter controls to feed `ajax.data`.

No migrations. No model changes. No new dependency in `composer.json` / `package.json`.

---

## 5. Implementation phases

1. **Helper + assets proof** — add `app/Support/DataTable.php`; convert
   **Orders** (3.1) end-to-end as the reference pattern (incl. summary-card for
   subtotals). Verify in browser + a feature test for the JSON contract.
2. **Assign** (3.2) — incl. cross-page selection.
3. **Trip items** (3.3) — incl. action-cell partial.
4. **Parcel search** (3.4).
5. **Polish** — Thai i18n, empty states, `responsive` behavior, column widths.

Each phase is independently shippable and reviewable.

---

## 6. Testing

- **Feature test per endpoint** (`tests/Feature`): hit `*.data` with DataTables
  params and assert JSON shape (`draw`, `recordsTotal`, `recordsFiltered`, `data`
  count) and that search/order/paging narrow correctly. Assert access control
  (admin/staff only; the routes sit in the existing admin group).
- **Ownership/scope:** assign-pool excludes already-assigned/returned parcels
  (reuse current logic); trip items scoped to the bound trip.
- Run focused first, then the suite, all via Docker
  (`docker compose exec app php artisan test --filter=...`).

---

## 7. Risks & gotchas

| Risk | Mitigation |
|---|---|
| SQL injection via `order`/`search` | Whitelist orderable/searchable to `db` columns in the helper; never use raw `columns[i][data]`. |
| Orders subtotal rows can't interleave | Move totals to a summary card / `<tfoot>` computed per filter. |
| Assign checkboxes lost across pages | JS `Set` of selected ids → hidden inputs on submit. |
| Action-cell forms in JSON | Pre-render partial to HTML string server-side incl. `@csrf`; keep POST routes unchanged. |
| Joins break `select *` (ambiguous ids) | `->select('order_receives.*')` after joins. |
| DataTables sends GET with long query | Use `POST` for the ajax (`type:'POST'` + CSRF header) if URLs get long. CSRF via `$.ajaxSetup({headers:{'X-CSRF-TOKEN': meta}})`. |
| Thai i18n missing | Ship `th.json` or set `language` inline; otherwise default English controls. |
| Double pagination | Remove the old `->links()` / `wrapDataIndex` paths from the converted views. |

---

## 8. Out of scope (future)

- Column visibility / export buttons (datatables-buttons is bundled — easy add later).
- Saved filters / state persistence.
- Converting non-parcel tables (trips list, contacts) — same pattern applies if wanted.

---

*Plan authored for the Tiny Transport admin parcel tables. Implementation pending
approval — no source changed by this document.*
