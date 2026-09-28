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
     * Unduh template CSV/Excel yang ramah Excel Windows (dengan UTF-8 BOM).
     */
    public function downloadTemplate(string $filename, array $headers, array $sampleRows = []): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $sampleRows) {
            $out = fopen('php://output', 'w');
            // Tulis UTF-8 BOM agar Excel Windows langsung membaca format dengan sempurna
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
