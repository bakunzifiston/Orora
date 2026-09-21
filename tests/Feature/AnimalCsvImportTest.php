<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Livestock;
use App\Services\Import\AnimalCsvImporter;
use App\Services\ImportExport\AnimalCsvSchema;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        $this->assertSame(3, $result['total']);
        $this->assertSame([], $result['errors']);

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
        $this->assertTrue(collect($result['warnings'])->contains(fn ($w) => str_contains($w['message'], 'NAN-0001')));
    }

    public function test_it_reports_a_future_date_of_birth_as_an_adjustment(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);

        $result = $this->importRows("\t", [
            $this->row(['tag_number' => '8337', 'date_of_birth' => now()->addMonth()->format('d/m/Y')]),
        ]);

        $this->assertSame(1, $result['created']);
        $this->assertTrue(collect($result['warnings'])->contains(fn ($w) => str_contains($w['message'], 'future')));
    }

    public function test_it_still_imports_comma_separated_files(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);

        $result = $this->importRows(',', [
            $this->row(['tag_number' => 'RW-001', 'name' => 'Bella', 'date_of_birth' => '2022-05-10']),
        ]);

        $this->assertSame(1, $result['created']);
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
        $animal = Animal::query()->where('tag_number', '2044169')->first();
        $this->assertSame('Pregnant', $animal->health_status);
        $this->assertSame('Gestating', $animal->production_status);
        $this->assertSame('Born on farm', $animal->acquisition_type);
    }

    public function test_it_creates_a_missing_livestock_group(): void
    {
        FarmTestFixtures::farm(['name' => 'Nandi farm']);

        $result = $this->importRows("\t", [
            $this->row([
                'farm_name' => 'Nandi farm',
                'livestock_name' => 'Dairy herd',
                'tag_number' => '2044169',
                'name' => 'Mariza',
                'health_status' => 'Healthy',
                'production_status' => 'pregnancy cow',
                'species' => 'Cattle',
            ]),
        ]);

        $this->assertSame(1, $result['created'], json_encode($result['errors']));
        $this->assertDatabaseHas('livestock', ['name' => 'Dairy herd']);
    }

    public function test_it_imports_rows_with_blank_fields_using_defaults(): void
    {
        FarmTestFixtures::farm(['name' => 'Nandi farm']);

        $result = $this->importRows("\t", [
            [
                'farm_name' => 'Nandi farm',
                'livestock_name' => 'Dairy herd',
                'tag_number' => '',
                'name' => '',
                'gender' => '',
                'health_status' => '',
                'lifecycle_status' => '',
                'date_of_birth' => '',
                'weight_kg' => '',
                'color_markings' => '',
                'species' => '',
                'breed' => '',
                'acquisition_type' => '',
                'acquisition_date' => '',
                'source' => '',
                'mother_tag' => '',
                'father_tag' => '',
                'production_status' => '',
                'current_condition' => '',
                'notes' => '',
            ],
        ]);

        $this->assertSame(1, $result['created'], json_encode($result['errors']));
        $animal = Animal::query()->first();
        $this->assertSame($animal->tag_number, $animal->name);
        $this->assertSame('unknown', $animal->gender);
        $this->assertSame('Healthy', $animal->health_status);
        $this->assertSame('Active', $animal->lifecycle_status);
    }

    public function test_it_rejects_duplicate_tags_in_file_and_keeps_valid_rows(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);

        $result = $this->importRows(',', [
            $this->row(['tag_number' => 'DUP-1', 'name' => 'First']),
            $this->row(['tag_number' => 'DUP-1', 'name' => 'Second']),
            $this->row(['tag_number' => 'OK-1', 'name' => 'Third']),
        ]);

        $this->assertSame(2, $result['created']);
        $this->assertSame(1, $result['failed']);
        $this->assertSame(3, $result['total']);
        $this->assertCount(1, Animal::query()->where('tag_number', 'DUP-1')->get());
        $this->assertDatabaseHas('animals', ['tag_number' => 'OK-1']);
        $this->assertStringContainsString('Duplicate tag number', $result['errors'][0]['message']);
    }

    public function test_it_rejects_tags_already_in_the_database(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        $livestock = FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);
        FarmTestFixtures::animal($farm, $livestock, 'female', ['tag_number' => 'EXIST-1', 'name' => 'Existing']);

        $result = $this->importRows(',', [
            $this->row(['tag_number' => 'EXIST-1', 'name' => 'Again']),
            $this->row(['tag_number' => 'NEW-1', 'name' => 'Fresh']),
        ]);

        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['failed']);
        $this->assertStringContainsString('already exists', $result['errors'][0]['message']);
        $this->assertDatabaseHas('animals', ['tag_number' => 'NEW-1']);
    }

    public function test_it_fails_missing_farm_without_creating_animals(): void
    {
        $result = $this->importRows(',', [
            $this->row(['farm_name' => 'Missing Farm', 'tag_number' => 'X-1']),
        ]);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['failed']);
        $this->assertSame(0, Animal::query()->count());
        $this->assertSame(0, Livestock::query()->count());
        $this->assertStringContainsString('was not found', $result['errors'][0]['message']);
    }

    public function test_it_imports_an_xlsx_file(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            AnimalCsvSchema::headers(),
            array_map(fn ($header) => $this->row([
                'tag_number' => 'XL-001',
                'name' => 'Excel Cow',
                'date_of_birth' => '2022-05-10',
            ])[$header], AnimalCsvSchema::headers()),
        ]);

        $path = tempnam(sys_get_temp_dir(), 'animals').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        $result = app(AnimalCsvImporter::class)->import(
            new UploadedFile($path, 'animals.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true)
        );

        $this->assertSame(1, $result['created'], json_encode($result['errors']));
        $this->assertDatabaseHas('animals', ['tag_number' => 'XL-001', 'name' => 'Excel Cow']);
    }

    public function test_it_maps_aliased_headers(): void
    {
        $farm = FarmTestFixtures::farm(['name' => 'Nandi Farm']);
        FarmTestFixtures::livestock($farm, ['name' => 'Cows (lactating)']);

        $path = tempnam(sys_get_temp_dir(), 'animals').'.csv';
        file_put_contents($path, "Farm Name,Livestock Group,Tag,Animal Name,Gender,Health Status,Lifecycle Status\n"
            ."Nandi Farm,Cows (lactating),ALIAS-1,Bella,female,Healthy,Active\n");

        $result = app(AnimalCsvImporter::class)->import(
            new UploadedFile($path, 'animals.csv', 'text/csv', null, true)
        );

        $this->assertSame(1, $result['created'], json_encode($result['errors']));
        $this->assertDatabaseHas('animals', ['tag_number' => 'ALIAS-1', 'name' => 'Bella']);
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
     * @return array{created: int, failed: int, total: int, errors: list<array{row: int, message: string}>, warnings: list<array{row: int, message: string}>}
     */
    private function importRows(string $delimiter, array $rows): array
    {
        $headers = AnimalCsvSchema::headers();
        $lines = [implode($delimiter, $headers)];

        foreach ($rows as $row) {
            $lines[] = implode($delimiter, array_map(fn ($header) => $row[$header] ?? '', $headers));
        }

        $path = tempnam(sys_get_temp_dir(), 'animals').'.csv';
        file_put_contents($path, implode("\r\n", $lines)."\r\n");

        return app(AnimalCsvImporter::class)->import(
            new UploadedFile($path, 'animals.csv', 'text/csv', null, true)
        );
    }
}
