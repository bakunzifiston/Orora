<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DownloadRwandaLocations extends Command
{
    protected $signature = 'rwanda:download-locations';

    protected $description = 'Download Rwanda administrative divisions JSON for farm location selects';

    public function handle(): int
    {
        $path = database_path('data/rwanda_locations.json');
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $this->info('Downloading Rwanda locations data…');

        $response = Http::timeout(120)->get(
            'https://raw.githubusercontent.com/jnkindi/rwanda-locations-json/master/locations.json'
        );

        if (! $response->successful()) {
            $this->error('Download failed.');

            return self::FAILURE;
        }

        file_put_contents($path, $response->body());

        $this->callSilent('cache:clear');
        $this->info('Saved to '.$path);

        return self::SUCCESS;
    }
}
