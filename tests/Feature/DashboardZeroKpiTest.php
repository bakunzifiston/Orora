<?php

namespace Tests\Feature;

use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class DashboardZeroKpiTest extends TenantTestCase
{
    public function test_zero_value_kpis_are_hidden_and_non_zero_ones_shown(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $herd = FarmTestFixtures::livestock($farm);
        FarmTestFixtures::animal($farm, $herd);

        $this->get(route('dashboard'))->assertOk()
            ->assertSee('Active farms')
            ->assertSee('Livestock groups')
            ->assertSee('Active animals')
            ->assertDontSee('Calves group')
            ->assertDontSee('Pregnant cows')
            ->assertDontSee('Accounts receivable');
    }

    public function test_diseased_animals_kpi_counts_open_cases_only(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $herd = FarmTestFixtures::livestock($farm);
        $sick = FarmTestFixtures::animal($farm, $herd);
        $healed = FarmTestFixtures::animal($farm, $herd);

        $this->get(route('dashboard'))->assertOk()->assertDontSee('Sick animals');

        foreach ([[$sick, 'chronic'], [$sick, 'recovering'], [$healed, 'recovered']] as [$animal, $status]) {
            \App\Models\DiseaseRecord::create([
                'disease_code' => 'DS-'.uniqid(),
                'farm_id' => $farm->id,
                'animal_id' => $animal->id,
                'disease_name' => 'Mastitis',
                'diagnosis_date' => now()->toDateString(),
                'recovery_status' => $status,
            ]);
        }

        $html = $this->get(route('dashboard'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/Sick animals<\/div>\s*<div class="farm-kpi__value">1<\/div>/', $html);
    }

    public function test_milk_produced_today_kpi(): void
    {
        $this->actingAsTenantUser();
        $farm = FarmTestFixtures::farm();
        $herd = FarmTestFixtures::livestock($farm);

        $this->get(route('dashboard'))->assertOk()->assertDontSee('Milk produced today');

        foreach ([['morning', now(), 30.5], ['evening', now(), 20], ['morning', now()->subDay(), 99]] as $i => [$shift, $date, $litres]) {
            \App\Models\MilkSession::create([
                'farm_id' => $farm->id,
                'livestock_id' => $herd->id,
                'session_code' => 'MS-T-'.$i.'-'.uniqid(),
                'session_date' => $date->toDateString(),
                'session_shift' => $shift,
                'total_yield_liters' => $litres,
                'milked_by' => 'Tester',
                'milking_method' => 'manual',
                'status' => 'completed',
            ]);
        }

        $html = $this->get(route('dashboard'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/Milk produced today<\/div>\s*<div class="farm-kpi__value">51 L<\/div>/', $html);
    }
}
