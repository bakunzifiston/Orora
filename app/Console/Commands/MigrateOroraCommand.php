<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateOroraCommand extends Command
{
    protected $signature = 'orora:migrate';

    protected $description = 'Run all migrations (central + application tables in one database)';

    public function handle(): int
    {
        return $this->call('migrate', ['--force' => true]);
    }
}
