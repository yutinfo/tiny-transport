<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\Contact;
use App\Support\DataTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $dbDate = $request->filled('db_date')
            ? Carbon::parse($request->input('db_date'))->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');

        return view('admin.order.list', [
            'dbDate' => $dbDate,
            'selectDate' => $request->input('select_date', ''),
            'summary' => $this->summaryForDate($dbDate),
        ]);
    }

    /**
     * Server-side DataTables endpoint for the orders/parcels list. Rows are one
     * order_receive (parcel) joined to its order so the customer name is searchable.
     */
    public function data(Request $request)
    {
        $base = OrderReceive::query()
            ->with('order')
            ->join('orders', 'orders.id', '=', 'order_receives.order_id')
            ->select('order_receives.*');

        $this->applyDateFilter($base, $request->input('db_date'));

        $columns = [
            ['key' => 'row', 'orderable' => false, 'searchable' => false],
            ['key' => 'created_at', 'db' => 'order_receives.created_at', 'orderable' => true, 'searchable' => false],
            ['key' => 'parcel_code', 'db' => 'order_receives.parcel_code', 'orderable' => true, 'searchable' => true],
            ['key' => 'parcel_description', 'orderable' => false, 'searchable' => false],
            ['key' => 'customer_name', 'db' => 'orders.customer_name', 'orderable' => false, 'searchable' => true],
            ['key' => 'receive_name', 'db' => 'order_receives.receive_name', 'orderable' => false, 'searchable' => true],
            ['key' => 'province_name', 'orderable' => false, 'searchable' => false],
            ['key' => 'parcel_pice', 'db' => 'order_receives.parcel_pice', 'orderable' => true, 'searchable' => false],
            ['key' => 'payment_type', 'orderable' => false, 'searchable' => false],
            ['key' => 'parcel_pickup_type', 'orderable' => false, 'searchable' => false],
            ['key' => 'actions', 'orderable' => false, 'searchable' => false],
        ];

        $start = max(0, (int) $request->input('start', 0));
        $i = 0;

        $payload = DataTable::respond($request, $base, $columns, function (OrderReceive $receiver) use ($start, &$i) {
            return $this->mapOrderRow($receiver, $start + (++$i));
        });

        // Subtotals follow the filter so the summary card can refresh on each draw.
        $payload['summary'] = $this->summaryForDate(
            $request->filled('db_date') ? Carbon::parse($request->input('db_date'))->format('Y-m-d') : Carbon::now()->format('Y-m-d')
        );

        return response()->json($payload);
    }

    /**
     * Apply the single-date page filter (defaults to today) to the order_receives query.
     */
    private function applyDateFilter($query, ?string $dbDate): void
    {
        $date = $dbDate ? Carbon::parse($dbDate) : Carbon::now();
        $query->whereBetween('order_receives.created_at', [
            $date->copy()->startOfDay()->format('Y-m-d H:i:s'),
            $date->copy()->endOfDay()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Subtotals for the summary card — grouped by payment_type for the filtered date.
     */
    private function summaryForDate(string $dbDate): array
    {
        $query = OrderReceive::query();
        $this->applyDateFilter($query, $dbDate);

        return [
            'immediately_total' => (float) (clone $query)->where('payment_type', 'immediately')->sum('parcel_pice'),
            'on_delivery_total' => (float) (clone $query)->where('payment_type', 'on_delivery')->sum('parcel_pice'),
        ];
    }

    /**
     * Map one order_receive into the DataTables JSON row (HTML is pre-rendered server-side).
     */
    private function mapOrderRow(OrderReceive $receiver, int $rowNumber): array
    {
        $pickupLabels = [
            'pickup' => 'รับที่ร้าน',
            'delivery' => 'จัดส่งปกติ',
        ];
        $paymentLabels = [
            'immediately' => 'จ่ายเงินทันที',
            'on_delivery' => 'เก็บเงินปลายทาง',
        ];

        $order = $receiver->order;
        $address = trim(implode(' ', array_filter([
            $receiver->receive_address,
            $receiver->district_name,
            $receiver->amphures_name,
            $receiver->province_name,
            $receiver->zip_code,
        ])));

        return [
            'row' => $rowNumber . '.',
            'created_at' => $receiver->created_at ? thaiDateFullmonth($receiver->created_at) : '-',
            'parcel_code' => e($receiver->parcel_code),
            'parcel_description' => e($receiver->parcel_description),
            'customer_name' => e(trim(($order->customer_name ?? '') . ' (' . ($order->customer_mobile ?? '') . ')')),
            'receive_name' => e(trim(($receiver->receive_name ?? '') . ' (' . ($receiver->receive_mobile ?? '') . ')')),
            'province_name' => e($address),
            'parcel_pice' => number_format((float) $receiver->getParcelPriceValue(), 2),
            'payment_type' => $paymentLabels[$receiver->payment_type] ?? 'จัดส่งปกติ',
            'parcel_pickup_type' => $pickupLabels[$receiver->parcel_pickup_type] ?? '-',
            'actions' => view('admin.order._row-actions', [
                'orderId' => $receiver->order_id,
                'orderReceiveId' => $receiver->id,
                'customerName' => $order->customer_name ?? '',
            ])->render(),
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.order.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validateStoreRequest($request);

        $data_array = $this->wrapDataStore($request);
        $order_create = DB::transaction(function () use ($data_array) {
            $order = Order::create($data_array['sender']);
            $receivers = [];

            foreach (Arr::get($data_array, 'receivers', []) as $value) {
                $value['order_id'] = $order->id;
                $receivers[] = OrderReceive::create($value);
            }

            $this->syncContactsFromOrderData($data_array);

            $order['receivers'] = $receivers;

            return $order;
        });

        return response()->json($order_create);
    }

    private function validateStoreRequest(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'sender_name' => ['required', 'string', 'max:100'],
            'sender_mobile' => ['required', 'string', 'max:20', 'regex:/^[\d\s()+-]+$/'],
            'sender_zip_code' => ['nullable', 'digits_between:5,10'],
            'driver_mobile' => ['nullable', 'string', 'max:10', 'regex:/^\d{9,10}$/'],
            'receivers' => ['nullable', 'array'],
        ], [
            'required' => ':attribute จำเป็นต้องกรอก',
            'regex' => ':attribute รูปแบบไม่ถูกต้อง',
            'max' => ':attribute ยาวเกินไป',
            'digits_between' => ':attribute รูปแบบไม่ถูกต้อง',
            'array' => ':attribute รูปแบบไม่ถูกต้อง',
        ], [
            'sender_name' => 'ชื่อ-นามสกุลผู้ฝาก',
            'sender_mobile' => 'เบอร์โทรศัพท์ผู้ฝาก',
            'sender_zip_code' => 'รหัสไปรษณีย์ผู้ฝาก',
            'driver_mobile' => 'เบอร์โทรศัพท์ผู้ขับ',
            'receivers' => 'รายการผู้รับ',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (! preg_match('/^\d{9,10}$/', $this->normalizeMobile($request->input('sender_mobile')))) {
                $this->addValidationError($validator, '', 'sender_mobile', 'เบอร์โทรศัพท์ผู้ฝาก รูปแบบไม่ถูกต้อง');
            }

            $queuedReceivers = $this->queuedReceiverPayloads($request);
            $currentReceiver = $request->only($this->receiverKeys());
            $hasCurrentReceiver = $this->hasReceiverPayload($currentReceiver);

            foreach ($queuedReceivers as $index => $receiver) {
                $this->addReceiverValidationErrors($validator, $receiver, "receivers.{$index}", 'ผู้รับลำดับที่ ' . ($index + 1));
            }

            if (count($queuedReceivers) === 0 || $hasCurrentReceiver) {
                $this->addReceiverValidationErrors($validator, $currentReceiver, '', 'ผู้รับ');
            }
        });

        $validator->validate();
    }

    private function receiverKeys(): array
    {
        return [
            "parcel_description",
            "parcel_pice",
            "payment_type",
            "pickup_type",
            "receive_mobile",
            "receive_name",
            "receive_address",
            "receive_amphure_text",
            "receive_district_text",
            "receive_province_text",
            "receive_province",
            "receive_amphure",
            "receive_district",
            "receive_zip_code",
        ];
    }

    private function queuedReceiverPayloads(Request $request): array
    {
        $receivers = $request->input('receivers', []);

        if (! is_array($receivers)) {
            return [];
        }

        return array_values($receivers);
    }

    private function hasReceiverPayload(array $receiver): bool
    {
        foreach ($this->receiverKeys() as $key) {
            if (filled(Arr::get($receiver, $key))) {
                return true;
            }
        }

        return false;
    }

    private function addReceiverValidationErrors($validator, array $receiver, string $prefix, string $groupLabel): void
    {
        $labels = [
            'receive_name' => 'ชื่อ-นามสกุลผู้รับ',
            'receive_mobile' => 'เบอร์โทรศัพท์ผู้รับ',
            'receive_address' => 'ที่อยู่ผู้รับ',
            'receive_province' => 'จังหวัดผู้รับ',
            'receive_amphure' => 'อำเภอผู้รับ',
            'receive_district' => 'ตำบลผู้รับ',
            'receive_zip_code' => 'รหัสไปรษณีย์ผู้รับ',
            'parcel_description' => 'ข้อมูลพัสดุ',
            'parcel_pice' => 'จำนวนเงิน/ราคา',
            'payment_type' => 'ช่องทางการชำระเงิน',
        ];

        $required = [
            'receive_name',
            'receive_mobile',
            'parcel_description',
            'parcel_pice',
            'payment_type',
        ];

        if ((string) Arr::get($receiver, 'pickup_type') !== '1') {
            array_push($required, 'receive_address', 'receive_province', 'receive_amphure', 'receive_district', 'receive_zip_code');
        }

        foreach ($required as $field) {
            if (blank(Arr::get($receiver, $field))) {
                $this->addValidationError($validator, $prefix, $field, "{$groupLabel}: {$labels[$field]} จำเป็นต้องกรอก");
            }
        }

        if (filled(Arr::get($receiver, 'receive_mobile')) && ! preg_match('/^\d{9,10}$/', $this->normalizeMobile(Arr::get($receiver, 'receive_mobile')))) {
            $this->addValidationError($validator, $prefix, 'receive_mobile', "{$groupLabel}: {$labels['receive_mobile']} รูปแบบไม่ถูกต้อง");
        }

        if (filled(Arr::get($receiver, 'receive_zip_code')) && ! preg_match('/^\d{5}$/', Arr::get($receiver, 'receive_zip_code'))) {
            $this->addValidationError($validator, $prefix, 'receive_zip_code', "{$groupLabel}: {$labels['receive_zip_code']} ต้องเป็นตัวเลข 5 หลัก");
        }

        if (filled(Arr::get($receiver, 'parcel_pice')) && (float) Arr::get($receiver, 'parcel_pice') <= 0) {
            $this->addValidationError($validator, $prefix, 'parcel_pice', "{$groupLabel}: {$labels['parcel_pice']} ต้องมากกว่า 0");
        }

        if (filled(Arr::get($receiver, 'payment_type')) && ! in_array((string) Arr::get($receiver, 'payment_type'), ['1', '2'], true)) {
            $this->addValidationError($validator, $prefix, 'payment_type', "{$groupLabel}: {$labels['payment_type']} รูปแบบไม่ถูกต้อง");
        }
    }

    private function addValidationError($validator, string $prefix, string $field, string $message): void
    {
        $validator->errors()->add($prefix ? "{$prefix}.{$field}" : $field, $message);
    }

    private function syncContactsFromOrderData(array $data): void
    {
        $sender = Arr::get($data, 'sender_contact', []);

        $this->syncContact('sender', [
            'name' => Arr::get($sender, 'name'),
            'mobile' => Arr::get($sender, 'mobile'),
            'address' => Arr::get($sender, 'address'),
            'province_id' => Arr::get($sender, 'province_id'),
            'amphure_id' => Arr::get($sender, 'amphure_id'),
            'district_id' => Arr::get($sender, 'district_id'),
            'province_name' => Arr::get($sender, 'province_name'),
            'amphure_name' => Arr::get($sender, 'amphure_name'),
            'district_name' => Arr::get($sender, 'district_name'),
            'zip_code' => Arr::get($sender, 'zip_code'),
        ]);

        foreach (Arr::get($data, 'receivers', []) as $receiver) {
            $this->syncContact('receiver', [
                'name' => Arr::get($receiver, 'receive_name'),
                'mobile' => Arr::get($receiver, 'receive_mobile'),
                'address' => Arr::get($receiver, 'receive_address'),
                'province_id' => Arr::get($receiver, 'province_id'),
                'amphure_id' => Arr::get($receiver, 'amphures_id'),
                'district_id' => Arr::get($receiver, 'district_id'),
                'province_name' => Arr::get($receiver, 'province_name'),
                'amphure_name' => Arr::get($receiver, 'amphures_name'),
                'district_name' => Arr::get($receiver, 'district_name'),
                'zip_code' => Arr::get($receiver, 'zip_code'),
            ]);
        }
    }

    private function syncContact(string $type, array $payload): void
    {
        $mobile = preg_replace('/\D/', '', (string) Arr::get($payload, 'mobile'));
        $name = trim((string) Arr::get($payload, 'name'));

        if ($mobile === '' || $name === '') {
            return;
        }

        $existingContacts = Contact::where('mobile', $mobile)->get();
        $contact = $existingContacts->firstWhere('type', 'both')
            ?: $existingContacts->firstWhere('type', $type);

        if (! $contact) {
            $contact = $existingContacts->first();

            if ($contact) {
                $contact->type = 'both';
            }
        }

        if ($contact && $contact->type !== 'both' && $existingContacts->contains(fn ($existing) => ! in_array($existing->type, [$type, 'both'], true))) {
            $contact->type = 'both';
        }

        if (! $contact) {
            $contact = new Contact([
                'type' => $type,
                'mobile' => $mobile,
            ]);
        }

        if (! $contact->exists) {
            $contact->created_by = Auth::user()->name ?? null;
        }

        $contact->fill([
            'name' => $name,
            'address' => Arr::get($payload, 'address'),
            'province_id' => Arr::get($payload, 'province_id'),
            'amphure_id' => Arr::get($payload, 'amphure_id'),
            'district_id' => Arr::get($payload, 'district_id'),
            'province_name' => Arr::get($payload, 'province_name'),
            'amphure_name' => Arr::get($payload, 'amphure_name'),
            'district_name' => Arr::get($payload, 'district_name'),
            'zip_code' => Arr::get($payload, 'zip_code'),
            'updated_by' => Auth::user()->name ?? null,
        ]);

        $contact->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order_model = Order::with('receivers')->find($id);

        return view('admin.order.edit', [
            'data' => $order_model
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validateUpdateRequest($request);

        try{
            $data_array = $this->wrapDataUpdate($request);

            DB::transaction(function () use ($id, $data_array) {
                $order = Order::findOrFail($id);
                $order->update($data_array['sender']);

                foreach (Arr::get($data_array, 'receivers', []) as $value) {
                    $receiver = OrderReceive::where('order_id', $order->id)->findOrFail($value['id']);
                    $receiver->update($value);
                }
            });

            return redirect()->route('admin.orders.edit', [
                'id' => $id
            ])->with('message', 'success');
        }catch(\Exception $e){
            return redirect()->route('admin.orders.edit', [
                'id' => $id
            ])->withErrors(
                $e->getMessage()

            );
        }

    }

    private function validateUpdateRequest(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'sender_name' => ['nullable', 'string', 'max:100'],
            'sender_mobile' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s()+-]+$/'],
            'sender_zip_code' => ['nullable', 'digits_between:5,10'],
            'driver_mobile' => ['nullable', 'string', 'max:10', 'regex:/^\d{9,10}$/'],
            'receive_mobile' => ['nullable', 'array'],
            'receive_mobile.*.*' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s()+-]+$/'],
            'receive_zip_code' => ['nullable', 'array'],
            'receive_zip_code.*.*' => ['nullable', 'digits_between:5,10'],
            'parcel_pice' => ['nullable', 'array'],
            'parcel_pice.*.*' => ['nullable', 'numeric', 'min:0.01'],
            'payment_type' => ['nullable', 'array'],
            'payment_type.*.*' => ['nullable', 'in:1,2'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if (filled($request->input('sender_mobile')) && ! preg_match('/^\d{9,10}$/', $this->normalizeMobile($request->input('sender_mobile')))) {
                $this->addValidationError($validator, '', 'sender_mobile', 'เบอร์โทรศัพท์ผู้ฝาก รูปแบบไม่ถูกต้อง');
            }

            foreach ((array) $request->input('receive_mobile', []) as $receiverId => $values) {
                foreach ((array) $values as $index => $mobile) {
                    if (filled($mobile) && ! preg_match('/^\d{9,10}$/', $this->normalizeMobile($mobile))) {
                        $this->addValidationError($validator, '', "receive_mobile.{$receiverId}.{$index}", 'เบอร์โทรศัพท์ผู้รับ รูปแบบไม่ถูกต้อง');
                    }
                }
            }
        });

        $validator->validate();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function wrapDataStore($param)
    {
        $sender = $param->only([
            "driver_car_id",
            "driver_mobile",
            "driver_name",
            "sender_address",
            "sender_mobile",
            "sender_name",
            "sender_province",
            "sender_zip_code",
            "sender_amphure",
            "sender_district",
            "sender_amphure_text",
            "sender_district_text",
            "sender_mobile",
            "sender_name",
            "sender_province",
            "sender_province_text",
        ]);

        $receiver_key = $this->receiverKeys();
        $receiver = $param->only($receiver_key);
        $receivers = [];
        if ($param->receivers) {
            foreach ($param->receivers as $key => $value) {
                foreach ($value as $item_key => $item) {
                    if (in_array($item_key, $receiver_key)) {
                        $receivers[$key][$item_key] = $item;
                    }
                }
            }
        }

        if ($this->hasReceiverPayload($receiver)) {
            array_push($receivers, $receiver);
        }

        $parcel_amount = count($receivers);
        $parcel_total = collect($receivers)->sum(fn ($receiver) => (float) Arr::get($receiver, 'parcel_pice', 0));

        $response_sender = [
            "code" => self::generateOrderCode(),
            "customer_name" => $sender["sender_name"] ?? "",
            "customer_mobile" => $this->normalizeMobile($sender["sender_mobile"] ?? ""),
            "customer_address" => $sender["sender_address"] ?? "",
            "province_name" => $sender["sender_province_text"] ?? "",
            "amphures_name" => $sender["sender_amphure_text"] ?? "",
            "district_name" => $sender["sender_district_text"] ?? "",
            "zip_code" => $sender["sender_zip_code"] ?? "",
            "car_id" => $sender["driver_car_id"] ?? "",
            "driver_name" => $sender["driver_name"] ?? "",
            "driver_mobile" => $sender["driver_mobile"] ?? "",
            "parcel_amount" => $parcel_amount,
            "parcel_total" => $parcel_total,
            "order_status" => "waiting",
            "created_by" => Auth::user()->name,
            "updated_by" => Auth::user()->name,
        ];
        $response_sender_contact = [
            "name" => $sender["sender_name"] ?? "",
            "mobile" => $this->normalizeMobile($sender["sender_mobile"] ?? ""),
            "address" => $sender["sender_address"] ?? "",
            "province_id" => $sender["sender_province"] ?? null,
            "amphure_id" => $sender["sender_amphure"] ?? null,
            "district_id" => $sender["sender_district"] ?? null,
            "province_name" => $sender["sender_province_text"] ?? "",
            "amphure_name" => $sender["sender_amphure_text"] ?? "",
            "district_name" => $sender["sender_district_text"] ?? "",
            "zip_code" => $sender["sender_zip_code"] ?? "",
        ];
        $response_receivers = [];
        foreach ($receivers as $key => $receiver) {
            $response_receivers[$key] = [
                "parcel_code" => self::generateParcelCode(),
                "parcel_description" => $receiver['parcel_description'] ?? null,
                "receive_name" => $receiver['receive_name'] ?? null,
                "receive_mobile" => $this->normalizeMobile($receiver['receive_mobile'] ?? ''),
                "receive_address" => $receiver['receive_address'] ?? null,
                "province_id" => $receiver['receive_province'] ?? null,
                "amphures_id" => $receiver['receive_amphure'] ?? null,
                "district_id" => $receiver['receive_district'] ?? null,
                "province_name" => $receiver['receive_province_text'] ?? null,
                "amphures_name" => $receiver['receive_amphure_text'] ?? null,
                "district_name" => $receiver['receive_district_text'] ?? null,
                "zip_code" => $receiver['receive_zip_code'] ?? null,
                "parcel_pickup_type" => (Arr::get($receiver, "pickup_type", 0) == "1") ? "pickup" : "delivery",
                "payment_type" => (Arr::get($receiver, 'payment_type', "1") == "1") ? "immediately" : "on_delivery",
                "delivery_status" => "waiting",
                "payment_status" => "waiting",
                "parcel_pice" => $receiver['parcel_pice'] ?? 0,
                "created_by" => Auth::user()->name,
                "updated_by" => Auth::user()->name,
            ];
        }

        return [
            'sender' => $response_sender,
            'sender_contact' => $response_sender_contact,
            'receivers' => $response_receivers,
        ];
    }

    static private function generateOrderCode()
    {
        $uuid =  substr(str_replace("-", "", Str::uuid()->toString()), 0, 5);
        return strtoupper("OR" . date('Y') . $uuid);
    }
    static private function generateParcelCode()
    {
        $uuid =  substr(str_replace("-", "", Str::uuid()->toString()), 0, 9);
        return strtoupper("P" . date('Y') . $uuid);
    }

    private function wrapDataUpdate($param)
    {
        $sender = $param->only([
            "driver_car_id",
            "driver_mobile",
            "driver_name",
            "sender_address",
            "sender_mobile",
            "sender_name",
            "sender_province",
            "sender_zip_code",
            "sender_mobile",
            "sender_province",
            "sender_amphure",
            "sender_district",
            "sender_name",
            "sender_province"
        ]);

        $receiver_key = [
            "parcel_description",
            "parcel_pice",
            "payment_type",
            "pickup_type",
            "receive_mobile",
            "receive_name",
            "receive_address",
            "receive_province",
            "receive_amphure",
            "receive_district",
            "receive_zip_code",
        ];
        $receiver = $param->only($receiver_key);

        $response_receiver = [];
        foreach ($receiver as $key => $value) {
            foreach ($value as $item_key => $item) {
                $response_receiver[$item_key][$key] = $item[0];
            }
        }
        $response_sender = [
            "updated_by" => Auth::user()->name,
        ];
        if (Arr::get($sender, "sender_name")) {
            $response_sender["customer_name"] = $sender["sender_name"];
        }
        if (Arr::get($sender, "sender_mobile")) {
            $response_sender["customer_mobile"] = $this->normalizeMobile($sender["sender_mobile"]);
        }
        if (Arr::get($sender, "sender_address")) {
            $response_sender["customer_address"] = $sender["sender_address"];
        }
        if (Arr::get($sender, "driver_car_id")) {
            $response_sender["car_id"] = $sender["driver_car_id"];
        }
        if (Arr::get($sender, "driver_name")) {
            $response_sender["driver_name"] = $sender["driver_name"];
        }
        if (Arr::get($sender, "driver_mobile")) {
            $response_sender["driver_mobile"] = $sender["driver_mobile"];
        }
        if (Arr::get($sender, "sender_province")) {
            $response_sender["province_name"] = $sender["sender_province"];
        }
        if (Arr::get($sender, "sender_amphure")) {
            $response_sender["amphures_name"] = $sender["sender_amphure"];
        }
        if (Arr::get($sender, "sender_district")) {
            $response_sender["district_name"] = $sender["sender_district"];
        }
        if (Arr::get($sender, "sender_zip_code")) {
            $response_sender["zip_code"] = $sender["sender_zip_code"];
        }


        $parcel_amount = 0;
        $parcel_total = 0;
        $response_receivers = [];
        $key_num = 0;
        foreach ($response_receiver as $key => $receiver) {
            $response_receivers[$key_num] = [
                "id" => $key,
                "parcel_description" => $receiver['parcel_description'] ?? null,
                "receive_name" => $receiver['receive_name'] ?? null,
                "receive_mobile" => $this->normalizeMobile($receiver['receive_mobile'] ?? ''),
                "receive_address" => $receiver['receive_address'] ?? null,
                "parcel_pickup_type" => (Arr::get($receiver, "pickup_type", 0) == "1") ? "pickup" : "delivery",
                "payment_type" => (Arr::get($receiver, 'payment_type', "1") == "1") ? "immediately" : "on_delivery",
                "parcel_pice" => $receiver['parcel_pice'],
                "updated_by" => Auth::user()->name,
            ];
            if (Arr::get($receiver, "receive_province")) {
                $response_receivers[$key_num]["province_name"] = $receiver["receive_province"];
            }
            if (Arr::get($receiver, "receive_amphure")) {
                $response_receivers[$key_num]["amphures_name"] = $receiver["receive_amphure"];
            }
            if (Arr::get($receiver, "receive_district")) {
                $response_receivers[$key_num]["district_name"] = $receiver["receive_district"];
            }

            if (Arr::get($receiver, "receive_zip_code")) {
                $response_receivers[$key_num]["zip_code"] = $receiver["receive_zip_code"];
            }
            $parcel_total += (float) $receiver['parcel_pice'];
            $parcel_amount++;
            $key_num++;
        }
        $response_sender["parcel_amount"] = $parcel_amount;
        $response_sender["parcel_total"] = $parcel_total;

        return [
            'sender' => $response_sender,
            'receivers' => $response_receivers,
        ];
    }

    private function normalizeMobile(?string $mobile): string
    {
        return preg_replace('/\D/', '', (string) $mobile) ?: '';
    }
}
