<?php

namespace App\Services\Reports;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Turns a header row + body rows into a downloadable CSV or Excel file.
 *
 * Excel here means a real .xlsx — an OOXML package written by hand with
 * ZipArchive, not a CSV wearing an .xls extension. That trick works right up
 * until a customer name contains a comma or a phone number starts with a zero,
 * at which point Excel silently mangles the vendor's report; and it makes
 * Excel show a "the file format does not match" warning every single time.
 *
 * Writing the package directly keeps the dependency list where it is (no
 * PhpSpreadsheet) at the cost of about a hundred lines. Only what a flat
 * table needs is implemented: one sheet, inline strings, numbers as numbers,
 * and a bold header row.
 */
class SpreadsheetWriter
{
    /**
     * Stream the rows out as CSV.
     *
     * Streamed rather than built in memory: a financial-year report for a busy
     * shop is tens of thousands of rows, and fputcsv straight to the output
     * handle keeps that flat regardless of size.
     *
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public function csv(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows) {
            $handle = fopen('php://output', 'w');

            // BOM: without it Excel reads the file as ASCII and turns ₹ and any
            // non-Latin customer name into mojibake.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $headings);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename . '.csv', [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Build a single-sheet .xlsx and send it as a download.
     *
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function xlsx(string $filename, array $headings, array $rows, string $sheetTitle = 'Bookings'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');

        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook($sheetTitle));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheet($headings, $rows));

        $zip->close();

        return response()
            ->download($path, $filename . '.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    /*
    |--------------------------------------------------------------------------
    | OOXML parts
    |--------------------------------------------------------------------------
    */

    private function sheet(array $headings, array $rows): string
    {
        // Column widths are worth setting: without them every date and phone
        // number opens as ####, which reads as a broken export.
        $cols = '';
        foreach ($headings as $i => $heading) {
            $width = max(12, min(30, strlen((string) $heading) + 6));
            $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $width . '" customWidth="1"/>';
        }

        $xml = '<row r="1">';
        foreach ($headings as $i => $heading) {
            $xml .= $this->cell($this->columnLetter($i) . '1', $heading, 1);
        }
        $xml .= '</row>';

        $rowNumber = 1;
        foreach ($rows as $row) {
            $rowNumber++;
            $xml .= '<row r="' . $rowNumber . '">';
            foreach (array_values($row) as $i => $value) {
                $xml .= $this->cell($this->columnLetter($i) . $rowNumber, $value);
            }
            $xml .= '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . $cols . '</cols>'
            . '<sheetData>' . $xml . '</sheetData>'
            . '</worksheet>';
    }

    /**
     * One cell. Numbers go in as numbers so the vendor can total a column;
     * everything else goes in as an inline string, which keeps the package to
     * one part instead of needing a shared-string table.
     */
    private function cell(string $ref, mixed $value, int $style = 0): string
    {
        $styleAttr = $style ? ' s="' . $style . '"' : '';

        if (is_int($value) || is_float($value)) {
            return '<c r="' . $ref . '"' . $styleAttr . '><v>' . $value . '</v></c>';
        }

        if ($value === null || $value === '') {
            return '<c r="' . $ref . '"' . $styleAttr . '/>';
        }

        return '<c r="' . $ref . '"' . $styleAttr . ' t="inlineStr"><is><t xml:space="preserve">'
            . $this->escape((string) $value)
            . '</t></is></c>';
    }

    /** A → Z, AA → AZ, … */
    private function columnLetter(int $index): string
    {
        $letter = '';

        for ($i = $index; $i >= 0; $i = intdiv($i, 26) - 1) {
            $letter = chr(65 + $i % 26) . $letter;
        }

        return $letter;
    }

    /**
     * XML-escape, then strip the control characters XML forbids outright —
     * they reach us through free-text fields like customer notes and make the
     * whole workbook unopenable rather than just showing oddly.
     */
    private function escape(string $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? $value;

        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbook(string $sheetTitle): string
    {
        // Excel rejects sheet names over 31 chars or containing : \ / ? * [ ]
        $title = mb_substr(str_replace([':', '\\', '/', '?', '*', '[', ']'], '', $sheetTitle), 0, 31) ?: 'Sheet1';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $this->escape($title) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    /** Two formats only: index 0 plain, index 1 the bold header band. */
    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF2563EB"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }
}
