<?php

namespace App\Tenancy;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager;

/**
 * Creates tenant databases via cPanel UAPI (shared hosting blocks SQL CREATE DATABASE).
 *
 * @see https://api.docs.cpanel.net/openapi/cpanel/operation/create_database/
 */
class CpanelMysqlDatabaseManager extends MySQLDatabaseManager
{
    public function createDatabase(TenantWithDatabase $tenant): bool
    {
        $database = $tenant->database()->getName();

        if ($this->databaseExists($database)) {
            return true;
        }

        $response = $this->cpanelRequest('create_database', [
            'name' => $this->apiDatabaseName($database),
        ]);

        return $this->cpanelSucceeded($response);
    }

    public function deleteDatabase(TenantWithDatabase $tenant): bool
    {
        $database = $tenant->database()->getName();

        if (! $this->databaseExists($database)) {
            return true;
        }

        $response = $this->cpanelRequest('delete_database', [
            'name' => $this->apiDatabaseName($database),
        ]);

        return $this->cpanelSucceeded($response);
    }

    /**
     * cPanel API expects the suffix after "username_" (it adds the account prefix itself).
     */
    protected function apiDatabaseName(string $fullName): string
    {
        $account = (string) config('tenancy.cpanel.username', '');

        if ($account !== '' && str_starts_with($fullName, $account.'_')) {
            return substr($fullName, strlen($account) + 1);
        }

        return $fullName;
    }

    /**
     * @return array<string, mixed>
     */
    protected function cpanelRequest(string $function, array $params): array
    {
        $host = rtrim((string) config('tenancy.cpanel.host'), '/');
        $username = (string) config('tenancy.cpanel.username');
        $token = (string) config('tenancy.cpanel.api_token');

        if ($host === '' || $username === '' || $token === '') {
            throw new RuntimeException(
                'cPanel tenant databases require TENANCY_CPANEL_HOST, TENANCY_CPANEL_USERNAME, and TENANCY_CPANEL_API_TOKEN in .env.'
            );
        }

        $response = Http::withHeaders([
            'Authorization' => "cpanel {$username}:{$token}",
        ])
            ->timeout(60)
            ->get("{$host}/execute/Mysql/{$function}", $params);

        if (! $response->successful()) {
            throw new RuntimeException(
                "cPanel Mysql/{$function} failed (HTTP {$response->status()}): ".$response->body()
            );
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException("cPanel Mysql/{$function} returned invalid JSON.");
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function cpanelSucceeded(array $payload): bool
    {
        $status = $payload['result']['status'] ?? $payload['status'] ?? null;

        if ($status === 1 || $status === '1') {
            return true;
        }

        $errors = $payload['result']['errors'] ?? $payload['errors'] ?? ['Unknown cPanel error'];

        throw new RuntimeException(
            'cPanel database operation failed: '.json_encode($errors, JSON_UNESCAPED_UNICODE)
        );
    }
}
