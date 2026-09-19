<?php

namespace App\Services\Import\Concerns;

use DateTime;

trait NormalizesDates
{
    protected bool $preferMonthFirstDates = false;

    /**
     * A slash date is only ambiguous when both parts are 12 or lower, so a
     * single value such as 9/16/2026 proves the whole file is month first.
     * Deciding per file keeps a sequence like 9/30, 10/1, 10/2 intact, which
     * reading each value in isolation would not.
     *
     * @param  iterable<mixed>  $values
     */
    protected function detectImportDateConvention(iterable $values): void
    {
        $monthFirst = false;
        $dayFirst = false;

        foreach ($values as $value) {
            if (! is_string($value) || preg_match('#^(\d{1,2})/(\d{1,2})/\d{4}$#', trim($value), $parts) !== 1) {
                continue;
            }

            $monthFirst = $monthFirst || (int) $parts[2] > 12;
            $dayFirst = $dayFirst || (int) $parts[1] > 12;
        }

        $this->preferMonthFirstDates = $monthFirst && ! $dayFirst;
    }

    /**
     * Formats are tried in order, so day/month/year wins ambiguous values
     * such as 3/4/2026 unless the file proved itself to be month first.
     *
     * @return list<string>
     */
    protected function importDateFormats(): array
    {
        return [
            'Y-m-d',
            'Y/m/d',
            'd-m-Y',
            'd.m.Y',
            ...$this->preferMonthFirstDates ? ['m/d/Y', 'd/m/Y'] : ['d/m/Y', 'm/d/Y'],
        ];
    }

    /**
     * Returns the value as Y-m-d, or unchanged when no format matches so that
     * validation can report it as an invalid date.
     */
    protected function normalizeImportDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        foreach ($this->importDateFormats() as $format) {
            $date = DateTime::createFromFormat('!'.$format, $value);
            $errors = DateTime::getLastErrors();

            if ($date === false || ($errors !== false && $errors['error_count'] + $errors['warning_count'] > 0)) {
                continue;
            }

            return $date->format('Y-m-d');
        }

        return $value;
    }

    protected function isNormalizedDate(?string $value): bool
    {
        return $value !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }
}
