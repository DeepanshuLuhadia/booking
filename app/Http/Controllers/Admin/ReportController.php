<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\BookingReportService;
use App\Services\Reports\SpreadsheetWriter;
use Illuminate\Http\Request;

/**
 * Platform-wide booking reports for the admin panel.
 *
 * The same periods, statuses and formats the vendor panel offers, across every
 * shop instead of one — the shop multi-select is the admin's counterpart to
 * the vendor's staff filter, and rows carry a Business column so a report
 * spanning thirty shops is readable.
 *
 * Reachable only through the `admin.only` middleware on the /admin group.
 * That check did not exist before this controller did; every row here is
 * another shop's customer names and phone numbers, which is not something to
 * leave behind a bare `auth`.
 */
class ReportController extends Controller
{
    public function __construct(
        private BookingReportService $reports,
        private SpreadsheetWriter $writer,
    ) {
    }

    public function index(Request $request)
    {
        [$period, $status] = $this->selection($request);

        $range     = $this->reports->resolveRange($period, $request->input('from'), $request->input('to'));
        $vendors   = $this->reports->resolveVendors((array) $request->input('vendors', []));
        $vendorIds = $vendors->pluck('id')->all();

        $summary = $this->reports->platformSummary($range, $status, $vendorIds);

        $preview = $this->reports->platformQuery($range, $status, $vendorIds)
            ->paginate(25)
            ->withQueryString();

        return view('admin.reports.index', [
            'periods'    => BookingReportService::PERIODS,
            'statuses'   => BookingReportService::STATUSES,
            'period'     => $range['key'],
            'status'     => $status,
            'range'      => $range,
            'from'       => $request->input('from', $range['start']->toDateString()),
            'to'         => $request->input('to', $range['end']->toDateString()),
            'summary'    => $summary,
            'preview'    => $preview,
            'allVendors' => Vendor::orderBy('business_name')->get(['id', 'business_name']),
            'vendorIds'  => $vendorIds,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'format'    => 'nullable|in:csv,xlsx',
            'from'      => 'nullable|date',
            'to'        => 'nullable|date',
            'vendors'   => 'nullable|array',
            'vendors.*' => 'integer',
        ]);

        [$period, $status] = $this->selection($request);

        $range   = $this->reports->resolveRange($period, $request->input('from'), $request->input('to'));
        $vendors = $this->reports->resolveVendors((array) $request->input('vendors', []));

        $query    = $this->reports->platformQuery($range, $status, $vendors->pluck('id')->all());
        $filename = $this->reports->platformFilename($range, $status, $vendors);
        $columns  = $this->reports->columns(withVendor: true);

        if ($request->input('format', 'csv') === 'xlsx') {
            $rows = [];
            $query->chunk(500, function ($bookings) use (&$rows) {
                foreach ($this->reports->rows($bookings, withVendor: true) as $row) {
                    $rows[] = $row;
                }
            });

            return $this->writer->xlsx($filename, $columns, $rows, $range['label']);
        }

        return $this->writer->csv($filename, $columns, $this->streamRows($query));
    }

    /** @return array{0: string, 1: string} */
    private function selection(Request $request): array
    {
        $period = (string) $request->input('period', 'today');
        $status = (string) $request->input('status', 'all');

        return [
            \array_key_exists($period, BookingReportService::PERIODS) ? $period : 'today',
            \array_key_exists($status, BookingReportService::STATUSES) ? $status : 'all',
        ];
    }

    /** Yield report rows a chunk at a time, for the streaming CSV writer. */
    private function streamRows($query): \Generator
    {
        foreach ($query->lazy(500) as $booking) {
            yield $this->reports->rows(collect([$booking]), withVendor: true)[0];
        }
    }
}
