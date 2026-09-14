<?php

namespace Database\Seeders;

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds chapters in real cities around the world so the chapter directory has
 * realistic data to search and sort by distance.
 */
class ChapterSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * City, country, latitude and longitude for each seeded chapter.
     *
     * @var list<array{0: string, 1: string, 2: float, 3: float}>
     */
    private const CITIES = [
        // Europe
        ['Paris', 'France', 48.8566, 2.3522],
        ['Marseille', 'France', 43.2965, 5.3698],
        ['Lyon', 'France', 45.7640, 4.8357],
        ['Madrid', 'Spain', 40.4168, -3.7038],
        ['Barcelona', 'Spain', 41.3874, 2.1686],
        ['Seville', 'Spain', 37.3891, -5.9845],
        ['Valencia', 'Spain', 39.4699, -0.3763],
        ['Lisbon', 'Portugal', 38.7223, -9.1393],
        ['Porto', 'Portugal', 41.1579, -8.6291],
        ['Rome', 'Italy', 41.9028, 12.4964],
        ['Milan', 'Italy', 45.4642, 9.1900],
        ['Naples', 'Italy', 40.8518, 14.2681],
        ['Amsterdam', 'Netherlands', 52.3676, 4.9041],
        ['Rotterdam', 'Netherlands', 51.9244, 4.4777],
        ['Brussels', 'Belgium', 50.8503, 4.3517],
        ['Munich', 'Germany', 48.1351, 11.5820],
        ['Hamburg', 'Germany', 53.5511, 9.9937],
        ['Zurich', 'Switzerland', 47.3769, 8.5417],
        ['Vienna', 'Austria', 48.2082, 16.3738],
        ['Prague', 'Czechia', 50.0755, 14.4378],
        ['Warsaw', 'Poland', 52.2297, 21.0122],
        ['Kraków', 'Poland', 50.0647, 19.9450],
        ['Budapest', 'Hungary', 47.4979, 19.0402],
        ['Belgrade', 'Serbia', 44.7866, 20.4489],
        ['Bucharest', 'Romania', 44.4268, 26.1025],
        ['Sofia', 'Bulgaria', 42.6977, 23.3219],
        ['Athens', 'Greece', 37.9838, 23.7275],
        ['Istanbul', 'Türkiye', 41.0082, 28.9784],
        ['Kyiv', 'Ukraine', 50.4501, 30.5234],
        ['Tbilisi', 'Georgia', 41.7151, 44.8271],
        ['Copenhagen', 'Denmark', 55.6761, 12.5683],
        ['Stockholm', 'Sweden', 59.3293, 18.0686],
        ['Oslo', 'Norway', 59.9139, 10.7522],
        ['Helsinki', 'Finland', 60.1699, 24.9384],
        ['Tallinn', 'Estonia', 59.4370, 24.7536],
        ['Vilnius', 'Lithuania', 54.6872, 25.2797],
        ['Reykjavík', 'Iceland', 64.1466, -21.9426],
        ['Dublin', 'Ireland', 53.3498, -6.2603],
        ['Edinburgh', 'United Kingdom', 55.9533, -3.1883],
        ['Manchester', 'United Kingdom', 53.4808, -2.2426],
        ['Bristol', 'United Kingdom', 51.4545, -2.5879],
        ['Belfast', 'United Kingdom', 54.5973, -5.9301],
        ['Cardiff', 'United Kingdom', 51.4816, -3.1791],
        ['Leeds', 'United Kingdom', 53.8008, -1.5491],
        ['Brighton', 'United Kingdom', 50.8225, -0.1372],
        ['Liverpool', 'United Kingdom', 53.4084, -2.9916],

        // North and Central America
        ['Los Angeles', 'United States', 34.0522, -118.2437],
        ['San Francisco', 'United States', 37.7749, -122.4194],
        ['Chicago', 'United States', 41.8781, -87.6298],
        ['Boston', 'United States', 42.3601, -71.0589],
        ['Philadelphia', 'United States', 39.9526, -75.1652],
        ['Washington', 'United States', 38.9072, -77.0369],
        ['Miami', 'United States', 25.7617, -80.1918],
        ['Atlanta', 'United States', 33.7490, -84.3880],
        ['New Orleans', 'United States', 29.9511, -90.0715],
        ['Austin', 'United States', 30.2672, -97.7431],
        ['Houston', 'United States', 29.7604, -95.3698],
        ['Denver', 'United States', 39.7392, -104.9903],
        ['Seattle', 'United States', 47.6062, -122.3321],
        ['Portland', 'United States', 45.5152, -122.6784],
        ['Detroit', 'United States', 42.3314, -83.0458],
        ['Minneapolis', 'United States', 44.9778, -93.2650],
        ['Toronto', 'Canada', 43.6532, -79.3832],
        ['Montreal', 'Canada', 45.5017, -73.5673],
        ['Vancouver', 'Canada', 49.2827, -123.1207],
        ['Guadalajara', 'Mexico', 20.6597, -103.3496],
        ['Oaxaca', 'Mexico', 17.0732, -96.7266],
        ['Havana', 'Cuba', 23.1136, -82.3666],
        ['Guatemala City', 'Guatemala', 14.6349, -90.5069],

        // South America
        ['São Paulo', 'Brazil', -23.5505, -46.6333],
        ['Rio de Janeiro', 'Brazil', -22.9068, -43.1729],
        ['Buenos Aires', 'Argentina', -34.6037, -58.3816],
        ['Santiago', 'Chile', -33.4489, -70.6693],
        ['Lima', 'Peru', -12.0464, -77.0428],
        ['Bogotá', 'Colombia', 4.7110, -74.0721],
        ['Medellín', 'Colombia', 6.2476, -75.5658],
        ['Quito', 'Ecuador', -0.1807, -78.4678],
        ['Montevideo', 'Uruguay', -34.9011, -56.1645],
        ['La Paz', 'Bolivia', -16.4897, -68.1193],
        ['Caracas', 'Venezuela', 10.4806, -66.9036],

        // Africa
        ['Cairo', 'Egypt', 30.0444, 31.2357],
        ['Casablanca', 'Morocco', 33.5731, -7.5898],
        ['Marrakesh', 'Morocco', 31.6295, -7.9811],
        ['Tunis', 'Tunisia', 36.8065, 10.1815],
        ['Dakar', 'Senegal', 14.7167, -17.4677],
        ['Accra', 'Ghana', 5.6037, -0.1870],
        ['Kinshasa', 'DR Congo', -4.4419, 15.2663],
        ['Addis Ababa', 'Ethiopia', 9.0300, 38.7400],
        ['Nairobi', 'Kenya', -1.2921, 36.8219],
        ['Kampala', 'Uganda', 0.3476, 32.5825],
        ['Kigali', 'Rwanda', -1.9441, 30.0619],
        ['Dar es Salaam', 'Tanzania', -6.7924, 39.2083],
        ['Johannesburg', 'South Africa', -26.2041, 28.0473],
        ['Cape Town', 'South Africa', -33.9249, 18.4241],

        // Asia and the Middle East
        ['Tokyo', 'Japan', 35.6762, 139.6503],
        ['Osaka', 'Japan', 34.6937, 135.5023],
        ['Seoul', 'South Korea', 37.5665, 126.9780],
        ['Beijing', 'China', 39.9042, 116.4074],
        ['Shanghai', 'China', 31.2304, 121.4737],
        ['Hong Kong', 'Hong Kong', 22.3193, 114.1694],
        ['Taipei', 'Taiwan', 25.0330, 121.5654],
        ['Bangkok', 'Thailand', 13.7563, 100.5018],
        ['Hanoi', 'Vietnam', 21.0278, 105.8342],
        ['Ho Chi Minh City', 'Vietnam', 10.8231, 106.6297],
        ['Manila', 'Philippines', 14.5995, 120.9842],
        ['Jakarta', 'Indonesia', -6.2088, 106.8456],
        ['Kuala Lumpur', 'Malaysia', 3.1390, 101.6869],
        ['Singapore', 'Singapore', 1.3521, 103.8198],
        ['Mumbai', 'India', 19.0760, 72.8777],
        ['Delhi', 'India', 28.7041, 77.1025],
        ['Kolkata', 'India', 22.5726, 88.3639],
        ['Bengaluru', 'India', 12.9716, 77.5946],
        ['Dhaka', 'Bangladesh', 23.8103, 90.4125],
        ['Kathmandu', 'Nepal', 27.7172, 85.3240],
        ['Karachi', 'Pakistan', 24.8607, 67.0011],
        ['Almaty', 'Kazakhstan', 43.2220, 76.8512],
        ['Ulaanbaatar', 'Mongolia', 47.8864, 106.9057],
        ['Dubai', 'United Arab Emirates', 25.2048, 55.2708],
        ['Tel Aviv', 'Israel', 32.0853, 34.7818],
        ['Beirut', 'Lebanon', 33.8938, 35.5018],
        ['Tehran', 'Iran', 35.6892, 51.3890],

        // Oceania
        ['Sydney', 'Australia', -33.8688, 151.2093],
        ['Melbourne', 'Australia', -37.8136, 144.9631],
        ['Brisbane', 'Australia', -27.4698, 153.0251],
        ['Perth', 'Australia', -31.9505, 115.8605],
        ['Auckland', 'New Zealand', -36.8485, 174.7633],
        ['Wellington', 'New Zealand', -41.2865, 174.7762],
    ];

    /**
     * Opening lines for chapter descriptions. The city name replaces :city.
     *
     * @var list<string>
     */
    private const DESCRIPTIONS = [
        'Weekend walks through :city\'s markets, stations and back streets, followed by an edit session over coffee.',
        'A friendly group documenting everyday life in :city. All cameras and phones welcome, beginners especially.',
        'Early-morning and after-dark photo walks around :city, with a monthly print swap and critique night.',
        'We shoot the neighbourhoods of :city that rarely make the postcards, and share the results in a monthly zine.',
        'Candid street photography around :city, with regular walks, shared assignments and an annual group show.',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->where('email', '!=', 'test@example.com')->get();

        foreach (self::CITIES as $index => [$city, $country, $latitude, $longitude]) {
            $chapter = Chapter::factory()->create([
                'name' => "{$city} Streetwatchers",
                'slug' => Str::slug("{$city} Streetwatchers"),
                'city' => $city,
                'country' => $country,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'description' => str_replace(':city', $city, self::DESCRIPTIONS[$index % count(self::DESCRIPTIONS)]),
                'status' => $this->statusFor($index),
            ]);

            $members = $users->random(min($users->count(), fake()->numberBetween(3, 6)));

            $chapter->members()->attach($members->take(2), ['role' => ChapterMemberRole::Admin]);
            $chapter->members()->attach($members->skip(2), ['role' => ChapterMemberRole::Member]);
        }
    }

    /**
     * Mostly active chapters, with a few awaiting approval or wound down so the admin filters have data.
     */
    private function statusFor(int $index): ChapterStatus
    {
        return match (true) {
            $index % 20 === 7 => ChapterStatus::Pending,
            $index % 25 === 13 => ChapterStatus::Inactive,
            default => ChapterStatus::Active,
        };
    }
}
