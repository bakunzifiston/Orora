<?php

namespace App\Services\Export;

use App\Models\Animal;
use App\Services\Export\Concerns\StreamsCsv;
use App\Services\ImportExport\AnimalCsvSchema;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnimalCsvExporter
{
    use StreamsCsv;

    public function export(Request $request): StreamedResponse
    {
        $headers = AnimalCsvSchema::headers();
        $filename = 'animals-'.now()->format('Y-m-d-His').'.csv';

        $query = Animal::query()
            ->with(['farm', 'livestock'])
            ->when($request->filled('farm_id'), fn ($q) => $q->where('farm_id', $request->integer('farm_id')))
            ->when($request->filled('livestock_id'), fn ($q) => $q->where('livestock_id', $request->integer('livestock_id')))
            ->when($request->filled('gender'), fn ($q) => $q->where('gender', $request->string('gender')))
            ->when($request->filled('lifecycle_status'), fn ($q) => $q->where('lifecycle_status', $request->string('lifecycle_status')))
            ->when($request->filled('health_status'), fn ($q) => $q->where('health_status', $request->string('health_status')))
            ->orderBy('tag_number');

        return $this->streamCsv($filename, $headers, function ($handle) use ($query): void {
            $query->chunk(200, function ($animals) use ($handle): void {
                foreach ($animals as $animal) {
                    fputcsv($handle, [
                        $animal->farm?->name,
                        $animal->livestock?->name,
                        $animal->tag_number,
                        $animal->name,
                        $animal->gender,
                        $animal->health_status,
                        $animal->lifecycle_status,
                        optional($animal->date_of_birth)?->format('Y-m-d'),
                        $animal->weight_kg,
                        $animal->color_markings,
                        $animal->species,
                        $animal->breed,
                        $animal->acquisition_type,
                        optional($animal->acquisition_date)?->format('Y-m-d'),
                        $animal->source,
                        $animal->mother_tag,
                        $animal->father_tag,
                        $animal->production_status,
                        $animal->current_condition,
                        $animal->notes,
                    ]);
                }
            });
        });
    }

    public function template(): StreamedResponse
    {
        return $this->streamCsv('animals-import-template.csv', AnimalCsvSchema::headers(), function ($handle): void {
            fputcsv($handle, AnimalCsvSchema::exampleRow());
        });
    }
}
