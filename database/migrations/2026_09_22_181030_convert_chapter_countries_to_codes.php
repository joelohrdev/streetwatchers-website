<?php

use App\Enums\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Common ways of writing a country that don't match its label.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'uk' => 'GB',
        'greatbritain' => 'GB',
        'britain' => 'GB',
        'england' => 'GB',
        'scotland' => 'GB',
        'wales' => 'GB',
        'northernireland' => 'GB',
        'us' => 'US',
        'usa' => 'US',
        'unitedstatesofamerica' => 'US',
        'america' => 'US',
        'drcongo' => 'CD',
        'drc' => 'CD',
        'congokinshasa' => 'CD',
        'congobrazzaville' => 'CG',
        'turkey' => 'TR',
        'czechrepublic' => 'CZ',
        'korea' => 'KR',
        'holland' => 'NL',
        'thenetherlands' => 'NL',
        'ivorycoast' => 'CI',
        'burma' => 'MM',
        'uae' => 'AE',
        'russianfederation' => 'RU',
        'swaziland' => 'SZ',
        'macedonia' => 'MK',
        'capeverde' => 'CV',
        'caboverde' => 'CV',
        'eastimor' => 'TL',
        'vatican' => 'VA',
        'holysee' => 'VA',
    ];

    /**
     * Replace free-text country names on chapters with ISO 3166-1 alpha-2 codes.
     */
    public function up(): void
    {
        $unmatched = [];

        foreach (DB::table('chapters')->distinct()->pluck('country') as $name) {
            $country = $this->match($name);

            if ($country === null) {
                $unmatched[] = $name;

                continue;
            }

            DB::table('chapters')->where('country', $name)->update(['country' => $country->value]);
        }

        if ($unmatched !== []) {
            throw new RuntimeException('Could not match these chapter countries to a code; fix them by hand and migrate again: '.implode(', ', $unmatched));
        }
    }

    /**
     * Put the country names back.
     */
    public function down(): void
    {
        foreach (Country::cases() as $country) {
            DB::table('chapters')->where('country', $country->value)->update(['country' => $country->label()]);
        }
    }

    /**
     * Find the country a stored name refers to, ignoring case, accents and punctuation.
     */
    private function match(string $name): ?Country
    {
        $key = $this->normalise($name);

        if (isset(self::ALIASES[$key])) {
            return Country::from(self::ALIASES[$key]);
        }

        foreach (Country::cases() as $country) {
            if ($key === strtolower($country->value) || $key === $this->normalise($country->label())) {
                return $country;
            }
        }

        return null;
    }

    private function normalise(string $name): string
    {
        $ascii = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $name);

        return preg_replace('/[^a-z0-9]/', '', str_replace('&', 'and', $ascii));
    }
};
