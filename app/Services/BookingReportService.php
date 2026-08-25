<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * The vendor's booking history, sliced by period and status, in a shape that
 * can be rendered on screen or written straight out to a spreadsheet.
 *
 * Two decisions are baked in here rather than left to each call site:
 *
 *   - THE FINANCIAL YEAR RUNS 1 APRIL → 31 MARCH. "This year" on an Indian
 *     shop's books is not the calendar year, and a report that quietly used
 *     January–December would not reconcile against anything the vendor files.
 *
 *   - REPORTS ARE FILED BY BUSINESS DATE (`booking_date`), not by the moment
 *     the row was created or by the real-world appointment time. That is the
 *     column the queue, the token sequence and the daily reset all group on,
 *     so a shop trading 22:00 → 02:00 gets one night on one line of the
 *     report instead of a shift split across two dates.
 */
class BookingReportService
{
    /** Month the financial year opens on. */
    private const FY_START_MONTH = 4;

    /**
     * Selectable reporting periods, in the order they are offered.
     * `custom` is resolved from the from/to dates the vendor picks.
     */
    public const PERIODS = [
        'today'       => 'Today',
        'yesterday'   => 'Yesterday',
        'week'        => 'This Week',
        'month'       => 'This Month',
        'current_fy'  => 'Current Year (Apr–Mar)',
        'previous_fy' => 'Previous Year (Apr–Mar)',
        'custom'      => 'Custom Range',
    ];

    /**
     * Selectable status filters. The keys are booking statuses; `all` is the
     * unfiltered set.
     */
    public const STATUSES = [
        'all'       => 'All Bookings',
        'confirmed' => 'Confirmed / Upcoming',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'skipped'   => 'Skipped',
        'expired'   => 'Expired',
        'pending'   => 'Pending Payment',
    ];

    /** Column headings of the exported sheet, in order. */
    public const COLUMNS = [
        'Booking ID',
        'Business Date',
        'Appointment Date',
        'Slot Start',
        'Slot End',
        'Token No.',
        'Customer',
        'Phone',
        'Specialist',
        'Source',
        'Status',
        'Token Amount',
        'Emergency Fee',
        'Paid Online',
        'Booked At',
    ];

    /**
     * Resolve a period key into a concrete inclusive date window.
     *
     * Custom ranges are clamped rather than rejected: a missing side falls back
     * to today, and a reversed pair is swapped, so a fumbled date picker still
     * produces a report instead of a validation dead end.
     *
     * @return array{start: Carbon, end: Carbon, label: string, key: string}
     */
    public function resolveRange(string $period, ?string $from = null, ?string $to = null): array
    {
        $today = Carbon::today();

        [$start, $end] = match ($period) {
            'yesterday'   => [$today->copy()->subDay(), $today->copy()->subDay()],
            'week'        => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'month'       => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'current_fy'  => $this->financialYear($today),
            'previous_fy' => $this->financialYear($today->copy()->subYear()),
            'custom'      => $this->customRange($from, $to, $today),
            default       => [$today->copy(), $today->copy()],
        };

        return [
            'key'   => array_key_exists($period, self::PERIODS) ? $period : 'today',
            'start' => $start->startOfDay(),
            'end'   => $end->endOfDay(),
            'label' => $this->rangeLabel($period, $start, $end),
        ];
    }

    /**
     * The financial year containing $date: 1 April of its year when the date
     * is in April or later, otherwise 1 April of the year before.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function financialYear(Carbon $date): array
    {
        $startYear = $date->month >= self::FY_START_MONTH ? $date->year : $date->year - 1;

        return [
            Carbon::create($startYear, self::FY_START_MONTH, 1)->startOfDay(),
            Carbon::create($startYear + 1, self::FY_START_MONTH, 1)->subDay()->endOfDay(),
        ];
    }

    /**
     * The vendor's bookings inside a resolved range, optionally narrowed to one
     * status and to a set of specialists. Ordered oldest-first so the sheet
     * reads as a ledger.
     *
     * An empty $employeeIds means every specialist, which is what makes the
     * staff filter safe to leave unset — "none selected" has to read as "all",
     * or an untouched filter would silently produce an empty report.
     *
     * @param  array{start: Carbon, end: Carbon}  $range
     * @param  array<int, int>  $employeeIds
     */
    public function query(Vendor $vendor, array $range, string $status = 'all', array $employeeIds = []): Builder
    {
        return $this->baseQuery($range, $status)
            ->where('vendor_id', $vendor->id)
            ->when($employeeIds !== [], fn (Builder $q) => $q->whereIn('employee_id', $employeeIds));
    }

    /**
     * The same report across every shop on the platform, for the admin panel,
     * optionally narrowed to a set of vendors.
     *
     * Deliberately a separate entry point rather than a nullable $vendor on
     * query(): an unscoped booking query is exactly the mistake that leaks one
     * shop's customers to another, and it should take a named method to write
     * one, not a forgotten argument.
     *
     * @param  array{start: Carbon, end: Carbon}  $range
     * @param  array<int, int>  $vendorIds
     */
    public function platformQuery(array $range, string $status = 'all', array $vendorIds = []): Builder
    {
        return $this->baseQuery($range, $status)
            ->when($vendorIds !== [], fn (Builder $q) => $q->whereIn('vendor_id', $vendorIds));
    }

    /**
     * Range + status, shared by both entry points. Carries no ownership scope
     * of its own, which is why it is private.
     *
     * @param  array{start: Carbon, end: Carbon}  $range
     */
    private function baseQuery(array $range, string $status): Builder
    {
        return Booking::query()
            /*
            | Legacy slots held for customers who never completed a payment are
            | not appointments and must not reach a report — they would be
            | counted, staffed for and charted as business that never happened.
            | Bookings made under the current flow are confirmed on arrival and
            | all pass through this filter untouched.
            */
            ->visibleToShop()
            // `vendor` is eager-loaded for the appointment_at accessor, which
            // needs the opening hours to place after-midnight slots correctly.
            ->with(['employee', 'vendor'])
            ->whereBetween('booking_date', [$range['start']->toDateString(), $range['end']->toDateString()])
            ->when($status !== 'all' && array_key_exists($status, self::STATUSES),
                fn (Builder $q) => $q->where('status', $status))
            ->orderBy('booking_date')
            ->orderBy('slot_start_time')
            ->orderBy('id');
    }

    /**
     * Headline numbers for the range — shown above the on-screen preview so the
     * vendor can sanity-check a report before downloading it.
     *
     * Counts are taken per status regardless of the status filter in play, so
     * the summary always describes the whole period; only `total` follows the
     * filter. The staff filter DOES apply throughout, because it scopes which
     * part of the business is being reported on rather than which slice of it
     * to list — a per-specialist report whose tiles counted the whole shop
     * would be actively misleading. Revenue counts money actually taken online.
     *
     * @param  array{start: Carbon, end: Carbon}  $range
     * @param  array<int, int>  $employeeIds
     */
    public function summary(Vendor $vendor, array $range, string $status = 'all', array $employeeIds = []): array
    {
        $counts = Booking::query()
            ->visibleToShop()
            ->where('vendor_id', $vendor->id)
            ->whereBetween('booking_date', [$range['start']->toDateString(), $range['end']->toDateString()])
            ->when($employeeIds !== [], fn (Builder $q) => $q->whereIn('employee_id', $employeeIds))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return $this->tally($counts, $this->query($vendor, $range, $status, $employeeIds));
    }

    /**
     * The same headline numbers across every shop, for the admin panel.
     *
     * Adds `vendors` — how many distinct shops the rows came from — which is
     * the one number a platform-wide report needs that a single shop's does
     * not.
     *
     * @param  array{start: Carbon, end: Carbon}  $range
     * @param  array<int, int>  $vendorIds
     */
    public function platformSummary(array $range, string $status = 'all', array $vendorIds = []): array
    {
        $counts = Booking::query()
            ->visibleToShop()
            ->whereBetween('booking_date', [$range['start']->toDateString(), $range['end']->toDateString()])
            ->when($vendorIds !== [], fn (Builder $q) => $q->whereIn('vendor_id', $vendorIds))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $filtered = $this->platformQuery($range, $status, $vendorIds);

        return $this->tally($counts, $filtered) + [
            'vendors' => (clone $filtered)->distinct()->count('vendor_id'),
        ];
    }

    /**
     * Shape a status tally plus a filtered query into the summary array both
     * panels render.
     *
     * @param  \Illuminate\Support\Collection<string, int>  $counts
     */
    private function tally(Collection $counts, Builder $filtered): array
    {
        return [
            'total'     => (clone $filtered)->count(),
            'all'       => (int) $counts->sum(),
            'confirmed' => (int) $counts->get('confirmed', 0),
            'completed' => (int) $counts->get('completed', 0),
            'cancelled' => (int) $counts->get('cancelled', 0),
            'skipped'   => (int) $counts->get('skipped', 0),
            'expired'   => (int) $counts->get('expired', 0),
            'pending'   => (int) $counts->get('pending', 0),
            'revenue'   => (float) (clone $filtered)->sum('online_paid_amount'),
        ];
    }

    /**
     * Sheet headings. The platform report carries an extra Business column —
     * without it, rows from thirty shops are indistinguishable.
     */
    public function columns(bool $withVendor = false): array
    {
        if (!$withVendor) {
            return self::COLUMNS;
        }

        $columns = self::COLUMNS;
        array_splice($columns, 1, 0, 'Business');

        return $columns;
    }

    /**
     * The report body: one flat row per booking, matching columns($withVendor).
     *
     * @param  \Illuminate\Support\Collection<int, Booking>  $bookings
     */
    public function rows(Collection $bookings, bool $withVendor = false): array
    {
        return $bookings->map(fn (Booking $booking) => array_merge([
            $booking->id,
        ], $withVendor ? [$booking->vendor?->business_name ?? ''] : [], [
            Carbon::parse($booking->booking_date)->format('d-m-Y'),
            $booking->appointment_at?->format('d-m-Y') ?? Carbon::parse($booking->booking_date)->format('d-m-Y'),
            $booking->appointment_at?->format('h:i A') ?? $booking->slot_start_time,
            $booking->appointment_end_at?->format('h:i A') ?? $booking->slot_end_time,
            $booking->token_number ?? '',
            $booking->customer_name,
            $booking->customer_phone ?? '',
            $booking->employee?->name ?? '',
            $booking->vendor_booked ? 'Walk-in (Shop)' : ucfirst($booking->booking_type ?? 'online'),
            ucfirst($booking->status),
            (float) ($booking->token_amount ?? 0),
            (float) ($booking->emergency_fee ?? 0),
            (float) ($booking->online_paid_amount ?? 0),
            $booking->created_at?->format('d-m-Y h:i A') ?? '',
        ]))->all();
    }

    /**
     * Narrow a list of requested specialist ids to the ones this vendor
     * actually employs.
     *
     * The booking query is already scoped by vendor_id, so a foreign id could
     * only ever return nothing — but filtering here means the UI and the
     * filename describe the same set the rows came from, instead of claiming a
     * specialist who was quietly ignored.
     *
     * @param  array<int, mixed>  $requested
     * @return \Illuminate\Support\Collection<int, \App\Models\Employee>
     */
    public function resolveEmployees(Vendor $vendor, array $requested): Collection
    {
        $ids = array_filter(array_map('intval', $requested));

        if ($ids === []) {
            return collect();
        }

        return $vendor->employees()->whereIn('id', $ids)->orderBy('name')->get();
    }

    /**
     * The admin counterpart of resolveEmployees(): requested vendor ids narrowed
     * to shops that actually exist, in name order.
     *
     * @param  array<int, mixed>  $requested
     * @return \Illuminate\Support\Collection<int, Vendor>
     */
    public function resolveVendors(array $requested): Collection
    {
        $ids = array_filter(array_map('intval', $requested));

        if ($ids === []) {
            return collect();
        }

        return Vendor::whereIn('id', $ids)->orderBy('business_name')->get();
    }

    /**
     * Filename stem for a platform-wide download.
     * e.g. "platform-bookings-completed-3-shops-01-04-2026-to-31-03-2027".
     *
     * @param  \Illuminate\Support\Collection|null  $vendors  the shop filter in play, if any
     */
    public function platformFilename(array $range, string $status, ?Collection $vendors = null): string
    {
        $scope = match (true) {
            !$vendors || $vendors->isEmpty() => null,
            $vendors->count() === 1          => \Illuminate\Support\Str::slug($vendors->first()->business_name),
            default                          => $vendors->count() . '-shops',
        };

        $parts = [
            'platform',
            'bookings',
            $status === 'all' ? null : $status,
            $scope,
            $range['start']->format('d-m-Y') . '-to-' . $range['end']->format('d-m-Y'),
        ];

        return implode('-', array_filter($parts));
    }

    /**
     * Filename stem for a download: shop, period, status and specialist, no
     * extension. e.g. "sharp-cuts-bookings-completed-priya-01-04-2026-to-31-03-2027".
     *
     * @param  \Illuminate\Support\Collection|null  $employees  the staff filter in play, if any
     */
    public function filename(Vendor $vendor, array $range, string $status, ?Collection $employees = null): string
    {
        // One specialist is worth naming; a handful is not — "3-staff" beats a
        // filename that runs past what a file manager will show.
        $staff = match (true) {
            !$employees || $employees->isEmpty() => null,
            $employees->count() === 1            => \Illuminate\Support\Str::slug($employees->first()->name),
            default                              => $employees->count() . '-staff',
        };

        $parts = [
            \Illuminate\Support\Str::slug($vendor->business_name ?: 'vendor'),
            'bookings',
            $status === 'all' ? null : $status,
            $staff,
            $range['start']->format('d-m-Y') . '-to-' . $range['end']->format('d-m-Y'),
        ];

        return implode('-', array_filter($parts));
    }

    /**
     * A missing or reversed custom range still has to produce something
     * sensible — see resolveRange().
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function customRange(?string $from, ?string $to, Carbon $today): array
    {
        $start = $this->parseDate($from) ?? $this->parseDate($to) ?? $today->copy();
        $end   = $this->parseDate($to) ?? $this->parseDate($from) ?? $today->copy();

        return $start->gt($end) ? [$end, $start] : [$start, $end];
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function rangeLabel(string $period, Carbon $start, Carbon $end): string
    {
        $label = self::PERIODS[$period] ?? self::PERIODS['today'];

        if ($start->isSameDay($end)) {
            return $label . ' — ' . $start->format('d M Y');
        }

        return $label . ' — ' . $start->format('d M Y') . ' to ' . $end->format('d M Y');
    }
}
