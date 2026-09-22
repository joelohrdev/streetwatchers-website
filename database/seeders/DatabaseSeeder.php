<?php

namespace Database\Seeders;

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Enums\Country;
use App\Enums\EventRsvpStatus;
use App\Enums\PhotoStatus;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Correspondent;
use App\Models\Event;
use App\Models\Like;
use App\Models\Photo;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => UserRole::SuperAdmin,
        ]);

        $users = User::factory(11)->create(['role' => UserRole::Member])->prepend($testUser);

        $tags = collect(['street', 'portrait', 'night', 'protest', 'market', 'transit', 'architecture', 'candid'])
            ->map(fn (string $name) => Tag::factory()->create(['name' => $name]));

        $chapters = collect([
            ['name' => 'London Streetwatchers', 'slug' => 'london', 'city' => 'London', 'country' => Country::UnitedKingdom, 'latitude' => 51.5072, 'longitude' => -0.1276],
            ['name' => 'New York Streetwatchers', 'slug' => 'new-york', 'city' => 'New York', 'country' => Country::UnitedStates, 'latitude' => 40.7128, 'longitude' => -74.0060],
            ['name' => 'Mexico City Streetwatchers', 'slug' => 'mexico-city', 'city' => 'Mexico City', 'country' => Country::Mexico, 'latitude' => 19.4326, 'longitude' => -99.1332],
        ])->map(fn (array $attributes) => Chapter::factory()->create([...$attributes, 'status' => ChapterStatus::Active]));

        $readyForApproval = Chapter::factory()->pending()->create([
            'name' => 'Lagos Streetwatchers', 'slug' => 'lagos', 'city' => 'Lagos', 'country' => Country::Nigeria, 'latitude' => 6.5244, 'longitude' => 3.3792,
        ]);
        $readyForApproval->members()->attach($users->except([$testUser->id])->random(2), ['role' => ChapterMemberRole::Admin]);

        $needsAnotherAdmin = Chapter::factory()->pending()->create([
            'name' => 'Berlin Streetwatchers', 'slug' => 'berlin', 'city' => 'Berlin', 'country' => Country::Germany, 'latitude' => 52.5200, 'longitude' => 13.4050,
        ]);
        $berlinMembers = $users->except([$testUser->id])->random(3);
        $needsAnotherAdmin->members()->attach($berlinMembers->first(), ['role' => ChapterMemberRole::Admin]);
        $needsAnotherAdmin->members()->attach($berlinMembers->skip(1), ['role' => ChapterMemberRole::Member]);

        User::factory()->suspended()->create([
            'name' => 'Suspended Example',
            'email' => 'suspended@example.com',
        ]);

        foreach ($chapters as $chapter) {
            $members = $users->random(5);

            $chapter->members()->attach($members->first(), ['role' => ChapterMemberRole::Admin]);
            $chapter->members()->attach($members->skip(1), ['role' => ChapterMemberRole::Member]);

            foreach ($members as $member) {
                Photo::factory(2)
                    ->for($member)
                    ->for($chapter)
                    ->published()
                    ->create([
                        'latitude' => $chapter->latitude + fake()->randomFloat(4, -0.05, 0.05),
                        'longitude' => $chapter->longitude + fake()->randomFloat(4, -0.05, 0.05),
                    ])
                    ->each(fn (Photo $photo) => $photo->tags()->attach($tags->random(2)));
            }
        }

        $photos = Photo::all();

        foreach ($photos->random(15) as $photo) {
            Comment::factory()->for($photo)->for($users->random())->create();

            foreach ($users->random(3) as $liker) {
                Like::factory()->for($photo)->for($liker)->create();
            }
        }

        foreach ($users as $user) {
            $user->following()->attach($users->except([$user->id])->random(3));
        }

        $this->call(CollectiveSeeder::class);

        $this->call(ChapterSeeder::class);

        foreach ($chapters as $chapter) {
            $event = Event::factory()
                ->for($chapter)
                ->for($chapter->members()->wherePivot('role', ChapterMemberRole::Admin)->first(), 'organizer')
                ->create([
                    'location_name' => $chapter->city.' Central Station',
                    'latitude' => $chapter->latitude,
                    'longitude' => $chapter->longitude,
                    'starts_at' => now()->addWeeks(2)->setTime(10, 0),
                    'ends_at' => now()->addWeeks(2)->setTime(13, 0),
                ]);

            foreach ($chapter->members as $member) {
                $event->rsvps()->attach($member, ['status' => fake()->randomElement(EventRsvpStatus::cases())]);
            }
        }

        $correspondentUsers = $users->except([$testUser->id])->random(2);

        foreach ($correspondentUsers as $correspondentUser) {
            $correspondentUser->update(['role' => UserRole::Correspondent]);

            Correspondent::factory()->for($correspondentUser)->create(['is_active' => true]);
            Article::factory(2)->for($correspondentUser)->create();
        }

        Report::factory()
            ->for($photos->random(), 'reportable')
            ->for($users->random(), 'reporter')
            ->create(['status' => ReportStatus::Open]);

        Report::factory()
            ->for(Comment::query()->inRandomOrder()->first(), 'reportable')
            ->for($users->random(), 'reporter')
            ->create(['status' => ReportStatus::Reviewed]);

        Report::factory()
            ->publicSubmission()
            ->for($photos->where('status', PhotoStatus::Published)->random(), 'reportable')
            ->create([
                'reason' => 'I am the person in this photo and I would like it removed.',
                'status' => ReportStatus::Open,
            ]);
    }
}
