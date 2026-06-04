<?php

use App\Support\AppUrl;

/*
|--------------------------------------------------------------------------
| Normalize APP_URL before Laravel boots (fixes Artisan "Host is malformed").
|--------------------------------------------------------------------------
*/

if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
    AppUrl::applyToEnvironment();
}
