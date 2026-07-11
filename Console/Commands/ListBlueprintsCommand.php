<?php

namespace Modules\TitanZero\Console\Commands;

use Illuminate\Console\Command;

/**
 * ListBlueprintsCommand — lists all loaded blueprints in the TitanZero module.
 *
 * Usage:
 *   php artisan titan-zero:list-blueprints
 *
 * Scans the Blueprints/ directory for any loadable blueprint definitions
 * and displays their name, version, and status.
 */
class ListBlueprintsCommand extends Command
{
    protected $signature   = 'titan-zero:list-blueprints {--json : Output as JSON}';
    protected $description = 'List all loaded TitanZero blueprints';

    public function handle(): int
    {
        $blueprints = $this->discoverBlueprints();

        if ($this->option('json')) {
            $this->line(json_encode($blueprints, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        if (empty($blueprints)) {
            $this->warn('No blueprints found in TitanZero/Blueprints/.');
            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Class', 'File', 'Loadable'],
            array_map(fn($b) => [
                $b['name'],
                $b['class'] ?? '—',
                $b['file'],
                $b['loadable'] ? '<fg=green>✓</>' : '<fg=red>✗</>',
            ], $blueprints),
        );

        $loadable = count(array_filter($blueprints, fn($b) => $b['loadable']));
        $this->info("Total: {$loadable}/" . count($blueprints) . " blueprints loadable.");

        return self::SUCCESS;
    }

    private function discoverBlueprints(): array
    {
        $base   = module_path('TitanZero', 'Blueprints');
        $results = [];

        if (!is_dir($base)) {
            return $results;
        }

        // Recursively find PHP files inside the Blueprints/ directory.
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS),
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace($base . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $class    = $this->fileToClass($file->getPathname());
            $loadable = false;

            if ($class !== null) {
                try {
                    require_once $file->getPathname();
                    $loadable = class_exists($class, false);
                } catch (\Throwable) {
                    $loadable = false;
                }
            }

            $results[] = [
                'name'     => $file->getBasename('.php'),
                'class'    => $class,
                'file'     => $relative,
                'loadable' => $loadable,
            ];
        }

        return $results;
    }

    /**
     * Attempt to derive a fully-qualified class name from the file path.
     * Reads the first 40 lines looking for namespace + class declarations.
     */
    private function fileToClass(string $path): ?string
    {
        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $ns    = null;
        $class = null;

        foreach (array_slice($lines, 0, 40) as $line) {
            if ($ns === null && preg_match('/^namespace\s+([\w\\\\]+)\s*;/', $line, $m)) {
                $ns = $m[1];
            }
            if ($class === null && preg_match('/^(?:abstract\s+|final\s+)?class\s+(\w+)/', $line, $m)) {
                $class = $m[1];
            }
            if ($ns && $class) {
                return $ns . '\\' . $class;
            }
        }

        return null;
    }
}
