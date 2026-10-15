<?php

namespace App\Providers;

use App\Listeners\SecurityEventSubscriber;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Goal;
use App\Models\GoalAssignment;
use App\Models\GoalObjective;
use App\Models\GoalPillar;
use App\Models\Position;
use App\Models\Quarter;
use App\Models\QuarterlyReflection;
use App\Models\Section;
use App\Models\SupervisorReview;
use App\Models\User;
use App\Models\WeeklyUpdate;
use App\Observers\AuditableObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        Schema::defaultStringLength(191);

        if (config('security.force_https')) {
            URL::forceScheme('https');
        }

        foreach ([
            User::class,
            Department::class,
            Section::class,
            Position::class,
            Quarter::class,
            GoalPillar::class,
            Goal::class,
            GoalAssignment::class,
            GoalObjective::class,
            WeeklyUpdate::class,
            SupervisorReview::class,
            QuarterlyReflection::class,
            CompanySetting::class,
            Role::class,
            Permission::class,
        ] as $model) {
            $model::observe(AuditableObserver::class);
        }

        Event::subscribe(SecurityEventSubscriber::class);
    }
}
