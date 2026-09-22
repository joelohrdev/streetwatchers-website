<?php

namespace Database\Seeders;

use App\Enums\CollectiveApplicationStatus;
use App\Enums\CollectiveMemberRole;
use App\Models\Collective;
use App\Models\CollectiveApplication;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Seeds a directory of collectives. The test user founds Night Shift, which has applications waiting,
 * is a member of Transit, and has a pending application to Low Tide, so every state can be tried out.
 */
class CollectiveSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Name, home city, description, verified, open to applications.
     *
     * @var list<array{0: string, 1: string, 2: string, 3: bool, 4: bool}>
     */
    private const COLLECTIVES = [
        ['Night Shift', 'Chicago, United States', 'We photograph cities after dark: late trains, neon signs, night workers and the last bus home.', true, true],
        ['Low Tide', 'Lisbon, Portugal', 'A collective documenting coastal towns and working harbours, and the people whose days follow the tide.', true, true],
        ['Concrete Poets', 'London, United Kingdom', 'Everyday life on post-war housing estates, photographed by people who live on them.', true, false],
        ['Market Days', 'Mexico City, Mexico', 'Street markets around the world, from first light set-up to the last crate packed away.', false, true],
        ['Transit', 'Tokyo, Japan', 'Platforms, stations and the in-between moments of the daily commute.', true, true],
        ['Second Glance', 'Berlin, Germany', 'The overlooked corners of ordinary streets: doorways, signs, shadows and the things people leave behind.', false, true],
        ['Heat', 'Madrid, Spain', 'How cities live through summer, from shaded plazas to midnight swims.', false, false],
        ['Weather Report', 'Glasgow, United Kingdom', 'Rain, fog and snow, and how they change the way a street looks and moves.', false, true],
        ['Shopfronts', 'New York, United States', 'Independent shops and the people who run them, before they change or disappear.', true, true],
        ['The Long Walk', 'Melbourne, Australia', 'We walk a city end to end in a single day and publish what we see as one long sequence.', false, false],
        ['Monochrome Hours', 'Buenos Aires, Argentina', 'A black-and-white collective drawn to hard light and deep shadow.', false, true],
        ['Crossings', 'Istanbul, Türkiye', 'Bridges, ferries and borders: places where people pass from one side to another.', false, true],
        ['Home Ground', 'Nairobi, Kenya', 'Each member spends a year photographing the neighborhood they live in.', false, true],
        ['Parade', 'New Orleans, United States', 'Festivals, marches and public celebrations, from the crowd rather than the grandstand.', false, false],
        ['Small Hours', 'Seoul, South Korea', 'Early mornings, when cities are only just waking up.', false, true],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testUser = User::query()->where('email', 'test@example.com')->firstOrFail();
        $others = User::query()
            ->whereKeyNot($testUser->id)
            ->where('status', 'active')
            ->get();

        foreach (self::COLLECTIVES as [$name, $basedIn, $description, $isVerified, $isOpen]) {
            $collective = Collective::factory()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
                'based_in' => $basedIn,
                'website_url' => null,
                'instagram_url' => 'https://instagram.com/'.Str::slug($name, ''),
                'is_verified' => $isVerified,
                'is_open_for_applications' => $isOpen,
            ]);

            $this->seedMembers($collective, $testUser, $others);
            $this->seedApplications($collective, $testUser, $others);
        }
    }

    /**
     * @param  Collection<int, User>  $others
     */
    private function seedMembers(Collective $collective, User $testUser, Collection $others): void
    {
        if ($collective->name === 'Night Shift') {
            $collective->members()->attach($testUser, ['role' => CollectiveMemberRole::Founder]);
            $collective->members()->attach($others->random(3), ['role' => CollectiveMemberRole::Member]);

            return;
        }

        $people = $others->random(fake()->numberBetween(3, 7));

        $collective->members()->attach($people->first(), ['role' => CollectiveMemberRole::Founder]);
        $collective->members()->attach($people->skip(1), ['role' => CollectiveMemberRole::Member]);

        if ($collective->name === 'Transit') {
            $collective->members()->attach($testUser, ['role' => CollectiveMemberRole::Member]);
        }
    }

    /**
     * @param  Collection<int, User>  $others
     */
    private function seedApplications(Collective $collective, User $testUser, Collection $others): void
    {
        $outsiders = $others->whereNotIn('id', $collective->members()->pluck('users.id'))->values();

        if ($collective->name === 'Night Shift') {
            foreach ($outsiders->take(3) as $applicant) {
                CollectiveApplication::factory()->pending()->for($collective)->for($applicant)->create([
                    'message' => 'I mostly shoot on my way home from late shifts and would love to walk with people who do the same.',
                ]);
            }

            return;
        }

        if ($collective->name === 'Low Tide') {
            CollectiveApplication::factory()->pending()->for($collective)->for($testUser)->create([
                'message' => 'I grew up in a harbour town and have been photographing the fishing boats there for years.',
            ]);
        }

        if (! $collective->is_open_for_applications || $outsiders->isEmpty()) {
            return;
        }

        CollectiveApplication::factory()->pending()->for($collective)->for($outsiders->random())->create();

        $founder = $collective->memberships()->where('role', CollectiveMemberRole::Founder)->value('user_id');

        CollectiveApplication::factory()->for($collective)->for($outsiders->random())->create([
            'status' => CollectiveApplicationStatus::Declined,
            'decided_by' => $founder,
            'decided_at' => now()->subDays(fake()->numberBetween(1, 20)),
        ]);
    }
}
