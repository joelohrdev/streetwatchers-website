<?php

namespace App\Concerns;

use Illuminate\Support\Str;

/**
 * Shortens text for link previews on WhatsApp, Facebook, X and so on.
 */
trait WritesLinkPreviews
{
    private const PREVIEW_LENGTH = 200;

    /**
     * Text of at most 200 characters. It ends at the last full sentence when one finishes in the second half of
     * that length, and otherwise at the last whole word, followed by "…". The second-half rule keeps a short
     * opening sentence from leaving an almost empty preview.
     */
    protected function previewText(string $text): string
    {
        $text = Str::squish($text);

        if (mb_strlen($text) <= self::PREVIEW_LENGTH) {
            return $text;
        }

        // One character past the limit, so a sentence ending exactly at the limit still counts.
        $window = mb_substr($text, 0, self::PREVIEW_LENGTH + 1);

        if (preg_match_all('/[.!?](?=\s)/u', $window, $matches, PREG_OFFSET_CAPTURE)) {
            $end = mb_strlen(substr($window, 0, end($matches[0])[1])) + 1;

            if ($end > self::PREVIEW_LENGTH / 2) {
                return mb_substr($text, 0, $end);
            }
        }

        return Str::limit($text, self::PREVIEW_LENGTH, '…', preserveWords: true);
    }
}
