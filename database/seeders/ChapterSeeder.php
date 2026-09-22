<?php

namespace Database\Seeders;

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Enums\Country;
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
     * @var list<array{0: string, 1: Country, 2: float, 3: float}>
     */
    private const CITIES = [
        // Europe
        ['Paris', Country::France, 48.8566, 2.3522],
        ['Marseille', Country::France, 43.2965, 5.3698],
        ['Lyon', Country::France, 45.7640, 4.8357],
        ['Madrid', Country::Spain, 40.4168, -3.7038],
        ['Barcelona', Country::Spain, 41.3874, 2.1686],
        ['Seville', Country::Spain, 37.3891, -5.9845],
        ['Valencia', Country::Spain, 39.4699, -0.3763],
        ['Lisbon', Country::Portugal, 38.7223, -9.1393],
        ['Porto', Country::Portugal, 41.1579, -8.6291],
        ['Rome', Country::Italy, 41.9028, 12.4964],
        ['Milan', Country::Italy, 45.4642, 9.1900],
        ['Naples', Country::Italy, 40.8518, 14.2681],
        ['Amsterdam', Country::Netherlands, 52.3676, 4.9041],
        ['Rotterdam', Country::Netherlands, 51.9244, 4.4777],
        ['Brussels', Country::Belgium, 50.8503, 4.3517],
        ['Munich', Country::Germany, 48.1351, 11.5820],
        ['Hamburg', Country::Germany, 53.5511, 9.9937],
        ['Zurich', Country::Switzerland, 47.3769, 8.5417],
        ['Vienna', Country::Austria, 48.2082, 16.3738],
        ['Prague', Country::Czechia, 50.0755, 14.4378],
        ['Warsaw', Country::Poland, 52.2297, 21.0122],
        ['Kraków', Country::Poland, 50.0647, 19.9450],
        ['Budapest', Country::Hungary, 47.4979, 19.0402],
        ['Belgrade', Country::Serbia, 44.7866, 20.4489],
        ['Bucharest', Country::Romania, 44.4268, 26.1025],
        ['Sofia', Country::Bulgaria, 42.6977, 23.3219],
        ['Athens', Country::Greece, 37.9838, 23.7275],
        ['Istanbul', Country::Turkiye, 41.0082, 28.9784],
        ['Kyiv', Country::Ukraine, 50.4501, 30.5234],
        ['Tbilisi', Country::Georgia, 41.7151, 44.8271],
        ['Copenhagen', Country::Denmark, 55.6761, 12.5683],
        ['Stockholm', Country::Sweden, 59.3293, 18.0686],
        ['Oslo', Country::Norway, 59.9139, 10.7522],
        ['Helsinki', Country::Finland, 60.1699, 24.9384],
        ['Tallinn', Country::Estonia, 59.4370, 24.7536],
        ['Vilnius', Country::Lithuania, 54.6872, 25.2797],
        ['Reykjavík', Country::Iceland, 64.1466, -21.9426],
        ['Dublin', Country::Ireland, 53.3498, -6.2603],
        ['Edinburgh', Country::UnitedKingdom, 55.9533, -3.1883],
        ['Manchester', Country::UnitedKingdom, 53.4808, -2.2426],
        ['Bristol', Country::UnitedKingdom, 51.4545, -2.5879],
        ['Belfast', Country::UnitedKingdom, 54.5973, -5.9301],
        ['Cardiff', Country::UnitedKingdom, 51.4816, -3.1791],
        ['Leeds', Country::UnitedKingdom, 53.8008, -1.5491],
        ['Brighton', Country::UnitedKingdom, 50.8225, -0.1372],
        ['Liverpool', Country::UnitedKingdom, 53.4084, -2.9916],

        // North and Central America
        ['Los Angeles', Country::UnitedStates, 34.0522, -118.2437],
        ['San Francisco', Country::UnitedStates, 37.7749, -122.4194],
        ['Chicago', Country::UnitedStates, 41.8781, -87.6298],
        ['Boston', Country::UnitedStates, 42.3601, -71.0589],
        ['Philadelphia', Country::UnitedStates, 39.9526, -75.1652],
        ['Washington', Country::UnitedStates, 38.9072, -77.0369],
        ['Miami', Country::UnitedStates, 25.7617, -80.1918],
        ['Atlanta', Country::UnitedStates, 33.7490, -84.3880],
        ['New Orleans', Country::UnitedStates, 29.9511, -90.0715],
        ['Austin', Country::UnitedStates, 30.2672, -97.7431],
        ['Houston', Country::UnitedStates, 29.7604, -95.3698],
        ['Denver', Country::UnitedStates, 39.7392, -104.9903],
        ['Seattle', Country::UnitedStates, 47.6062, -122.3321],
        ['Portland', Country::UnitedStates, 45.5152, -122.6784],
        ['Detroit', Country::UnitedStates, 42.3314, -83.0458],
        ['Minneapolis', Country::UnitedStates, 44.9778, -93.2650],
        ['Toronto', Country::Canada, 43.6532, -79.3832],
        ['Montreal', Country::Canada, 45.5017, -73.5673],
        ['Vancouver', Country::Canada, 49.2827, -123.1207],
        ['Guadalajara', Country::Mexico, 20.6597, -103.3496],
        ['Oaxaca', Country::Mexico, 17.0732, -96.7266],
        ['Havana', Country::Cuba, 23.1136, -82.3666],
        ['Guatemala City', Country::Guatemala, 14.6349, -90.5069],

        // South America
        ['São Paulo', Country::Brazil, -23.5505, -46.6333],
        ['Rio de Janeiro', Country::Brazil, -22.9068, -43.1729],
        ['Buenos Aires', Country::Argentina, -34.6037, -58.3816],
        ['Santiago', Country::Chile, -33.4489, -70.6693],
        ['Lima', Country::Peru, -12.0464, -77.0428],
        ['Bogotá', Country::Colombia, 4.7110, -74.0721],
        ['Medellín', Country::Colombia, 6.2476, -75.5658],
        ['Quito', Country::Ecuador, -0.1807, -78.4678],
        ['Montevideo', Country::Uruguay, -34.9011, -56.1645],
        ['La Paz', Country::Bolivia, -16.4897, -68.1193],
        ['Caracas', Country::Venezuela, 10.4806, -66.9036],

        // Africa
        ['Cairo', Country::Egypt, 30.0444, 31.2357],
        ['Casablanca', Country::Morocco, 33.5731, -7.5898],
        ['Marrakesh', Country::Morocco, 31.6295, -7.9811],
        ['Tunis', Country::Tunisia, 36.8065, 10.1815],
        ['Dakar', Country::Senegal, 14.7167, -17.4677],
        ['Accra', Country::Ghana, 5.6037, -0.1870],
        ['Kinshasa', Country::DemocraticRepublicOfTheCongo, -4.4419, 15.2663],
        ['Addis Ababa', Country::Ethiopia, 9.0300, 38.7400],
        ['Nairobi', Country::Kenya, -1.2921, 36.8219],
        ['Kampala', Country::Uganda, 0.3476, 32.5825],
        ['Kigali', Country::Rwanda, -1.9441, 30.0619],
        ['Dar es Salaam', Country::Tanzania, -6.7924, 39.2083],
        ['Johannesburg', Country::SouthAfrica, -26.2041, 28.0473],
        ['Cape Town', Country::SouthAfrica, -33.9249, 18.4241],

        // Asia and the Middle East
        ['Tokyo', Country::Japan, 35.6762, 139.6503],
        ['Osaka', Country::Japan, 34.6937, 135.5023],
        ['Seoul', Country::SouthKorea, 37.5665, 126.9780],
        ['Beijing', Country::China, 39.9042, 116.4074],
        ['Shanghai', Country::China, 31.2304, 121.4737],
        ['Hong Kong', Country::HongKong, 22.3193, 114.1694],
        ['Taipei', Country::Taiwan, 25.0330, 121.5654],
        ['Bangkok', Country::Thailand, 13.7563, 100.5018],
        ['Hanoi', Country::Vietnam, 21.0278, 105.8342],
        ['Ho Chi Minh City', Country::Vietnam, 10.8231, 106.6297],
        ['Manila', Country::Philippines, 14.5995, 120.9842],
        ['Jakarta', Country::Indonesia, -6.2088, 106.8456],
        ['Kuala Lumpur', Country::Malaysia, 3.1390, 101.6869],
        ['Singapore', Country::Singapore, 1.3521, 103.8198],
        ['Mumbai', Country::India, 19.0760, 72.8777],
        ['Delhi', Country::India, 28.7041, 77.1025],
        ['Kolkata', Country::India, 22.5726, 88.3639],
        ['Bengaluru', Country::India, 12.9716, 77.5946],
        ['Dhaka', Country::Bangladesh, 23.8103, 90.4125],
        ['Kathmandu', Country::Nepal, 27.7172, 85.3240],
        ['Karachi', Country::Pakistan, 24.8607, 67.0011],
        ['Almaty', Country::Kazakhstan, 43.2220, 76.8512],
        ['Ulaanbaatar', Country::Mongolia, 47.8864, 106.9057],
        ['Dubai', Country::UnitedArabEmirates, 25.2048, 55.2708],
        ['Tel Aviv', Country::Israel, 32.0853, 34.7818],
        ['Beirut', Country::Lebanon, 33.8938, 35.5018],
        ['Tehran', Country::Iran, 35.6892, 51.3890],

        // Oceania
        ['Sydney', Country::Australia, -33.8688, 151.2093],
        ['Melbourne', Country::Australia, -37.8136, 144.9631],
        ['Brisbane', Country::Australia, -27.4698, 153.0251],
        ['Perth', Country::Australia, -31.9505, 115.8605],
        ['Auckland', Country::NewZealand, -36.8485, 174.7633],
        ['Wellington', Country::NewZealand, -41.2865, 174.7762],
    ];

    /**
     * Opening lines for chapter descriptions. The city name replaces :city.
     *
     * @var list<string>
     */
    private const DESCRIPTIONS = [
        'Weekend walks through :city\'s markets, stations and back streets, followed by an edit session over coffee.',
        'A friendly group documenting everyday life in :city. All cameras and phones welcome, beginners especially.',
        'Early-morning and after-dark photo walks around :city, with a monthly print swap and slideshow night.',
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
