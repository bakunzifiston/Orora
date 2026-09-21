<?php

namespace App\Services\Import\Concerns;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

trait ParsesCsv
{
    /**
     * @param  list<string>  $expectedHeaders
     * @param  list<string>|null  $requiredHeaders
     * @param  array<string, string>  $headerAliases
     * @return \Generator<int, array<string, string|null>>
     */
    protected function parseCsvRows(
        UploadedFile $file,
        array $expectedHeaders,
        int $maxRows = 2000,
        ?array $requiredHeaders = null,
        array $headerAliases = [],
    ): \Generator {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new RuntimeException('Unable to read the uploaded CSV file.');
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded CSV file.');
        }

        $requiredHeaders ??= $expectedHeaders;

        try {
            $delimiter = $this->detectCsvDelimiter($handle, $expectedHeaders, $headerAliases);
            rewind($handle);

            $headerRow = fgetcsv($handle, 0, $delimiter, '"', '\\');

            if ($headerRow === false || $headerRow === [null]) {
                throw new InvalidArgumentException('The CSV file is empty.');
            }

            $headers = [];

            foreach ($headerRow as $index => $header) {
                $normalized = $this->normalizeCsvHeader((string) $header);
                $headers[$index] = $headerAliases[$normalized] ?? $normalized;
            }

            if ($headers === [] || ($headers[0] ?? '') === '') {
                throw new InvalidArgumentException('The CSV file is missing a header row.');
            }

            $present = array_values(array_unique(array_filter($headers)));
            $missing = array_values(array_diff($requiredHeaders, $present));

            if ($missing !== []) {
                throw new InvalidArgumentException(
                    'Missing required CSV columns: '.implode(', ', $missing).'.'
                );
            }

            $rowNumber = 1;
            $dataRows = 0;

            while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
                $rowNumber++;

                if ($this->csvRowIsEmpty($row)) {
                    continue;
                }

                $dataRows++;

                if ($dataRows > $maxRows) {
                    throw new InvalidArgumentException(
                        "The CSV file exceeds the maximum of {$maxRows} data rows."
                    );
                }

                $assoc = [];

                foreach ($headers as $index => $header) {
                    if (! in_array($header, $expectedHeaders, true)) {
                        continue;
                    }

                    $value = $row[$index] ?? null;

                    if (is_string($value)) {
                        $value = method_exists($this, 'normalizeImportCell')
                            ? $this->normalizeImportCell($value)
                            : trim($value);
                    }

                    $assoc[$header] = ($value === null || $value === '') ? null : $value;
                }

                foreach ($expectedHeaders as $header) {
                    $assoc[$header] ??= null;
                }

                yield $rowNumber => $assoc;
            }

            if ($dataRows === 0) {
                throw new InvalidArgumentException('The CSV file has a header row but no data rows.');
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  resource  $handle
     * @param  list<string>  $expectedHeaders
     * @param  array<string, string>  $headerAliases
     */
    protected function detectCsvDelimiter($handle, array $expectedHeaders, array $headerAliases = []): string
    {
        $line = fgets($handle);

        if ($line === false) {
            return ',';
        }

        $best = ',';
        $bestScore = -1;

        foreach ([',', "\t", ';', '|'] as $delimiter) {
            $headers = [];

            foreach (str_getcsv($line, $delimiter, '"', '\\') as $header) {
                $normalized = $this->normalizeCsvHeader((string) $header);
                $headers[] = $headerAliases[$normalized] ?? $normalized;
            }

            $score = count(array_intersect($expectedHeaders, $headers));

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $delimiter;
            }
        }

        return $best;
    }

    protected function normalizeCsvHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = strtolower(trim($header));
        $header = preg_replace('/\s+/u', ' ', $header) ?? $header;

        return $header;
    }

    /**
     * @param  list<string|null>|false  $row
     */
    protected function csvRowIsEmpty(array|false $row): bool
    {
        if ($row === false) {
            return true;
        }

        foreach ($row as $value) {
            if (is_string($value) && trim($value) !== '') {
                return false;
            }

            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }
}
