<?php

namespace App\Providers;

use App\Modules\Assignment\Events\AssignmentCreated;
use App\Modules\Assignment\Events\AssignmentResponded;
use App\Modules\Assignment\Events\AssignmentSuperseded;
use App\Modules\Assignment\Listeners\RecordAssignmentCreatedNotification;
use App\Modules\Assignment\Listeners\RecordAssignmentRespondedNotification;
use App\Modules\Assignment\Listeners\RecordAssignmentSupersededNotification;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\WorkOrder\Events\WorkOrderStatusChanged;
use App\Modules\WorkOrder\Listeners\RecordWorkOrderStatusNotification;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Policies\AssignmentPolicy;
use App\Policies\TrackingSessionPolicy;
use App\Policies\WorkOrderPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(TrackingSession::class, TrackingSessionPolicy::class);
        Gate::policy(WorkOrder::class, WorkOrderPolicy::class);

        Event::listen(AssignmentCreated::class, RecordAssignmentCreatedNotification::class);
        Event::listen(AssignmentResponded::class, RecordAssignmentRespondedNotification::class);
        Event::listen(AssignmentSuperseded::class, RecordAssignmentSupersededNotification::class);
        Event::listen(WorkOrderStatusChanged::class, RecordWorkOrderStatusNotification::class);

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by((string) $request->user()?->getAuthIdentifier() ?: $request->ip()));
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by((string) $request->input('email').$request->ip()));
        RateLimiter::for('tracking', fn (Request $request) => Limit::perMinute(120)->by((string) $request->user()?->getAuthIdentifier()));
        RateLimiter::for('public-tracking', fn (Request $request) => Limit::perMinute(30)->by((string) $request->ip()));
    }
}
