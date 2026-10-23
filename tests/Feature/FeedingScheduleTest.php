<?php

namespace Tests\Feature;

use App\Models\FeedingSchedule;
use App\Models\FeedType;
use Illuminate\Support\Str;
use Tests\Support\FarmTestFixtures;
use Tests\TenantTestCase;

class FeedingScheduleTest extends TenantTestCase
{
    public function test_recording_feeding_moves_daily_schedule_to_next_day(): void
    {
        $this->actingAsTenantUser();
        [$schedule, $herd] = $this->schedule('daily', now()->subDays(3));

        $this->feedFrom($schedule, $herd, now());

        $this->assertSame(now()->addDay()->toDateString(), $schedule->fresh()->next_due_date->toDateString());
        $this->assertEquals(80, (float) $schedule->feedInventory->fresh()->quantity_on_hand);
    }

    public function test_weekly_schedule_moves_a_week_after_the_feeding(): void
    {
        $this->actingAsTenantUser();
        [$schedule, $herd] = $this->schedule('weekly', now()->startOfDay());

        $this->feedFrom($schedule, $herd, now());

        $this->assertSame(now()->addWeek()->toDateString(), $schedule->fresh()->next_due_date->toDateString());
    }

    public function test_schedule_completes_after_its_end_date(): void
    {
        $this->actingAsTenantUser();
        [$schedule, $herd] = $this->schedule('daily', now()->subDay(), now()->toDateString());

        $this->feedFrom($schedule, $herd, now());

        $this->assertSame('completed', $schedule->fresh()->status);
    }

    public function test_list_shows_overdue_and_record_link_and_dashboard_alert(): void
    {
        $this->actingAsTenantUser();
        [$schedule] = $this->schedule('daily', now()->subDays(4));

        $this->get(route('feeding.schedules'))->assertOk()
            ->assertSee('Overdue by 4 days')
            ->assertSee(route('feeding.records.create', ['schedule' => $schedule->id]));

        $this->get(route('feeding.records.create', ['schedule' => $schedule->id]))->assertOk()
            ->assertSee('data-inventory-id="'.$schedule->feed_inventory_id.'"', false);

        $this->get(route('dashboard'))->assertOk()->assertSee('Scheduled feedings overdue');
    }

    /**
     * @return array{0: FeedingSchedule, 1: \App\Models\Livestock}
     */
    private function schedule(string $frequency, $start, ?string $end = null): array
    {
        $farm = FarmTestFixtures::farm();
        $herd = FarmTestFixtures::livestock($farm);
        $type = FeedType::create(['name' => 'Silage '.Str::random(5), 'unit' => 'kg', 'is_active' => true]);

        $this->post(route('feeding.schedules.store'), [
            'farm_id' => $farm->id,
            'feed_type_id' => $type->id,
            'feed_inventory_id' => '',
            'feed_target' => 'group',
            'livestock_id' => $herd->id,
            'quantity' => '20',
            'unit' => 'kg',
            'frequency' => $frequency,
            'scheduled_time' => '07:00',
            'start_date' => $start->toDateString(),
            'end_date' => $end ?? '',
            'next_due_date' => '',
            'status' => 'active',
        ])->assertSessionHasNoErrors();

        $schedule = FeedingSchedule::query()->where('feed_type_id', $type->id)->firstOrFail();
        $schedule->feedInventory->update(['quantity_on_hand' => 100]);

        return [$schedule, $herd];
    }

    private function feedFrom(FeedingSchedule $schedule, $herd, $fedOn): void
    {
        $this->post(route('feeding.records.store'), [
            'farm_id' => $schedule->farm_id,
            'feed_inventory_id' => $schedule->feed_inventory_id,
            'feeding_schedule_id' => $schedule->id,
            'feed_target' => 'group',
            'livestock_id' => $herd->id,
            'quantity' => '20',
            'fed_on' => $fedOn->toDateString(),
        ])->assertSessionHasNoErrors();
    }
}
