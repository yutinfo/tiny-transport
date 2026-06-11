<div class="ta-table-actions">
    <a href="{{ route('admin.trips.show', $trip) }}" class="btn bg-primary btn-xs"><i class="fas fa-eye"></i> ดู</a>
    <a href="{{ route('admin.trips.driver', $trip) }}" class="btn btn-default btn-xs"><i class="fas fa-mobile-alt"></i> Driver</a>
    @if(! in_array($trip->status, [\App\Models\Trip::STATUS_COMPLETED, \App\Models\Trip::STATUS_CANCELLED], true))
        <a href="{{ route('admin.trips.edit', $trip) }}" class="btn bg-info btn-xs"><i class="fas fa-edit"></i> แก้ไข</a>
    @endif
</div>
