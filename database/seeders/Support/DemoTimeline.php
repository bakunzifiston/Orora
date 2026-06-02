<?php

namespace Database\Seeders\Support;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

final class DemoTimeline
{
    public readonly Carbon $start;

    public readonly Carbon $end;

    public function __construct(?Carbon $start = null, ?Carbon $end = null)
    {
        $this->start = ($start ?? Carbon::parse(config('demo.date_start', '2020-01-01')))->startOfDay();
        $this->end = ($end ?? Carbon::parse(config('demo.date_end', '2026-06-01')))->endOfDay();
    }

    public static function make(): self
    {
        return new self;
    }

    public function randomDate(?int $seed = null): Carbon
    {
        $days = max(1, $this->start->diffInDays($this->end));
        $offset = $seed !== null
            ? $seed % ($days + 1)
            : random_int(0, $days);

        return $this->start->copy()->addDays($offset);
    }

    public function randomDateBetween(Carbon $from, Carbon $to, ?int $seed = null): Carbon
    {
        $from = $from->copy()->startOfDay()->max($this->start);
        $to = $to->copy()->endOfDay()->min($this->end);

        if ($from->gt($to)) {
            return $to->copy();
        }

        $days = max(1, $from->diffInDays($to));
        $offset = $seed !== null ? $seed % ($days + 1) : random_int(0, $days);

        return $from->copy()->addDays($offset);
    }

    public function dateAtProgress(float $progress): Carbon
    {
        $progress = max(0, min(1, $progress));
        $days = (int) round($this->start->diffInDays($this->end) * $progress);

        return $this->start->copy()->addDays($days);
    }

    /** @return list<Carbon> */
    public function spreadDates(int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $dates = [];
        $span = max(1, $this->start->diffInDays($this->end));

        for ($i = 0; $i < $count; $i++) {
            $dates[] = $this->start->copy()->addDays((int) round($span * ($i / max(1, $count - 1))));
        }

        return $dates;
    }

    /** @return \Generator<int, Carbon> */
    public function monthly(Carbon $from, Carbon $to): \Generator
    {
        foreach (CarbonPeriod::create($from->copy()->startOfMonth(), '1 month', $to->copy()->endOfMonth()) as $month) {
            yield $month;
        }
    }
}
