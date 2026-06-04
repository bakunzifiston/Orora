<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateOroraCommand extends Command
{
    protected $signature = 'orora:migrate';

    protected $description = 'Run central migrations, then migrate all tenant databases';

    public function handle(): int
    {
        $this->components->info('Central database…');
        $this->call('migrate', ['--force' => true]);

        $this->components->info('Tenant databases…');

        return $this->call('tenants:migrate', ['--force' => true]);
    }
}
