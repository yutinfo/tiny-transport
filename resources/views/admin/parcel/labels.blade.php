<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - {{ $subtitle }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Tahoma, Arial, sans-serif; color: #111; background: #f4f6f9; }
        .toolbar { padding: 12px 16px; background: #fff; border-bottom: 1px solid #d8dee4; display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .toolbar h1 { font-size: 18px; margin: 0; }
        .toolbar small { color: #6c757d; }
        .toolbar button { border: 0; background: #007bff; color: #fff; padding: 8px 14px; border-radius: 4px; cursor: pointer; }
        .label-grid { padding: 12px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .parcel-label { background: #fff; border: 1px solid #111; min-height: 148mm; padding: 10mm; page-break-inside: avoid; display: grid; grid-template-columns: 38mm 1fr; gap: 8mm; align-items: start; }
        .qr-box { text-align: center; }
        .qr-box svg { width: 36mm; height: 36mm; display: block; margin: 0 auto 3mm; }
        .parcel-code { font-size: 15px; font-weight: 700; word-break: break-all; }
        .label-title { font-size: 20px; font-weight: 700; margin-bottom: 4mm; }
        .field { margin-bottom: 2mm; font-size: 13px; line-height: 1.35; }
        .field strong { display: inline-block; min-width: 24mm; }
        .address { white-space: pre-wrap; }
        .cod { font-size: 16px; font-weight: 700; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .label-grid { padding: 0; grid-template-columns: repeat(2, 1fr); gap: 0; }
            .parcel-label { border: 1px dashed #777; min-height: 148mm; }
            @page { size: A4; margin: 8mm; }
        }
        @media (max-width: 767px) {
            .label-grid { grid-template-columns: 1fr; }
            .parcel-label { min-height: auto; grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <h1>{{ $title }}</h1>
            <small>{{ $subtitle }}</small>
        </div>
        <button type="button" onclick="window.print()">พิมพ์</button>
    </div>

    <main class="label-grid">
        @forelse($labels as $label)
            <section class="parcel-label">
                <div class="qr-box">
                    {!! $label['qr_svg'] !!}
                    <div class="parcel-code">{{ $label['parcel_code'] }}</div>
                </div>
                <div>
                    <div class="label-title">ใบปะหน้าพัสดุ</div>
                    <div class="field"><strong>ผู้ส่ง</strong> {{ $label['sender_name'] }} ({{ $label['sender_mobile'] }})</div>
                    <div class="field"><strong>ผู้รับ</strong> {{ $label['receiver_name'] }} ({{ $label['receiver_mobile'] }})</div>
                    <div class="field address"><strong>ปลายทาง</strong> {{ $label['destination_address'] ?: '-' }}</div>
                    <div class="field"><strong>ออเดอร์</strong> {{ $label['order_code'] }}</div>
                    <div class="field"><strong>ชำระเงิน</strong> {{ $label['payment_type'] }}</div>
                    <div class="field"><strong>วิธีจัดส่ง</strong> {{ $label['pickup_type'] }}</div>
                    @if($label['cod_amount'] !== null)
                        <div class="field cod"><strong>COD</strong> {{ number_format($label['cod_amount'], 2) }} บาท</div>
                    @endif
                    <div class="field"><strong>วันที่สร้าง</strong> {{ $label['created_date'] ?: '-' }}</div>
                </div>
            </section>
        @empty
            <section class="parcel-label">
                <div class="field">ไม่พบรายการพัสดุสำหรับพิมพ์ใบปะหน้า</div>
            </section>
        @endforelse
    </main>
</body>
</html>
