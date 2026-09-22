<?php

namespace App\Concerns;

/**
 * A short, URL-safe code for a model: its ID written in base 62 (0-9, a-z, A-Z). ID 12345 becomes "3d7".
 * Codes need no storage and never change. They're guessable, so only use them for public pages.
 */
trait HasShortCode
{
    private const SHORT_CODE_ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function shortCode(): string
    {
        $id = (int) $this->getKey();
        $code = '';

        do {
            $code = self::SHORT_CODE_ALPHABET[$id % 62].$code;
            $id = intdiv($id, 62);
        } while ($id > 0);

        return $code;
    }

    /**
     * The model a short code refers to, or null if the code is malformed or nothing has that ID. Ten characters
     * cover IDs far beyond any real table while keeping the decoded number inside PHP's integer range.
     */
    public static function findByShortCode(string $code): ?static
    {
        if ($code === '' || strlen($code) > 10 || strspn($code, self::SHORT_CODE_ALPHABET) !== strlen($code)) {
            return null;
        }

        $id = 0;

        foreach (str_split($code) as $character) {
            $id = $id * 62 + strpos(self::SHORT_CODE_ALPHABET, $character);
        }

        return static::query()->find($id);
    }
}
