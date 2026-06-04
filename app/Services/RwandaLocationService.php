<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RwandaLocationService
{
    private const SOURCE_URL = 'https://raw.githubusercontent.com/jnkindi/rwanda-locations-json/master/locations.json';

    private static ?array $rows = null;

    public function dataPath(): string
    {
        return database_path('data/rwanda_locations.json');
    }

    public function ensureDataExists(): void
    {
        if (is_file($this->dataPath())) {
            return;
        }

        if ($this->downloadDataFile()) {
            return;
        }

        throw new RuntimeException(
            'Rwanda location data is missing. Expected this exact path on the server: '
            .$this->dataPath().'. '
            .'Run: php artisan rwanda:download-locations '
            .'or upload database/data/rwanda_locations.json from your computer (~6 MB).'
        );
    }

    public function downloadDataFile(): bool
    {
        $path = $this->dataPath();
        $directory = dirname($path);

        if (! is_dir($directory)) {
            if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                return false;
            }
        }

        try {
            $response = Http::timeout(180)->get(self::SOURCE_URL);

            if (! $response->successful()) {
                return false;
            }

            file_put_contents($path, $response->body());

            return is_file($path) && filesize($path) > 10_000;
        } catch (\Throwable) {
            return false;
        }
    }

    private function rows(): array
    {
        if (self::$rows !== null) {
            return self::$rows;
        }

        $this->ensureDataExists();

        $decoded = json_decode(file_get_contents($this->dataPath()), true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Invalid Rwanda locations data file.');
        }

        return self::$rows = $decoded;
    }

    public function provinces(): array
    {
        $provinces = [];

        foreach ($this->rows() as $row) {
            $code = (int) $row['province_code'];
            if (! isset($provinces[$code])) {
                $provinces[$code] = [
                    'code' => $code,
                    'name' => $this->formatName($row['province_name']),
                ];
            }
        }

        usort($provinces, fn ($a, $b) => $a['code'] <=> $b['code']);

        return array_values($provinces);
    }

    public function districts(?int $provinceCode = null): array
    {
        $districts = [];

        foreach ($this->rows() as $row) {
            if ($provinceCode !== null && (int) $row['province_code'] !== $provinceCode) {
                continue;
            }

            $code = (int) $row['district_code'];
            if (! isset($districts[$code])) {
                $districts[$code] = [
                    'code' => $code,
                    'name' => $this->formatName($row['district_name']),
                    'province_code' => (int) $row['province_code'],
                ];
            }
        }

        usort($districts, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return array_values($districts);
    }

    public function sectors(?int $districtCode = null): array
    {
        $sectors = [];

        foreach ($this->rows() as $row) {
            if ($districtCode !== null && (int) $row['district_code'] !== $districtCode) {
                continue;
            }

            $code = (string) $row['sector_code'];
            if (! isset($sectors[$code])) {
                $sectors[$code] = [
                    'code' => $code,
                    'name' => $this->formatName($row['sector_name']),
                    'district_code' => (int) $row['district_code'],
                ];
            }
        }

        usort($sectors, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return array_values($sectors);
    }

    public function cells(?string $sectorCode = null): array
    {
        $cells = [];

        foreach ($this->rows() as $row) {
            if ($sectorCode !== null && (string) $row['sector_code'] !== $sectorCode) {
                continue;
            }

            $code = (int) $row['cell_code'];
            if (! isset($cells[$code])) {
                $cells[$code] = [
                    'code' => $code,
                    'name' => $this->formatName($row['cell_name']),
                    'sector_code' => (string) $row['sector_code'],
                ];
            }
        }

        usort($cells, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return array_values($cells);
    }

    public function villages(?int $cellCode = null): array
    {
        $villages = [];

        foreach ($this->rows() as $row) {
            if ($cellCode !== null && (int) $row['cell_code'] !== $cellCode) {
                continue;
            }

            $code = (int) $row['village_code'];
            if (! isset($villages[$code])) {
                $villages[$code] = [
                    'code' => $code,
                    'name' => $this->formatName($row['village_name']),
                    'cell_code' => (int) $row['cell_code'],
                ];
            }
        }

        usort($villages, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return array_values($villages);
    }

    public function findProvince(int $code): ?array
    {
        return collect($this->provinces())->firstWhere('code', $code);
    }

    public function findDistrict(int $code): ?array
    {
        return collect($this->districts())->firstWhere('code', $code);
    }

    public function findSector(string $code): ?array
    {
        return collect($this->sectors())->firstWhere('code', $code);
    }

    public function findCell(int $code): ?array
    {
        return collect($this->cells())->firstWhere('code', $code);
    }

    public function findVillage(int $code): ?array
    {
        return collect($this->villages())->firstWhere('code', $code);
    }

    public function isValidSelection(
        int $provinceCode,
        int $districtCode,
        string $sectorCode,
        int $cellCode,
        int $villageCode
    ): bool {
        foreach ($this->rows() as $row) {
            if ((int) $row['province_code'] === $provinceCode
                && (int) $row['district_code'] === $districtCode
                && (string) $row['sector_code'] === $sectorCode
                && (int) $row['cell_code'] === $cellCode
                && (int) $row['village_code'] === $villageCode) {
                return true;
            }
        }

        return false;
    }

    private function formatName(string $name): string
    {
        return mb_convert_case(strtolower($name), MB_CASE_TITLE, 'UTF-8');
    }
}
