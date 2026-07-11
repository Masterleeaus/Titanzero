<?php

namespace Modules\TitanZero\Services\Docs;

class TextCleaner
{
    public function clean(string $text): string
    {
        $text = str_replace("\r", "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }
}
