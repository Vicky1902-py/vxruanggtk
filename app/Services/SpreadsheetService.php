<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class SpreadsheetService
{
    /**
     * Parse file (XLSX atau CSV) menjadi array 2 dimensi.
     * Baris pertama biasanya header kolom.
     *
     * @param UploadedFile|string $file
     * @return array
     */
    public function parseFile($file): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $extension = strtolower($file instanceof UploadedFile ? $file->getClientOriginalExtension() : pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'xlsx') {
            return $this->parseXlsx($path);
        }

        return $this->parseCsv($path);
    }

    /**
     * Parsing file .xlsx OpenXML tanpa library eksternal via ZipArchive & SimpleXML.
     */
    public function parseXlsx(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            return $this->parseCsv($filePath);
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Baca shared strings
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = @simplexml_load_string($sharedStringsXml);
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Baca lembar kerja utama (sheet1.xml)
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            // Coba temukan sheet apapun
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_starts_with($name, 'xl/worksheets/sheet') && str_ends_with($name, '.xml')) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $rows = [];
        if ($sheetXml !== false) {
            $xml = @simplexml_load_string($sheetXml);
            if ($xml && isset($xml->sheetData->row)) {
                foreach ($xml->sheetData->row as $row) {
                    $rowData = [];
                    $lastColIndex = 0;

                    foreach ($row->c as $cell) {
                        $ref = (string) $cell['r']; // e.g. A1, B2, C10
                        $colLetters = preg_replace('/[0-9]/', '', $ref);
                        $colIndex = $this->letterToColumnIndex($colLetters);

                        // Isi sel kosong jika ada lompatan kolom
                        while ($lastColIndex < $colIndex - 1) {
                            $rowData[] = '';
                            $lastColIndex++;
                        }

                        $type = (string) $cell['t'];
                        $value = '';

                        if ($type === 's') {
                            // Shared string index
                            $idx = (int) $cell->v;
                            $value = $sharedStrings[$idx] ?? '';
                        } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                            $value = (string) $cell->is->t;
                        } elseif (isset($cell->v)) {
                            $value = (string) $cell->v;
                        }

                        $rowData[] = trim($value);
                        $lastColIndex = $colIndex;
                    }

                    // Hanya masukkan baris jika tidak seluruhnya kosong
                    if (!empty(array_filter($rowData, fn ($v) => $v !== ''))) {
                        $rows[] = $rowData;
                    }
                }
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * Konversi huruf kolom Excel (A, B, C, ..., Z, AA, AB) ke index 1-based.
     */
    protected function letterToColumnIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $len = strlen($letters);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($letters[$i]) - 64);
        }
        return $num;
    }

    /**
     * Parsing file CSV dengan auto-detect delimiter dan pembersihan BOM UTF-8.
     */
    public function parseCsv(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        // Hapus UTF-8 BOM jika ada
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (empty($lines)) {
            return [];
        }

        // Deteksi delimiter dari baris pertama
        $firstLine = $lines[0];
        $delimiters = [',', ';', "\t", '|'];
        $bestDelimiter = ',';
        $maxCount = 0;
        foreach ($delimiters as $delim) {
            $count = substr_count($firstLine, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $bestDelimiter = $delim;
            }
        }

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $data = str_getcsv($line, $bestDelimiter);
            $cleanData = array_map('trim', $data);
            if (!empty(array_filter($cleanData, fn ($v) => $v !== ''))) {
                $rows[] = $cleanData;
            }
        }

        return $rows;
    }

    /**
     * Unduh template file Excel .xlsx asli (OpenXML Spreadsheet).
     * Jika ZipArchive tidak tersedia di sistem, otomatis fallback ke CSV ber-BOM.
     */
    public function downloadTemplate(string $filename, array $headers, array $sampleRows = []): StreamedResponse
    {
        // Ubah ekstensi menjadi .xlsx jika belum
        $baseName = preg_replace('/\.(xlsx|csv)$/i', '', $filename);
        $xlsxFilename = $baseName . '.xlsx';

        if (class_exists('ZipArchive')) {
            return $this->downloadXlsx($xlsxFilename, $headers, $sampleRows);
        }

        return $this->downloadCsv($baseName . '.csv', $headers, $sampleRows);
    }

    /**
     * Buat dan unduh file Microsoft Excel OpenXML (.xlsx) asli secara streaming.
     */
    public function downloadXlsx(string $filename, array $headers, array $sampleRows = [], string $sheetName = 'Data'): StreamedResponse
    {
        if (!str_ends_with(strtolower($filename), '.xlsx')) {
            $filename .= '.xlsx';
        }

        $safeSheetName = preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $sheetName);
        $safeSheetName = trim(substr($safeSheetName ?: 'Data', 0, 31));

        return response()->streamDownload(function () use ($headers, $sampleRows, $safeSheetName) {
            $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_tmpl_');
            $zip = new ZipArchive();

            if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                // 1. [Content_Types].xml
                $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                    '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
                    '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
                    '<Default Extension="xml" ContentType="application/xml"/>' .
                    '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
                    '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
                    '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
                    '</Types>');

                // 2. _rels/.rels
                $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                    '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
                    '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
                    '</Relationships>');

                // 3. xl/_rels/workbook.xml.rels
                $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                    '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
                    '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
                    '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
                    '</Relationships>');

                // 4. xl/workbook.xml
                $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                    '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
                    '<sheets><sheet name="' . htmlspecialchars($safeSheetName, ENT_XML1, 'UTF-8') . '" sheetId="1" r:id="rId1"/></sheets>' .
                    '</workbook>');

                // 5. xl/styles.xml (Format font standar Calibri 11pt)
                $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                    '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
                    '<fonts count="1"><font><name val="Calibri"/><sz val="11"/></font></fonts>' .
                    '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>' .
                    '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>' .
                    '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' .
                    '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>' .
                    '</styleSheet>');

                // 6. xl/worksheets/sheet1.xml
                $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
                    '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

                // Row 1: Headers
                $sheet .= '<row r="1">';
                foreach ($headers as $colIdx => $h) {
                    $colLetter = $this->indexToColumnLetter($colIdx + 1);
                    $val = htmlspecialchars((string) $h, ENT_XML1, 'UTF-8');
                    $sheet .= '<c r="' . $colLetter . '1" t="inlineStr"><is><t>' . $val . '</t></is></c>';
                }
                $sheet .= '</row>';

                // Rows 2..N: Samples
                $rowNum = 2;
                foreach ($sampleRows as $row) {
                    $sheet .= '<row r="' . $rowNum . '">';
                    foreach ($row as $colIdx => $cell) {
                        $colLetter = $this->indexToColumnLetter($colIdx + 1);
                        $val = htmlspecialchars((string) $cell, ENT_XML1, 'UTF-8');
                        $sheet .= '<c r="' . $colLetter . $rowNum . '" t="inlineStr"><is><t>' . $val . '</t></is></c>';
                    }
                    $sheet .= '</row>';
                    $rowNum++;
                }

                $sheet .= '</sheetData></worksheet>';
                $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
                $zip->close();

                // Baca file dan stream ke browser
                readfile($tempFile);
                @unlink($tempFile);
            }
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Export dataset ke file Excel (.xlsx).
     */
    public function exportXlsx(string $filename, array $headers, array $rows = [], string $sheetName = 'Data'): StreamedResponse
    {
        return $this->downloadXlsx($filename, $headers, $rows, $sheetName);
    }

    /**
     * Konversi index kolom 1-based ke huruf Excel (1 -> A, 2 -> B, ..., 27 -> AA).
     */
    protected function indexToColumnLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $mod = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIndex = intval(($colIndex - $mod) / 26);
        }
        return $letter;
    }

    /**
     * Unduh template CSV ber-BOM sebagai alternatif.
     */
    public function downloadCsv(string $filename, array $headers, array $sampleRows = []): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $sampleRows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            foreach ($sampleRows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
