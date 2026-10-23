<?php

namespace App\Http\Controllers;

use App\Support\TenantStorageUrl;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Generic read-only "View" page for module records listed in config/record_views.php.
 */
class RecordDetailsController extends Controller
{
    /** Attributes never shown on a detail page. */
    private const ALWAYS_HIDDEN = ['id', 'tenant_id', 'password', 'remember_token'];

    /** Show routes for related records that have their own page. */
    private const RELATED_SHOW_ROUTES = [
        \App\Models\Animal::class => 'animals.show',
        \App\Models\Farm::class => 'farms.show',
        \App\Models\Livestock::class => 'livestock.show',
        \App\Models\Flock::class => 'flocks.show',
        \App\Models\Customer::class => 'customers.show',
        \App\Models\Employee::class => 'employees.show',
        \App\Models\SaleTransaction::class => 'sales.transactions.show',
        \App\Models\Medicine::class => 'health.medicines.show',
    ];

    public function show(Request $request): View
    {
        $config = config('record_views.'.$request->route('recordType'));
        abort_unless($config, 404);

        /** @var Model $model */
        $model = $config['model']::query()->findOrFail((int) $request->route('record'));

        $hidden = array_merge(self::ALWAYS_HIDDEN, $config['hidden'] ?? []);
        $fields = collect($model->getFillable())
            ->reject(fn (string $attribute) => in_array($attribute, $hidden, true))
            ->map(fn (string $attribute) => $this->field($model, $attribute, $config['labels'][$attribute] ?? null))
            ->filter()
            ->values();

        $editRoute = $config['route'].'.edit';
        $listRoute = collect([$config['route'], $config['route'].'.index'])->first(fn ($name) => Route::has($name));

        return view('records.show', [
            'activeNav' => $config['nav'] ?? 'dashboard',
            'label' => $config['label'],
            'listLabel' => $config['list'] ?? Str::plural($config['label']),
            'title' => $this->displayName($model) ?? $config['label'].' #'.$model->getKey(),
            'fields' => $fields,
            'createdAt' => $model->created_at,
            'updatedAt' => $model->updated_at,
            'editUrl' => Route::has($editRoute) ? route($editRoute, $model) : null,
            'listUrl' => $listRoute ? route($listRoute) : null,
        ]);
    }

    /**
     * @return array{label: string, value: ?string, url: ?string, long: bool}|null
     */
    private function field(Model $model, string $attribute, ?string $label): ?array
    {
        $raw = $model->getAttribute($attribute);
        $url = null;

        if (str_ends_with($attribute, '_id')) {
            $related = $this->related($model, $attribute);
            $label ??= Str::headline(Str::beforeLast($attribute, '_id'));

            if ($related) {
                $value = $this->displayName($related) ?? '#'.$related->getKey();
                $route = self::RELATED_SHOW_ROUTES[$related::class] ?? null;
                $url = $route && Route::has($route) ? route($route, $related) : null;
            } else {
                $value = $raw === null ? null : '#'.$raw;
            }

            return ['label' => $label, 'value' => $value, 'url' => $url, 'long' => false];
        }

        $label ??= Str::headline($attribute);

        if (str_ends_with($attribute, '_path')) {
            return [
                'label' => Str::headline(Str::beforeLast($attribute, '_path')),
                'value' => $raw ? __('Open file') : null,
                'url' => $raw ? TenantStorageUrl::forPublicDisk($raw) : null,
                'long' => false,
            ];
        }

        $value = match (true) {
            $raw === null || $raw === '' => null,
            is_bool($raw) => $raw ? __('Yes') : __('No'),
            $raw instanceof CarbonInterface => $raw->format($raw->format('H:i:s') === '00:00:00' ? 'M j, Y' : 'M j, Y H:i'),
            is_array($raw) => collect($raw)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->implode(', ') ?: null,
            is_numeric($raw) && str_contains((string) $raw, '.') => rtrim(rtrim(number_format((float) $raw, 2), '0'), '.'),
            in_array($attribute, ['status', 'frequency', 'category', 'type', 'severity'], true) || str_ends_with($attribute, '_type') || str_ends_with($attribute, '_status')
                => Str::of((string) $raw)->replace(['_', '-'], ' ')->ucfirst()->toString(),
            default => (string) $raw,
        };

        return [
            'label' => $label,
            'value' => $value,
            'url' => null,
            'long' => is_string($value) && (mb_strlen($value) > 80 || in_array($attribute, ['notes', 'description', 'diagnosis', 'symptoms'], true)),
        ];
    }

    private function related(Model $model, string $attribute): ?Model
    {
        $base = Str::beforeLast($attribute, '_id');

        foreach ([Str::camel($base), Str::camel(Str::afterLast($base, '_'))] as $relation) {
            if (! method_exists($model, $relation)) {
                continue;
            }

            try {
                $relationObject = $model->{$relation}();
            } catch (\Throwable) {
                continue;
            }

            if ($relationObject instanceof BelongsTo && $relationObject->getForeignKeyName() === $attribute) {
                return $model->{$relation};
            }
        }

        return null;
    }

    private function displayName(Model $model): ?string
    {
        $config = collect(config('record_views', []))->firstWhere('model', $model::class);

        foreach ($config['title'] ?? [] as $path) {
            if (filled($value = data_get($model, $path))) {
                return filled($config['title_prefix'] ?? null) ? $config['title_prefix'].' · '.$value : (string) $value;
            }
        }

        foreach (['tag_number', 'title', 'name', 'display_name', 'container_name', 'session_code', 'sale_number', 'distribution_code', 'product_name', 'vaccine_name', 'treatment_name', 'disease_name', 'certificate_number'] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (filled($value)) {
                if ($attribute === 'tag_number' && filled($model->getAttribute('name'))) {
                    return $value.' · '.$model->getAttribute('name');
                }

                return (string) $value;
            }
        }

        return null;
    }
}
