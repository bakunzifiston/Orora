<?php

namespace App\Console\Commands;

use App\Services\RwandaLocationService;
use Illuminate\Console\Command;

class DownloadRwandaLocations extends Command
{
    protected $signature = 'rwanda:download-locations';

    protected $description = 'Download Rwanda administrative divisions JSON for farm location selects';

    public function handle(RwandaLocationService $locations): int
    {
        if (is_file($locations->dataPath())) {
            $this->components->info('Already present: '.$locations->dataPath());

            return self::SUCCESS;
        }

        $this->info('Downloading Rwanda locations data (~6 MB)…');

        if (! $locations->downloadDataFile()) {
            $this->error('Download failed. Check server internet access to GitHub.');
            $this->line('Or upload manually to: '.$locations->dataPath());

            return self::FAILURE;
        }

        $this->callSilent('cache:clear');
        $this->info('Saved to '.$locations->dataPath());

        return self::SUCCESS;
    }
}
