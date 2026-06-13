<?php

namespace App\Services\Marketplace;

use App\Models\Animal;
use App\Models\Farm;
use App\Models\Offspring;
use App\Services\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AnimalTraceService
{
    public function findByTag(string $tagNumber): Collection
    {
        $tag = trim($tagNumber);

        if ($tag === '') {
            return new Collection;
        }

        return Animal::query()
            ->withoutGlobalScope('tenant')
            ->with(['farm:id,name,district,province,country,registration_number,status,tenant_id', 'livestock:id,name,livestock_types,livestock_type_other,breed'])
            ->whereRaw('LOWER(tag_number) = ?', [Str::lower($tag)])
            ->orderByDesc('updated_at')
            ->get();
    }

    public function loadProfile(Animal $animal): Animal
    {
        return TenantContext::run($animal->tenant_id, function () use ($animal) {
            $animal->load([
                'farm',
                'livestock',
                'vaccinations' => fn ($query) => $query->orderByDesc('vaccination_date')->limit(20),
                'treatments' => fn ($query) => $query->orderByDesc('start_date')->limit(20),
                'healthRecords' => fn ($query) => $query->orderByDesc('recorded_on')->limit(20),
                'movements' => fn ($query) => $query->with(['fromFarm:id,name', 'toFarm:id,name'])->orderByDesc('moved_on')->limit(20),
                'certificates' => fn ($query) => $query->orderByDesc('issued_on')->limit(20),
            ]);

            return $animal;
        });
    }

    public function resolve(int $animalId): ?Animal
    {
        $animal = Animal::query()
            ->withoutGlobalScope('tenant')
            ->find($animalId);

        if (! $animal) {
            return null;
        }

        return $this->loadProfile($animal);
    }

    public function animalCode(Animal $animal): string
    {
        $animal->loadMissing('farm');

        $farmSlug = Str::upper(Str::substr(preg_replace('/[^a-z0-9]/i', '', $animal->farm?->name ?? 'FARM'), 0, 3));
        $farmSlug = str_pad($farmSlug ?: 'FRM', 3, 'X');

        return sprintf('RW-%s-A-%03d', $farmSlug, $animal->id);
    }

    /**
     * @return array{
     *     mother_label: ?string,
     *     father_label: ?string,
     *     breeding_type: ?string,
     *     birth_type: ?string,
     *     has_history: bool
     * }
     */
    public function breedingHistory(Animal $animal): array
    {
        return TenantContext::run($animal->tenant_id, function () use ($animal) {
            $motherLabel = $this->resolveTagLabel($animal->mother_tag);
            $fatherLabel = $this->resolveTagLabel($animal->father_tag);
            $breedingType = null;
            $birthType = null;

            $offspring = Offspring::query()
                ->where('animal_id', $animal->id)
                ->with(['birthRecord.breedingRecord', 'motherAnimal:id,tag_number,name', 'fatherAnimal:id,tag_number,name'])
                ->first();

            if ($offspring) {
                if ($offspring->motherAnimal) {
                    $motherLabel = $this->formatTagName($offspring->motherAnimal->tag_number, $offspring->motherAnimal->name);
                }

                if ($offspring->fatherAnimal) {
                    $fatherLabel = $this->formatTagName($offspring->fatherAnimal->tag_number, $offspring->fatherAnimal->name);
                } elseif ($offspring->external_sire_name) {
                    $fatherLabel = $offspring->external_sire_name;
                }

                $breedingType = $offspring->birthRecord?->breedingRecord?->breedingTypeLabel();
                $birthType = $offspring->birthRecord?->birth_type;
            }

            $hasHistory = filled($motherLabel)
                || filled($fatherLabel)
                || filled($breedingType)
                || filled($birthType);

            return [
                'mother_label' => $motherLabel,
                'father_label' => $fatherLabel,
                'breeding_type' => $breedingType,
                'birth_type' => $birthType,
                'has_history' => $hasHistory,
            ];
        });
    }

    public function lastHealthCheck(Animal $animal): ?CarbonInterface
    {
        $dates = collect([
            $animal->healthRecords->first()?->recorded_on,
            $animal->vaccinations->first()?->vaccination_date,
            $animal->treatments->first()?->start_date,
        ])->filter();

        return $dates->sortDesc()->first();
    }

    public function farmLocationLabel(?Farm $farm): string
    {
        if (! $farm) {
            return 'Rwanda';
        }

        $parts = collect([$farm->district, $farm->province, $farm->country ?: 'Rwanda'])
            ->filter()
            ->unique()
            ->values();

        return $parts->implode(', ') ?: 'Rwanda';
    }

    public function livestockGroupLabel(Animal $animal): string
    {
        $type = $animal->species
            ?: $animal->livestock?->livestock_types_label
            ?: 'Livestock';
        $group = $animal->livestock?->name;

        return $group ? "{$type} — {$group}" : $type;
    }

    public function verificationToken(Animal $animal): string
    {
        return strtoupper(substr(hash('sha256', $animal->tenant_id.'|'.$animal->id.'|'.$animal->tag_number), 0, 16));
    }

    public function traceUrl(Animal $animal): string
    {
        return route('marketplace.trace.show', $animal);
    }

    public function qrCodeUrl(Animal $animal): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&data='.urlencode($this->traceUrl($animal));
    }

    public function logoDataUri(): ?string
    {
        $path = public_path('images/orora-logo.png');

        if (! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    }

    public function pdfFilename(Animal $animal): string
    {
        $slug = Str::slug($animal->tag_number) ?: 'animal';

        return "orora-trace-{$slug}.pdf";
    }

    /**
     * @return array<string, mixed>
     */
    public function reportContext(Animal $animal): array
    {
        $animal = $this->loadProfile($animal);

        return [
            'animal' => $animal,
            'animalCode' => $this->animalCode($animal),
            'breeding' => $this->breedingHistory($animal),
            'lastHealthCheck' => $this->lastHealthCheck($animal),
            'farmLocation' => $this->farmLocationLabel($animal->farm),
            'livestockGroup' => $this->livestockGroupLabel($animal),
            'verificationToken' => $this->verificationToken($animal),
            'traceUrl' => $this->traceUrl($animal),
            'qrCodeUrl' => $this->qrCodeUrl($animal),
            'logoDataUri' => $this->logoDataUri(),
            'generatedAt' => now(),
        ];
    }

    public function pdfResponse(Animal $animal): Response
    {
        $context = $this->reportContext($animal);

        $pdf = Pdf::loadView('marketplace.trace.pdf', $context)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        return $pdf->download($this->pdfFilename($animal));
    }

    protected function resolveTagLabel(?string $tag): ?string
    {
        if (! filled($tag)) {
            return null;
        }

        $match = Animal::query()->where('tag_number', $tag)->first(['tag_number', 'name']);

        return $this->formatTagName($tag, $match?->name);
    }

    protected function formatTagName(string $tag, ?string $name): string
    {
        return filled($name) ? "{$tag} · {$name}" : $tag;
    }
}
