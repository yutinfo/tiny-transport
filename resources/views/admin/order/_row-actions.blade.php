<div class="ta-table-actions">
    <a class="btn bg-info btn-xs" href="{{ route('admin.orders.edit', $orderId) }}"><i class="fas fa-edit"></i> แก้ไข</a>
    <a class="btn bg-dark btn-xs" href="{{ route('admin.orders.labels', $orderId) }}"><i class="fas fa-qrcode"></i> Label</a>
    <a class="btn bg-secondary btn-xs" href="{{ route('admin.parcels.tracking', $orderReceiveId) }}"><i class="fas fa-history"></i> ดูประวัติ</a>
    <button type="button" class="btn bg-danger btn-xs" onclick="dt('{{ $orderReceiveId }}', @js($customerName))"><i class="fas fa-trash-alt"></i> ลบ</button>
</div>
