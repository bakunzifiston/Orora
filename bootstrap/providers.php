<?php

use App\Providers\AppServiceProvider;
use App\Providers\FinanceServiceProvider;
use App\Providers\MilkServiceProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    FinanceServiceProvider::class,
    MilkServiceProvider::class,
    TenancyServiceProvider::class,
];
