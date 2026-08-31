<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\BookingReportService;
use App\Services\Reports\SpreadsheetWriter;
use Illuminate\Http\Request;

/**
 * Booking reports for the vendor panel — on screen, and as a CSV/Excel
 * download.
 *
 * Gated on the shop's plan by the `reports.access` middleware: free-trial and
 * Premium shops only (see Vendor::hasReportAccess). Nothing in here re-checks
 * that — the route is the gate, and a second copy of the rule would be one
 * more thing to keep in step with the model.
 */
class ReportController extends Controller
{
    public function __construct(
        private BookingReportService $reports,
        private SpreadsheetWriter $writer,
    ) {
    }

    /**
     * The report builder: period + status pickers, headline numbers, and the
     * first page of matching rows so the vendor can see what they are about to
     * download before they download it.
     */
    public function index(Request $request)
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return redirect('/')->with('error', 'Vendor profile not found.');
        }

        [$period, $status] = $this->selection($request);

        $range     = $this->reports->resolveRange($period, $request->input('from'), $request->input('to'));
        $employees = $this->reports->resolveEmployees($vendor, (array) $request->input('employees', []));
        $staffIds  = $employees->pluck('id')->all();

        $summary = $this->reports->summary($vendor, $range, $status, $staffIds);

        // A preview, not the report — the file is what the vendor wants the
        // whole set in. Paginated rather than capped at a flat 25 so a year's
        // worth is still browsable on screen without rendering the lot.
        //
        // withQueryString() carries period/status/employees[] onto the page
        // links; without it, paging past page 1 would silently drop back to
        // today's unfiltered bookings under an unchanged-looking filter bar.
        $preview = $this->reports->query($vendor, $range, $status, $staffIds)
            ->paginate(25)
            ->withQueryString();

        return view('vendor.reports.index', [
            'vendor'    => $vendor,
            'periods'   => BookingReportService::PERIODS,
            'statuses'  => BookingReportService::STATUSES,
            'period'    => $range['key'],
            'status'    => $status,
            'range'     => $range,
            'from'      => $request->input('from', $range['start']->toDateString()),
            'to'        => $request->input('to', $range['end']->toDateString()),
            'summary'   => $summary,
            'preview'   => $preview,
            // Every specialist on the books drives the picker; the resolved
            // subset says which of them are ticked.
            'allStaff'  => $vendor->employees()->orderBy('name')->get(),
            'staffIds'  => $staffIds,
        ]);
    }

    /** Download the current selection as a spreadsheet. */
    public function export(Request $request)
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return redirect('/')->with('error', 'Vendor profile not found.');
        }

        $request->validate([
            'format'      => 'nullable|in:csv,xlsx',
            'from'        => 'nullable|date',
            'to'          => 'nullable|date',
            'employees'   => 'nullable|array',
            'employees.*' => 'integer',
        ]);

        [$period, $status] = $this->selection($request);

        $range     = $this->reports->resolveRange($period, $request->input('from'), $request->input('to'));
        $employees = $this->reports->resolveEmployees($vendor, (array) $request->input('employees', []));

        $query    = $this->reports->query($vendor, $range, $status, $employees->pluck('id')->all());
        $filename = $this->reports->filename($vendor, $range, $status, $employees);

        if ($request->input('format', 'csv') === 'xlsx') {
            // .xlsx has to be assembled whole before it can be zipped, so it is
            // read in chunks rather than hydrating the entire year at once.
            $rows = [];
            $query->chunk(500, function ($bookings) use (&$rows) {
                foreach ($this->reports->rows($bookings) as $row) {
                    $rows[] = $row;
                }
            });

            return $this->writer->xlsx(
                $filename,
                $this->reports->columns(),
                $rows,
                $range['label']
            );
        }

        // CSV streams, so the response starts before the query has finished and
        // memory stays flat however long the period is.
        return $this->writer->csv(
            $filename,
            $this->reports->columns(),
            $this->streamRows($query)
        );
    }

    /**
     * The period/status pair the request is asking for, falling back to today's
     * full booking list when either is missing or not one we offer.
     *
     * @return array{0: string, 1: string}
     */
    private function selection(Request $request): array
    {
        $period = (string) $request->input('period', 'today');
        $status = (string) $request->input('status', 'all');

        return [
            array_key_exists($period, BookingReportService::PERIODS) ? $period : 'today',
            array_key_exists($status, BookingReportService::STATUSES) ? $status : 'all',
        ];
    }

    /** Yield report rows a chunk at a time, for the streaming CSV writer. */
    private function streamRows($query): \Generator
    {
        foreach ($query->lazy(500) as $booking) {
            yield $this->reports->rows(collect([$booking]))[0];
        }
    }
}
