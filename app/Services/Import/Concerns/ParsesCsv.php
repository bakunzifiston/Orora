<?php

namespace App\Services\Import\Concerns;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

trait ParsesCsv
{
    /**
     * @param  list<string>  $expectedHeaders
     * @return \Generator<int, array<string, string|null>>
     */
    protected function parseCsvRows(UploadedFile $file, array $expectedHeaders, int $maxRows = 2000): \Generator
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new RuntimeException('Unable to read the uploaded CSV file.');
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded CSV file.');
        }

        try {
            $headerRow = fgetcsv($handle);

            if ($headerRow === false || $headerRow === [null]) {
                throw new InvalidArgumentException('The CSV file is empty.');
            }

            $headers = array_map(fn ($header) => $this->normalizeCsvHeader((string) $header), $headerRow);

            if ($headers === [] || $headers[0] === '') {
                throw new InvalidArgumentException('The CSV file is missing a header row.');
            }

            $missing = array_values(array_diff($expectedHeaders, $headers));

            if ($missing !== []) {
                throw new InvalidArgumentException(
                    'Missing required CSV columns: '.implode(', ', $missing).'.'
                );
            }

            $rowNumber = 1;
            $dataRows = 0;

            while (($row = fgetcsv($handle)) !== false) {
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
                    $assoc[$header] = is_string($value) ? trim($value) : $value;

                    if ($assoc[$header] === '') {
                        $assoc[$header] = null;
                    }
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

    protected function normalizeCsvHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;

        return strtolower(trim($header));
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
