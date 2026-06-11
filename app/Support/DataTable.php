<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DataTable
{
    /**
     * Build a DataTables server-side response from an Eloquent base query.
     *
     * @param  array  $columns  Ordered to match the DataTables `columns` array. Each entry:
     *   ['key' => 'parcel_code', 'db' => 'order_receives.parcel_code',
     *    'orderable' => true, 'searchable' => true]
     *   The 'db' key is the whitelisted column used for ordering/searching — never the
     *   client-supplied `columns[i][data]`, to keep ordering injection-safe.
     * @param  callable  $rowMapper  fn($model): array — the JSON row (keys = column 'key' + extras).
     * @return array{draw:int,recordsTotal:int,recordsFiltered:int,data:array}
     */
    public static function respond(Request $request, Builder $base, array $columns, callable $rowMapper): array
    {
        $recordsTotal = (clone $base)->count();

        $query = clone $base;

        // Global search across searchable columns.
        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $c) {
                    if (($c['searchable'] ?? false) && ! empty($c['db'])) {
                        $q->orWhere($c['db'], 'like', "%{$search}%");
                    }
                }
            });
        }

        $recordsFiltered = (clone $query)->count();

        // Ordering — validate against the whitelist of orderable db columns.
        $orderColIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';
        $col = $columns[$orderColIdx] ?? null;
        if ($col && ($col['orderable'] ?? false) && ! empty($col['db'])) {
            $query->orderBy($col['db'], $orderDir);
        } else {
            $query->orderByDesc($base->getModel()->getQualifiedKeyName());
        }

        // Paging — cap the page size to guard against abuse.
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 20);
        if ($length > 0) {
            $query->skip($start)->take(min($length, 200));
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
