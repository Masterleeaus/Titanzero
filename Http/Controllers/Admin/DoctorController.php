<?php

namespace Modules\TitanZero\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    public function index()
    {
        $checks = [];

        // DB table checks
        $tables = [
            'titanzero_documents',
            'titanzero_document_chunks',
            'titanzero_imports',
            'titanzero_audit_logs',
        ];

        foreach ($tables as $t) {
            try {
                DB::table($t)->limit(1)->get();
                $checks[] = ['name' => "DB table: $t", 'ok' => true, 'detail' => 'OK'];
            } catch (\Throwable $e) {
                $checks[] = ['name' => "DB table: $t", 'ok' => false, 'detail' => $e->getMessage()];
            }
        }

        // pdftotext availability
        $pdftotext = @shell_exec('which pdftotext 2>/dev/null');
        $checks[] = ['name' => 'pdftotext installed', 'ok' => is_string($pdftotext) && trim($pdftotext) !== '', 'detail' => trim((string)$pdftotext)];

        return view('titanzero::admin.doctor', compact('checks'));
    }
}
