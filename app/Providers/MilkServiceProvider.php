<?php

namespace App\Providers;

use App\Models\MilkRecord;
use App\Observers\MilkRecordObserver;
use Illuminate\Support\ServiceProvider;

class MilkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        MilkRecord::observe(MilkRecordObserver::class);
    }
}
