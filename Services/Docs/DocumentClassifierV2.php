<?php

namespace Modules\TitanZero\Services\Docs;

use Modules\TitanZero\Entities\TitanZeroDocument;

class DocumentClassifierV2
{
    public function classify(TitanZeroDocument $doc, string $evidenceText = ''): array
    {
        $meta = (array)($doc->meta ?? []);
        $title = mb_strtolower((string)$doc->title);
        $name  = mb_strtolower((string)($meta['original_name'] ?? ''));
        $toc   = mb_strtolower((string)($meta['toc_text'] ?? ''));
        $head  = mb_strtolower((string)($meta['first_pages_text'] ?? ''));

        $text = $title.' '.$name.' '.mb_substr($toc,0,6000).' '.mb_substr($head,0,6000).' '.mb_substr(mb_strtolower($evidenceText),0,8000);

        $tags = [];
        $docType = null;
        $authority = null;
        $jurisdiction = null;
        $superseded = false;

        $score = 0;

        // superseded detection
        if (preg_match('/\bsuperseded\b|\bwithdrawn\b|\bobsolete\b/', $text)) {
            $superseded = true;
            $score += 10;
        }

        // jurisdiction hints
        if (preg_match('/\bnew south wales\b|\bnsw\b/', $text)) $jurisdiction = 'NSW';
        elseif (preg_match('/\bqueensland\b|\bqld\b/', $text)) $jurisdiction = 'QLD';
        elseif (preg_match('/\bvictoria\b|\bvic\b/', $text)) $jurisdiction = 'VIC';
        elseif (preg_match('/\bsouth australia\b|\bsa\b/', $text)) $jurisdiction = 'SA';
        elseif (preg_match('/\bwestern australia\b|\bwa\b/', $text)) $jurisdiction = 'WA';
        elseif (preg_match('/\btasmania\b|\btas\b/', $text)) $jurisdiction = 'TAS';
        elseif (preg_match('/\baustralia\b|\bau\b/', $text)) $jurisdiction = 'AU';

        // Standards / NCC / AS-NZS
        if (preg_match('/\bncc\b|national construction code|building code of australia|\bas\/nzs\b|australian standard|as \d{3,}/', $text)) {
            $tags[] = 'standards';
            $tags[] = 'compliance';
            $docType = $docType ?? 'standard';
            $authority = $authority ?? 'regulatory';
            $score += 35;
        }

        // WHS/Safety evidence
        if (preg_match('/work health and safety|\bwhs\b|\bswms\b|toolbox talk|risk assessment|hazard|ppe|safe work method/', $text)) {
            $tags[] = 'safety';
            $docType = $docType ?? 'guide';
            $score += 20;
        }

        // Business ops evidence
        if (preg_match('/how to run|operations manual|business plan|pricing strategy|client communication|project profitability|\bcashflow\b|\bcash flow\b/', $text)) {
            $tags[] = 'business';
            $authority = $authority ?? 'industry_guide';
            $docType = $docType ?? 'book';
            $score += 20;
        }

        // Pricing/estimation/contracts/finance
        if (preg_match('/markup|margin|charge out|hourly rate|\bpricing\b/', $text)) { $tags[]='pricing'; $score += 10; }
        if (preg_match('/\bestimat\w+\b|quantity takeoff|take-off|bill of quantities/', $text)) { $tags[]='estimation'; $score += 10; }
        if (preg_match('/\bcontract\b|variation|scope of work|progress claim|\bdispute\b|security of payment/', $text)) { $tags[]='contracts'; $score += 10; }
        if (preg_match('/\bprofit\b|\bbudget\b|\baccounting\b|\binvoice\b|\bpayment terms\b/', $text)) { $tags[]='finance'; $score += 8; }

        // Site/foreman
        if (preg_match('/prestart|daily plan|site diary|supervisor|foreman|sequence of works|installation sequence/', $text)) {
            $tags[]='site';
            $tags[]='foreman';
            $docType = $docType ?? 'guide';
            $score += 15;
        }

        $tags = array_values(array_unique($tags));

        // Confidence: cap 0..100, require at least one strong signal
        $confidence = min(100, $score);
        if (count($tags) === 0) $confidence = 0;

        return [
            'doc_type' => $docType,
            'authority_level' => $authority,
            'jurisdiction' => $jurisdiction,
            'is_superseded' => $superseded,
            'tag_keys' => $tags,
            'confidence' => $confidence,
        ];
    }
}
