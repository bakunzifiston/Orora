<?php

namespace App\Services\Import\Concerns;

use App\Services\ImportExport\AnimalCsvSchema;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

trait ParsesAnimalImportFile
{
    use ParsesCsv;

    /**
     * @return array{0: list<string>, 1: array<int, array<string, string|null>>}
     */
    protected function readAnimalImportRows(UploadedFile $file, int $maxRows = 2000): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            return $this->readSpreadsheetAnimalRows($file, $maxRows);
        }

        return $this->readCsvAnimalRows($file, $maxRows);
    }

    /**
     * @return array{0: list<string>, 1: array<int, array<string, string|null>>}
     */
    protected function readCsvAnimalRows(UploadedFile $file, int $maxRows): array
    {
        $expected = AnimalCsvSchema::headers();
        $required = AnimalCsvSchema::requiredHeaders();
        $rows = [];

        foreach ($this->parseCsvRows($file, $expected, $maxRows, $required, AnimalCsvSchema::headerAliases()) as $rowNumber => $row) {
            $rows[$rowNumber] = $row;
        }

        return [$expected, $rows];
    }

    /**
     * @return array{0: list<string>, 1: array<int, array<string, string|null>>}
     */
    protected function readSpreadsheetAnimalRows(UploadedFile $file, int $maxRows): array
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new RuntimeException('Unable to read the uploaded spreadsheet.');
        }

        try {
            $spreadsheet = IOFactory::load($path);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException('Unable to read the Excel file. Export it as CSV or a valid .xlsx and try again.');
        }

        $sheet = $spreadsheet->getActiveSheet();
        $matrix = $sheet->toArray(null, true, true, false);

        if ($matrix === []) {
            throw new InvalidArgumentException('The spreadsheet is empty.');
        }

        $rawHeader = array_shift($matrix);

        if (! is_array($rawHeader)) {
            throw new InvalidArgumentException('The spreadsheet is missing a header row.');
        }

        $aliases = AnimalCsvSchema::headerAliases();
        $expected = AnimalCsvSchema::headers();
        $required = AnimalCsvSchema::requiredHeaders();

        $headers = [];
        foreach ($rawHeader as $index => $header) {
            $normalized = $this->normalizeCsvHeader((string) ($header ?? ''));
            $canonical = $aliases[$normalized] ?? $normalized;
            $headers[$index] = $canonical;
        }

        $present = array_values(array_unique(array_filter($headers)));
        $missing = array_values(array_diff($required, $present));

        if ($missing !== []) {
            throw new InvalidArgumentException(
                'Missing required columns: '.implode(', ', $missing).'.'
            );
        }

        $rows = [];
        $dataRows = 0;
        $rowNumber = 1;

        foreach ($matrix as $rawRow) {
            $rowNumber++;

            if (! is_array($rawRow) || $this->csvRowIsEmpty($rawRow)) {
                continue;
            }

            $dataRows++;

            if ($dataRows > $maxRows) {
                throw new InvalidArgumentException(
                    "The file exceeds the maximum of {$maxRows} data rows."
                );
            }

            $assoc = [];

            foreach ($headers as $index => $header) {
                if (! in_array($header, $expected, true)) {
                    continue;
                }

                $value = $rawRow[$index] ?? null;

                if (is_float($value) || is_int($value)) {
                    $value = $this->stringifySpreadsheetNumber($value);
                } elseif (is_string($value)) {
                    $value = $this->normalizeImportCell($value);
                } elseif ($value !== null) {
                    $value = $this->normalizeImportCell((string) $value);
                }

                $assoc[$header] = ($value === null || $value === '') ? null : $value;
            }

            foreach ($expected as $header) {
                $assoc[$header] ??= null;
            }

            $rows[$rowNumber] = $assoc;
        }

        if ($rows === []) {
            throw new InvalidArgumentException('The spreadsheet has a header row but no data rows.');
        }

        return [$expected, $rows];
    }

    protected function stringifySpreadsheetNumber(int|float $value): string
    {
        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(sprintf('%.8F', $value), '0'), '.');
    }

    protected function normalizeImportCell(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value;
    }
}
