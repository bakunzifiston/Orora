<?php

namespace App\Support;

class HelpCenter
{
    /**
     * Documents visible to the given audience level.
     *
     * @param  'member'|'workspace_admin'|'platform'  $level
     * @return array<string, array<string, mixed>>
     */
    public static function documentsFor(string $level): array
    {
        $rank = ['member' => 1, 'workspace_admin' => 2, 'platform' => 3];
        $manifest = self::manifest();

        $docs = [];
        foreach (config('help_center.documents', []) as $key => $doc) {
            if ($rank[$doc['audience']] > $rank[$level]) {
                continue;
            }
            $path = self::path($doc['file']);
            $meta = $manifest[$doc['file']] ?? [];
            $docs[$key] = $doc + [
                'available' => is_file($path),
                'version' => $meta['version'] ?? null,
                'updated' => $meta['updated'] ?? null,
                'pages' => $meta['pages'] ?? null,
                'size' => is_file($path) ? filesize($path) : null,
            ];
        }

        return $docs;
    }

    public static function path(string $file): string
    {
        return rtrim(config('help_center.path'), '/').'/'.basename($file);
    }

    /** @return array<string, array<string, mixed>> */
    private static function manifest(): array
    {
        $file = self::path('manifest.json');

        return is_file($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
    }
}
