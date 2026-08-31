<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\Vendor;
use App\Services\BookingReportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Every booking on the platform, in one paginated ledger.
 *
 * This is the tracking counterpart to Admin\ReportController: the report exists
 * to produce a downloadable period sheet, this exists to answer "what happened
 * with this customer / this shop / this specialist" without exporting anything.
 * The status vocabulary is borrowed from BookingReportService so both screens
 * name the same things the same way.
 *
 * Rows are never returned unpaginated — a platform-wide booking table is the
 * one query here that grows without bound.
 */
class BookingController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request)
    {
        $filters = $this->filters($request);

        // Status is applied last so the tiles can count every status inside the
        // same vendor/staff/date/search selection — a breakdown that shrank to
        // one row as soon as you clicked a status would tell you nothing.
        $scoped = $this->scopedQuery($filters);

        $counts = (clone $scoped)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $bookings = $this->applyStatus(clone $scoped, $filters['status'])
            ->with(['employee', 'vendor'])
            ->orderByDesc('booking_date')
            ->orderByDesc('slot_start_time')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings'   => $bookings,
            'filters'    => $filters,
            'statuses'   => BookingReportService::STATUSES,
            'allVendors' => Vendor::orderBy('business_name')->get(['id', 'business_name']),
            // The staff list only makes sense once a shop is picked; across the
            // whole platform it would be thousands of names with no context.
            'employees'  => $filters['vendor']
                ? Employee::where('vendor_id', $filters['vendor'])->orderBy('name')->get(['id', 'name'])
                : collect(),
            'summary'    => [
                'total'     => (int) $counts->sum(),
                'confirmed' => (int) $counts->get('confirmed', 0),
                'completed' => (int) $counts->get('completed', 0),
                'cancelled' => (int) $counts->get('cancelled', 0),
                'skipped'   => (int) $counts->get('skipped', 0),
                'pending'   => (int) $counts->get('pending', 0),
            ],
        ]);
    }

    /**
     * The requested filters, normalised: unknown statuses fall back to `all`,
     * ids to null, and a reversed date pair is swapped rather than rejected so a
     * fumbled picker still lists something.
     *
     * @return array{q: ?string, vendor: ?int, employee: ?int, status: string, from: ?string, to: ?string}
     */
    private function filters(Request $request): array
    {
        $status = (string) $request->query('status', 'all');
        $from   = $request->query('from') ?: null;
        $to     = $request->query('to') ?: null;

        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [
            'q'        => trim((string) $request->query('q', '')) ?: null,
            'vendor'   => ((int) $request->query('vendor')) ?: null,
            'employee' => ((int) $request->query('employee')) ?: null,
            'status'   => \array_key_exists($status, BookingReportService::STATUSES) ? $status : 'all',
            'from'     => $from,
            'to'       => $to,
        ];
    }

    /** Everything except the status filter — see index(). */
    private function scopedQuery(array $filters): Builder
    {
        return Booking::query()
            ->when($filters['vendor'], fn (Builder $q, $id) => $q->where('vendor_id', $id))
            ->when($filters['employee'], fn (Builder $q, $id) => $q->where('employee_id', $id))
            ->when($filters['from'], fn (Builder $q, $date) => $q->whereDate('booking_date', '>=', $date))
            ->when($filters['to'], fn (Builder $q, $date) => $q->whereDate('booking_date', '<=', $date))
            ->when($filters['q'], function (Builder $q, string $term) {
                $like = '%' . $term . '%';

                $q->where(function (Builder $inner) use ($term, $like) {
                    $inner->where('customer_name', 'like', $like)
                        ->orWhere('customer_phone', 'like', $like);

                    // Token and id are integer columns; only compare when the
                    // term actually is one, so "priya" doesn't reach them.
                    if (ctype_digit($term)) {
                        $inner->orWhere('token_number', (int) $term)
                            ->orWhere('id', (int) $term);
                    }
                });
            });
    }

    private function applyStatus(Builder $query, string $status): Builder
    {
        return $query->when($status !== 'all', fn (Builder $q) => $q->where('status', $status));
    }
}
