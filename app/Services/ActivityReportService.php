<?php

namespace App\Services;

use App\Models\AbattoirDispatch;
use App\Models\Animal;
use App\Models\BirthRecord;
use App\Models\BreedingRecord;
use App\Models\DiseaseRecord;
use App\Models\EggCollection;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\Feeding;
use App\Models\MilkDistribution;
use App\Models\MilkSession;
use App\Models\Mortality;
use App\Models\Movement;
use App\Models\PregnancyCheck;
use App\Models\SalePayment;
use App\Models\SaleTransaction;
use App\Models\Treatment;
use App\Models\Vaccination;
use App\Models\VetVisit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Builds one chronological list of everything that happened in a period,
 * across modules, optionally limited to one farm and to some modules.
 */
class ActivityReportService
{
    /** Max rows read per activity type (keeps memory bounded). */
    public const SOURCE_LIMIT = 1500;

    public const MODULES = [
        'sales' => 'Sales & payments',
        'expenses' => 'Expenses',
        'milk' => 'Milk',
        'eggs' => 'Eggs',
        'health' => 'Health',
        'breeding' => 'Breeding',
        'feeding' => 'Feeding',
        'animals' => 'Animals & movements',
    ];

    /** @var array<string, string> */
    private array $farmNames = [];

    /** @var array<int, string> */
    private array $tags = [];

    /**
     * @param  array{from:string,to:string,farm_id?:int|string|null,modules?:list<string>}  $filters
     * @return array{rows:Collection<int,array<string,mixed>>,summary:Collection<int,array<string,mixed>>,truncated:bool,sales:Collection<int,SaleTransaction>}
     */
    public function build(array $filters): array
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->endOfDay();
        $farmId = filled($filters['farm_id'] ?? null) ? (int) $filters['farm_id'] : null;
        $modules = $filters['modules'] ?? array_keys(self::MODULES);

        $this->farmNames = Farm::query()->pluck('name', 'id')->all();
        $this->tags = [];

        $truncated = false;
        $rows = collect();

        foreach ($this->sources() as $source) {
            if (! in_array($source['module'], $modules, true)) {
                continue;
            }

            $query = ($source['query'])()
                ->whereDate($source['date'], '>=', $from->toDateString())
                ->whereDate($source['date'], '<=', $to->toDateString());

            if ($farmId) {
                ($source['farm'])($query, $farmId);
            }

            $models = $query->orderBy($source['date'])->limit(self::SOURCE_LIMIT)->get();
            $truncated = $truncated || $models->count() >= self::SOURCE_LIMIT;

            $this->preloadTags($models);

            foreach ($models as $m) {
                $rows->push([
                    'at' => Carbon::parse($m->{$source['date']}),
                    'module' => $source['module'],
                    'type' => $source['label'],
                    'farm' => $this->farmNames[$m->farm_id ?? null] ?? '',
                    'description' => ($source['describe'])($m, $this),
                    'amount' => isset($source['amount']) ? ($source['amount'])($m) : null,
                    'unit' => $source['unit'] ?? null,
                    'currency' => $m->currency ?? null,
                ]);
            }
        }

        $rows = $rows->sortBy(fn ($r) => $r['at']->timestamp)->values();

        $summary = $rows->groupBy('type')->map(function (Collection $group) {
            $first = $group->first();
            $amounts = $group->filter(fn ($r) => $r['amount'] !== null);

            return [
                'module' => self::MODULES[$first['module']],
                'type' => $first['type'],
                'count' => $group->count(),
                'total' => $amounts->isEmpty() ? null : (float) $amounts->sum('amount'),
                'unit' => $first['unit'] === 'currency' ? ($first['currency'] ?: 'RWF') : $first['unit'],
            ];
        })->sortBy('module')->values();

        return [
            'rows' => $rows,
            'summary' => $summary,
            'truncated' => $truncated,
            'sales' => in_array('sales', $modules, true) ? $this->salesDetails($from, $to, $farmId) : collect(),
        ];
    }

    /**
     * Full sale records (customer, line items, paid, balance) for the period.
     *
     * @return Collection<int, SaleTransaction>
     */
    public function salesDetails(Carbon $from, Carbon $to, ?int $farmId): Collection
    {
        return SaleTransaction::query()
            ->with(['customer', 'farm:id,name', 'items'])
            ->withSum('payments as paid_total', 'amount_paid')
            ->where('sale_status', '!=', 'cancelled')
            ->whereDate('sale_date', '>=', $from->toDateString())
            ->whereDate('sale_date', '<=', $to->toDateString())
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->orderBy('sale_date')
            ->orderBy('id')
            ->limit(self::SOURCE_LIMIT)
            ->get();
    }

    public function tag(?int $animalId): string
    {
        return $animalId && isset($this->tags[$animalId]) ? $this->tags[$animalId] : '';
    }

    private function preloadTags(Collection $models): void
    {
        $ids = $models->flatMap(fn ($m) => [
            $m->animal_id ?? null, $m->female_animal_id ?? null, $m->mother_animal_id ?? null,
        ])->filter()->unique()->diff(array_keys($this->tags));

        if ($ids->isNotEmpty()) {
            $this->tags += Animal::query()->whereIn('id', $ids)->pluck('tag_number', 'id')->all();
        }
    }

    /** @return list<array<string,mixed>> */
    private function sources(): array
    {
        $farmCol = fn (Builder $q, int $id) => $q->where('farm_id', $id);
        $join = fn (string $s, ?string $x) => trim($s.' '.($x ? '· '.$x : ''));
        $tag = fn ($m, $svc) => ($t = $svc->tag($m->animal_id ?? null)) ? " [{$t}]" : '';

        return [
            [
                'module' => 'sales', 'label' => 'Sale', 'date' => 'sale_date', 'unit' => 'currency',
                'query' => fn () => SaleTransaction::query()->with('customer')->where('sale_status', '!=', 'cancelled'),
                'farm' => $farmCol,
                'describe' => fn ($m) => $join("{$m->sale_number} — ".$m->typeLabel().($m->customer ? " to {$m->customer->display_name}" : ''), $m->statusLabel().' / '.ucfirst((string) $m->payment_status)),
                'amount' => fn ($m) => (float) $m->total_amount,
            ],
            [
                'module' => 'sales', 'label' => 'Payment received', 'date' => 'payment_date', 'unit' => 'currency',
                'query' => fn () => SalePayment::query(),
                'farm' => fn (Builder $q, int $id) => $q->whereIn('sale_transaction_id', SaleTransaction::query()->where('farm_id', $id)->select('id')),
                'describe' => fn ($m) => $join((string) $m->payment_reference, $m->payment_method ? ucfirst(str_replace('_', ' ', $m->payment_method)) : null),
                'amount' => fn ($m) => (float) $m->amount_paid,
            ],
            [
                'module' => 'sales', 'label' => 'Abattoir dispatch', 'date' => 'dispatch_date',
                'query' => fn () => AbattoirDispatch::query(),
                'farm' => $farmCol,
                'describe' => fn ($m) => $join("{$m->dispatch_code} to {$m->abattoir_name}", "{$m->total_animals_dispatched} animals, ".$m->dispatch_status),
            ],
            [
                'module' => 'expenses', 'label' => 'Expense', 'date' => 'expense_date', 'unit' => 'currency',
                'query' => fn () => Expense::query()->where('status', '!=', 'void'),
                'farm' => $farmCol,
                'describe' => fn ($m) => $join((string) $m->title, ucfirst((string) $m->status)),
                'amount' => fn ($m) => (float) $m->amount,
            ],
            [
                'module' => 'milk', 'label' => 'Milking session', 'date' => 'session_date', 'unit' => 'L',
                'query' => fn () => MilkSession::query(),
                'farm' => $farmCol,
                'describe' => fn ($m) => $join("{$m->session_code} ({$m->session_shift})", "{$m->number_of_animals_milked} animals, ".$m->status),
                'amount' => fn ($m) => (float) $m->total_yield_liters,
            ],
            [
                'module' => 'milk', 'label' => 'Milk distribution', 'date' => 'distributed_at', 'unit' => 'L',
                'query' => fn () => MilkDistribution::query(),
                'farm' => $farmCol,
                'describe' => fn ($m) => $join(ucfirst((string) $m->category), $m->recipient_name),
                'amount' => fn ($m) => (float) $m->quantity_liters,
            ],
            [
                'module' => 'eggs', 'label' => 'Egg collection', 'date' => 'collected_on', 'unit' => 'eggs',
                'query' => fn () => EggCollection::query(),
                'farm' => $farmCol,
                'describe' => fn ($m) => $join((string) $m->collection_code, "{$m->cracked_count} cracked"),
                'amount' => fn ($m) => (float) $m->eggs_count,
            ],
            [
                'module' => 'health', 'label' => 'Treatment', 'date' => 'start_date',
                'query' => fn () => Treatment::query(),
                'farm' => $farmCol,
                'describe' => fn ($m, $svc) => $join(trim(($m->disease_name ?: 'Treatment').' — '.$m->medicine_name, ' —').$tag($m, $svc), $m->status),
            ],
            [
                'module' => 'health', 'label' => 'Vaccination', 'date' => 'vaccination_date',
                'query' => fn () => Vaccination::query(),
                'farm' => $farmCol,
                'describe' => fn ($m, $svc) => $join($m->vaccine_name.$tag($m, $svc), $m->status),
            ],
            [
                'module' => 'health', 'label' => 'Disease record', 'date' => 'diagnosis_date',
                'query' => fn () => DiseaseRecord::query(),
                'farm' => $farmCol,
                'describe' => fn ($m, $svc) => $join($m->disease_name.$tag($m, $svc), trim("{$m->severity_level} {$m->recovery_status}")),
            ],
            [
                'module' => 'health', 'label' => 'Vet visit', 'date' => 'start_date',
                'query' => fn () => VetVisit::query(),
                'farm' => $farmCol,
                'describe' => fn ($m, $svc) => $join(($m->disease_name ?: 'Visit').$tag($m, $svc), $m->veterinarian_name),
            ],
            [
                'module' => 'health', 'label' => 'Death recorded', 'date' => 'death_date', 'unit' => 'head',
                'query' => fn () => Mortality::query(),
                'farm' => $farmCol,
                'describe' => fn ($m, $svc) => $join(($m->deaths_count ?: 1).' death(s)'.$tag($m, $svc), $m->cause_of_death),
                'amount' => fn ($m) => (float) ($m->deaths_count ?: 1),
            ],
            [
                'module' => 'breeding', 'label' => 'Breeding', 'date' => 'breeding_date',
                'query' => fn () => BreedingRecord::query(),
                'farm' => $farmCol,
                'describe' => fn ($m, $svc) => $join($m->breeding_code.($svc->tag($m->female_animal_id) ? ' ['.$svc->tag($m->female_animal_id).']' : ''), $m->breeding_status),
            ],
            [
                'module' => 'breeding', 'label' => 'Pregnancy check', 'date' => 'check_date',
                'query' => fn () => PregnancyCheck::query(),
                'farm' => fn (Builder $q, int $id) => $q->whereIn('breeding_record_id', BreedingRecord::query()->where('farm_id', $id)->select('id')),
                'describe' => fn ($m, $svc) => $join($m->check_code.$tag($m, $svc), $m->result),
            ],
            [
                'module' => 'breeding', 'label' => 'Birth', 'date' => 'birth_date', 'unit' => 'born',
                'query' => fn () => BirthRecord::query(),
                'farm' => fn (Builder $q, int $id) => $q->whereIn('breeding_record_id', BreedingRecord::query()->where('farm_id', $id)->select('id')),
                'describe' => fn ($m) => $join((string) $m->birth_code, "{$m->alive_offspring}/{$m->total_offspring} alive"),
                'amount' => fn ($m) => (float) $m->total_offspring,
            ],
            [
                'module' => 'feeding', 'label' => 'Feeding', 'date' => 'fed_on',
                'query' => fn () => Feeding::query(),
                'farm' => $farmCol,
                'describe' => fn ($m, $svc) => rtrim(rtrim((string) $m->quantity, '0'), '.')." {$m->unit}".$tag($m, $svc),
            ],
            [
                'module' => 'animals', 'label' => 'Animal registered', 'date' => 'created_at',
                'query' => fn () => Animal::query(),
                'farm' => $farmCol,
                'describe' => fn ($m) => $join($m->tag_number.($m->name ? " ({$m->name})" : ''), trim("{$m->species} {$m->breed}")),
            ],
            [
                'module' => 'animals', 'label' => 'Movement', 'date' => 'moved_on',
                'query' => fn () => Movement::query(),
                'farm' => fn (Builder $q, int $id) => $q->where(fn ($w) => $w->where('from_farm_id', $id)->orWhere('to_farm_id', $id)),
                'describe' => fn ($m, $svc) => $join(ucfirst(str_replace('_', ' ', (string) $m->movement_type)).$tag($m, $svc), $m->reference),
            ],
        ];
    }
}
