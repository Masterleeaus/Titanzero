<?php

namespace Modules\TitanZero\Console\Commands;

use Illuminate\Console\Command;
use Modules\TitanZero\Services\Docs\ImportService;

class TitanZeroImportPdf extends Command
{
    protected $signature = 'titanzero:import-pdf {path} {--title=}';
    protected $description = 'Import a standards PDF into Titan Zero library (Pass 4)';

    public function handle(ImportService $importService): int
    {
        $path = $this->argument('path');
        $title = $this->option('title') ?: basename($path);

        if (!is_file($path)) {
            $this->error('File not found: '.$path);
            return 1;
        }

        $import = $importService->importPdf($path, $title, ['source' => 'cli']);
        $this->info('Import #'.$import->id.' status='.$import->status.' message='.$import->message);
        return $import->status === 'done' ? 0 : 2;
    }
}
