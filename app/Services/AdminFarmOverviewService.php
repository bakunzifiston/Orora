<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only figures for the platform admin farm page: workspace users, money, health and recent activity.
 * Queries are written against farm_id (and tenant_id) directly so tenant scopes are never involved.
 */
class AdminFarmOverviewService
{
    /**
     * @return array{users: Collection, money: array<string, mixed>, health: array<string, int>, activity: Collection, last_activity: ?Carbon}
     */
    public function overview(Farm $farm, Carbon $from, Carbon $to): array
    {
        $activity = $this->recentActivity($farm);

        return [
            'users' => $this->workspaceUsers($farm),
            'money' => $this->money($farm, $from, $to),
            'health' => $this->health($farm, $from, $to),
            'activity' => $activity->take(10)->values(),
            'last_activity' => $activity->first()['at'] ?? null,
        ];
    }

    /** @return Collection<int, User> */
    private function workspaceUsers(Farm $farm): Collection
    {
        if (! Schema::hasTable('users') || ! $farm->tenant_id) {
            return collect();
        }

        $columns = ['id', 'name', 'email', 'role', 'is_active', 'created_at'];
        if (Schema::hasColumn('users', 'last_login_at')) {
            $columns[] = 'last_login_at';
        }

        return User::query()->withoutGlobalScopes()
            ->where('tenant_id', $farm->tenant_id)
            ->orderBy('id')
            ->get($columns);
    }

    /** @return array<string, mixed> */
    private function money(Farm $farm, Carbon $from, Carbon $to): array
    {
        $out = ['revenue' => 0.0, 'expenses' => 0.0, 'receivable' => 0.0, 'unpaid_sales' => 0, 'overdue_sales' => 0, 'currency' => 'RWF'];

        if (Schema::hasTable('sale_transactions')) {
            $sales = fn () => DB::table('sale_transactions')->where('farm_id', $farm->id)->where('sale_status', 'completed');

            $out['revenue'] = (float) $sales()->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()])->sum('total_amount');

            $unpaid = $sales()->where('payment_status', '!=', 'paid')->get(['id', 'total_amount', 'sale_date']);
            $paid = Schema::hasTable('sale_payments')
                ? DB::table('sale_payments')->whereIn('sale_transaction_id', $unpaid->pluck('id'))->selectRaw('sale_transaction_id, SUM(amount_paid) as paid')->groupBy('sale_transaction_id')->pluck('paid', 'sale_transaction_id')
                : collect();

            foreach ($unpaid as $sale) {
                $balance = max(0, (float) $sale->total_amount - (float) ($paid[$sale->id] ?? 0));
                if ($balance <= 0) {
                    continue;
                }
                $out['receivable'] += $balance;
                $out['unpaid_sales']++;
                if (Carbon::parse($sale->sale_date)->lt(now()->subDays(30))) {
                    $out['overdue_sales']++;
                }
            }
        }

        if (Schema::hasTable('expenses')) {
            $out['expenses'] = (float) DB::table('expenses')->where('farm_id', $farm->id)->where('status', 'paid')
                ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])->sum('amount');
        }

        return $out;
    }

    /** @return array<string, int> */
    private function health(Farm $farm, Carbon $from, Carbon $to): array
    {
        $count = fn (string $table, callable $q) => Schema::hasTable($table) ? (int) $q(DB::table($table)->where('farm_id', $farm->id))->count() : 0;

        $unwell = ['sick' => 0, 'under_treatment' => 0, 'quarantined' => 0];
        if (Schema::hasTable('animals')) {
            $byStatus = DB::table('animals')->where('farm_id', $farm->id)->where('lifecycle_status', 'Active')
                ->whereIn('health_status', ['Sick', 'Under treatment', 'Quarantined'])
                ->selectRaw('health_status, COUNT(*) as c')->groupBy('health_status')->pluck('c', 'health_status');
            $unwell = ['sick' => (int) ($byStatus['Sick'] ?? 0), 'under_treatment' => (int) ($byStatus['Under treatment'] ?? 0), 'quarantined' => (int) ($byStatus['Quarantined'] ?? 0)];
        }

        return [
            'sick_animals' => array_sum($unwell),
            'sick' => $unwell['sick'],
            'under_treatment' => $unwell['under_treatment'],
            'quarantined' => $unwell['quarantined'],
            'vaccinations_due' => $count('vaccinations', fn ($q) => $q->whereNotNull('next_due_date')->where('next_due_date', '<=', now()->addDays(30)->toDateString())),
            'vaccinations' => $count('vaccinations', fn ($q) => $q->whereBetween('vaccination_date', [$from->toDateString(), $to->toDateString()])),
            'treatments' => $count('treatments', fn ($q) => $q->whereBetween('start_date', [$from->toDateString(), $to->toDateString()])),
            'open_disease_cases' => $count('disease_records', fn ($q) => $q->whereRaw("LOWER(COALESCE(recovery_status, '')) <> 'recovered'")),
            'disease_cases' => $count('disease_records', fn ($q) => $q->whereBetween('diagnosis_date', [$from->toDateString(), $to->toDateString()])),
            'deaths' => Schema::hasTable('mortalities')
                ? (int) DB::table('mortalities')->where('farm_id', $farm->id)->whereBetween('death_date', [$from->toDateString(), $to->toDateString()])->sum('deaths_count')
                : 0,
        ];
    }

    /** @return Collection<int, array{at: Carbon, type: string, tone: string, text: string}> */
    private function recentActivity(Farm $farm): Collection
    {
        // table => [type label, tone, text builder columns, text callback]
        $sources = [
            ['sale_transactions', 'Sale', 'ok', ['sale_number', 'total_amount', 'sale_status'], fn ($r) => trim(($r->sale_number ?? '').' · '.number_format((float) $r->total_amount).' RWF · '.($r->sale_status ?? ''))],
            ['milk_sessions', 'Milk session', 'ok', ['session_code', 'total_yield_liters', 'status'], fn ($r) => trim(($r->session_code ?? '').' · '.rtrim(rtrim(number_format((float) $r->total_yield_liters, 1), '0'), '.').' L · '.($r->status ?? ''))],
            ['egg_collections', 'Egg collection', 'ok', ['eggs_count', 'collected_on'], fn ($r) => number_format((int) $r->eggs_count).' eggs on '.$r->collected_on],
            ['expenses', 'Expense', 'warn', ['title', 'amount', 'status'], fn ($r) => trim(($r->title ?: 'Expense').' · '.number_format((float) $r->amount).' RWF · '.($r->status ?? ''))],
            ['vaccinations', 'Vaccination', 'default', ['vaccine_name', 'vaccination_date'], fn ($r) => ($r->vaccine_name ?: 'Vaccination').' on '.$r->vaccination_date],
            ['treatments', 'Treatment', 'default', ['disease_name', 'medicine_name'], fn ($r) => trim(($r->disease_name ?: 'Treatment').' · '.($r->medicine_name ?? ''), ' ·')],
            ['disease_records', 'Disease record', 'bad', ['disease_name', 'severity_level'], fn ($r) => trim(($r->disease_name ?: 'Disease').' · '.($r->severity_level ?? ''), ' ·')],
            ['mortalities', 'Mortality', 'bad', ['deaths_count', 'cause_of_death'], fn ($r) => trim(number_format((int) $r->deaths_count).' death(s) · '.($r->cause_of_death ?? ''), ' ·')],
            ['feedings', 'Feeding', 'default', ['quantity', 'unit', 'fed_on'], fn ($r) => rtrim(rtrim(number_format((float) $r->quantity, 2), '0'), '.').' '.$r->unit.' on '.$r->fed_on],
            ['breeding_records', 'Breeding', 'default', ['breeding_code', 'breeding_status'], fn ($r) => trim(($r->breeding_code ?? '').' · '.($r->breeding_status ?? ''), ' ·')],
            ['animals', 'Animal registered', 'default', ['tag_number', 'name'], fn ($r) => trim(($r->tag_number ?? '').' '.($r->name ?? ''))],
        ];

        $items = collect();
        foreach ($sources as [$table, $type, $tone, $columns, $text]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'farm_id')) {
                continue;
            }
            foreach (DB::table($table)->where('farm_id', $farm->id)->orderByDesc('created_at')->limit(8)->get(array_merge($columns, ['created_at'])) as $row) {
                if (! $row->created_at) {
                    continue;
                }
                $items->push(['at' => Carbon::parse($row->created_at), 'type' => $type, 'tone' => $tone, 'text' => $text($row)]);
            }
        }

        return $items->sortByDesc('at')->values();
    }
}
