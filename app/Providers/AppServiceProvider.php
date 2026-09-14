<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Chapter;
use App\Models\Collective;
use App\Models\Comment;
use App\Models\Correspondent;
use App\Models\CritiqueComment;
use App\Models\CritiqueGroup;
use App\Models\Photo;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureMorphMap();
        $this->configureGates();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Store short, rename-safe names in polymorphic type columns such as reports.reportable_type.
     */
    protected function configureMorphMap(): void
    {
        Relation::enforceMorphMap([
            'article' => Article::class,
            'chapter' => Chapter::class,
            'collective' => Collective::class,
            'comment' => Comment::class,
            'correspondent' => Correspondent::class,
            'critique_comment' => CritiqueComment::class,
            'critique_group' => CritiqueGroup::class,
            'photo' => Photo::class,
            'report' => Report::class,
            'tag' => Tag::class,
            'user' => User::class,
        ]);
    }

    /**
     * Define the gates that guard whole areas of the application.
     */
    protected function configureGates(): void
    {
        Gate::define('access-admin', fn (User $user): bool => $user->isSuperAdmin() && $user->isActive());
    }
}
