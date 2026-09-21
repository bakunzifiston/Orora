<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Services\Import\AnimalCsvImporter;
use App\Services\ImportExport\AnimalCsvSchema;
use Illuminate\Http\UploadedFile;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class AnimalCsvImportTest extends TenantTestCase
{
    public function test_it_imports_a_tab_separated_spreadsheet_export(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);

        $future = now()->addMonth();

        $result = $this->importRows("\t", [
            $this->row(['tag_number' => '8337', 'name' => 'Mbabazi', 'date_of_birth' => '9/16/2024']),
            $this->row(['tag_number' => '', 'name' => 'Kirezi', 'date_of_birth' => '3/4/2024']),
            $this->row(['tag_number' => '417344', 'name' => 'Ijyeri', 'date_of_birth' => $future->format('n/j/Y')]),
        ]);

        $this->assertSame(3, $result['created']);
        $this->assertSame(0, $result['failed']);
        $this->assertSame([], $result['errors']);

        // 9/16 can only be month/day/year, which settles the format for the file.
        $this->assertSame('2024-09-16', Animal::query()->where('name', 'Mbabazi')->value('date_of_birth')->toDateString());
        $this->assertSame('2024-03-04', Animal::query()->where('name', 'Kirezi')->value('date_of_birth')->toDateString());

        $this->assertNull(Animal::query()->where('name', 'Ijyeri')->value('date_of_birth'));
    }

    public function test_it_reads_ambiguous_dates_as_day_month_year(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);

        $result = $this->importRows("\t", [
            $this->row(['tag_number' => '8337', 'name' => 'Mbabazi', 'date_of_birth' => '3/4/2024']),
        ]);

        $this->assertSame(1, $result['created']);
        $this->assertSame('2024-04-03', Animal::query()->where('name', 'Mbabazi')->value('date_of_birth')->toDateString());
    }

    public function test_it_assigns_tag_numbers_to_rows_that_have_none(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);

        $result = $this->importRows("\t", [
            $this->row(['tag_number' => '', 'name' => 'Kirezi']),
            $this->row(['tag_number' => '', 'name' => 'Muga']),
        ]);

        $this->assertSame(2, $result['created']);
        $this->assertSame(['NAN-0001', 'NAN-0002'], Animal::query()->orderBy('id')->pluck('tag_number')->all());

        $this->assertCount(2, $result['warnings']);
        $this->assertStringContainsString('NAN-0001', $result['warnings'][0]['message']);
    }

    public function test_it_reports_a_future_date_of_birth_as_an_adjustment(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);

        $result = $this->importRows("\t", [
            $this->row(['tag_number' => '8337', 'date_of_birth' => now()->addMonth()->format('d/m/Y')]),
        ]);

        $this->assertSame(1, $result['created']);
        $this->assertCount(1, $result['warnings']);
        $this->assertStringContainsString('future', $result['warnings'][0]['message']);
    }

    public function test_it_still_imports_comma_separated_files(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);

        $result = $this->importRows(',', [
            $this->row(['tag_number' => 'RW-001', 'name' => 'Bella', 'date_of_birth' => '2022-05-10']),
        ]);

        $this->assertSame(1, $result['created']);
        $this->assertSame([], $result['warnings']);
        $this->assertDatabaseHas('animals', ['tag_number' => 'RW-001', 'name' => 'Bella']);
    }

    public function test_it_maps_pregnancy_cow_so_health_filters_find_the_animal(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Dairy herd']);

        $result = $this->importRows("\t", [
            $this->row([
                'livestock_name' => 'Dairy herd',
                'tag_number' => '2044169',
                'name' => 'Mariza',
                'health_status' => 'Healthy',
                'production_status' => 'pregnancy cow',
                'acquisition_type' => 'born in the farm',
                'date_of_birth' => '10/5/2022',
            ]),
        ]);

        $this->assertSame(1, $result['created'], json_encode($result['errors']));
        $this->assertSame(0, $result['failed']);

        $animal = Animal::query()->where('tag_number', '2044169')->first();

        $this->assertNotNull($animal);
        $this->assertSame('Pregnant', $animal->health_status);
        $this->assertSame('Gestating', $animal->production_status);
        $this->assertSame('Born on farm', $animal->acquisition_type);
        $this->assertSame('2022-05-10', $animal->date_of_birth->toDateString());

        $messages = collect($result['warnings'])->pluck('message')->implode(' ');
        $this->assertStringContainsString('pregnancy cow', $messages);
        $this->assertStringContainsString('Pregnant', $messages);
    }

    public function test_it_maps_pregnant_health_aliases(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Dairy herd']);

        $result = $this->importRows("\t", [
            $this->row([
                'livestock_name' => 'Dairy herd',
                'tag_number' => 'P-1',
                'name' => 'Bella',
                'health_status' => 'pregnancy',
                'production_status' => '',
            ]),
        ]);

        $this->assertSame(1, $result['created']);
        $this->assertDatabaseHas('animals', [
            'tag_number' => 'P-1',
            'health_status' => 'Pregnant',
            'production_status' => null,
        ]);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function row(array $overrides = []): array
    {
        return array_merge([
            'farm_name' => 'Nandi Farm',
            'livestock_name' => 'Cows (lactating)',
            'tag_number' => '',
            'name' => 'Mbabazi',
            'gender' => 'female',
            'health_status' => 'Healthy',
            'lifecycle_status' => 'Active',
            'date_of_birth' => '',
            'weight_kg' => '500',
            'color_markings' => 'black and white',
            'species' => 'Cattle',
            'breed' => 'cross freisian',
            'acquisition_type' => 'Purchased',
            'acquisition_date' => '',
            'source' => 'farm',
            'mother_tag' => '',
            'father_tag' => '',
            'production_status' => 'Lactating',
            'current_condition' => 'Good',
            'notes' => '',
        ], $overrides);
    }

    /**
     * @param  list<array<string, string>>  $rows
     * @return array{created: int, failed: int, errors: list<array{row: int, message: string}>, warnings: list<array{row: int, message: string}>}
     */
    private function importRows(string $delimiter, array $rows): array
    {
        $headers = AnimalCsvSchema::headers();
        $lines = [implode($delimiter, $headers)];

        foreach ($rows as $row) {
            $lines[] = implode($delimiter, array_map(fn ($header) => $row[$header], $headers));
        }

        // Spreadsheets export CRLF line endings.
        $path = tempnam(sys_get_temp_dir(), 'animals').'.csv';
        file_put_contents($path, implode("\r\n", $lines)."\r\n");

        return app(AnimalCsvImporter::class)->import(
            new UploadedFile($path, 'animals.csv', 'text/csv', null, true)
        );
    }
}
