<?php

namespace Modules\TitanZero\Services\Docs;

use Modules\TitanZero\Entities\TitanZeroDocument;

class DocumentClassifier
{
    public function classify(TitanZeroDocument $doc): array
    {
        $title = mb_strtolower((string)$doc->title);
        $meta = (array)($doc->meta ?? []);
        $name = mb_strtolower((string)($meta['original_name'] ?? ''));

        $text = $title . ' ' . $name;

        $tags = [];
        $docType = null;
        $authority = null;
        $jurisdiction = null;
        $superseded = false;

        // superseded detection
        if (str_contains($text, 'superseded') || str_contains($text, 'withdrawn') || str_contains($text, 'obsolete')) {
            $superseded = true;
        }

        // jurisdiction hints
        if (preg_match('/\bnsw\b/', $text)) $jurisdiction = 'NSW';
        elseif (preg_match('/\bqld\b/', $text)) $jurisdiction = 'QLD';
        elseif (preg_match('/\bvic\b/', $text)) $jurisdiction = 'VIC';
        elseif (preg_match('/\bau\b|australia/', $text)) $jurisdiction = 'AU';

        // standards/compliance
        if (str_contains($text, 'as/nzs') || str_contains($text, 'as nzs') || str_contains($text, 'australian standard') || str_contains($text, 'ncc') || str_contains($text, 'national construction code')) {
            $tags[] = 'standards';
            $tags[] = 'compliance';
            $docType = $docType ?? 'standard';
            $authority = $authority ?? 'regulatory';
        }

        if (str_contains($text, 'whs') || str_contains($text, 'work health') || str_contains($text, 'safety') || str_contains($text, 'swms') || str_contains($text, 'risk assessment') || str_contains($text, 'toolbox talk')) {
            $tags[] = 'safety';
            $docType = $docType ?? 'guide';
        }

        // business ops
        if (str_contains($text, 'business') || str_contains($text, 'operations') || str_contains($text, 'how to run') || str_contains($text, 'management') || str_contains($text, 'contractor handbook')) {
            $tags[] = 'business';
            $docType = $docType ?? 'book';
            $authority = $authority ?? 'industry_guide';
        }

        // finance/pricing/estimation/contracts
        if (str_contains($text, 'cashflow') || str_contains($text, 'cash flow') || str_contains($text, 'profit') || str_contains($text, 'budget')) $tags[] = 'finance';
        if (str_contains($text, 'pricing') || str_contains($text, 'markup') || str_contains($text, 'margin')) $tags[] = 'pricing';
        if (str_contains($text, 'estimate') || str_contains($text, 'estimating') || str_contains($text, 'takeoff') || str_contains($text, 'quantity')) $tags[] = 'estimation';
        if (str_contains($text, 'contract') || str_contains($text, 'variation') || str_contains($text, 'scope') || str_contains($text, 'dispute')) $tags[] = 'contracts';

        // site execution / foreman
        if (str_contains($text, 'foreman') || str_contains($text, 'supervisor') || str_contains($text, 'site') || str_contains($text, 'prestart') || str_contains($text, 'daily plan') || str_contains($text, 'sequence')) {
            $tags[] = 'site';
            $tags[] = 'foreman';
            $docType = $docType ?? 'guide';
        }

        $tags = array_values(array_unique($tags));

        return [
            'doc_type' => $docType,
            'authority_level' => $authority,
            'jurisdiction' => $jurisdiction,
            'is_superseded' => $superseded,
            'tag_keys' => $tags,
        ];
    }
}
