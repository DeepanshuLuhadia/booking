<?php

namespace App\Providers;

use App\Services\AdminBadgeService;
use App\Services\CustomerBookingService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
        | The admin panel's badge counters.
        |
        | A singleton because the admin layout renders its menu twice per page
        | (desktop sidebar + mobile sheet) and the dashboard reads the same
        | figures again. Without this the four counting queries would run three
        | times over on every admin page load.
        */
        $this->app->singleton(AdminBadgeService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // if (app()->environment('local')) {
        //     URL::forceScheme('https');
        // }

        /*
        | The "My Bookings" badge in the site navigation. Bound here rather than
        | in each controller because the layout is shared by every customer-facing
        | page, and a visitor holding a token at one shop must see it from any of
        | them — that invisibility is what let them walk into a booking refusal
        | with nothing on screen to explain it.
        |
        | The count query only runs for a visitor we can actually identify (a
        | booking phone in the session/cookie, or a signed-in customer), so the
        | anonymous first visit stays a pure cache/render with no extra query.
        */
        View::composer('components.app-layout', function ($view) {
            $bookings   = app(CustomerBookingService::class);
            $identified = $bookings->isIdentified();

            $view->with([
                'myBookingCount' => $identified ? $bookings->liveBookingCount() : 0,

                /*
                | True when this device is holding a booking we currently have no
                | way to notify about. Browsers will not hand over a push token
                | without permission, and permission is only asked for once — so a
                | customer who dismissed the prompt (or booked before it appeared)
                | is silently unreachable for the rest of that booking. This lets
                | the layout re-offer it at the one moment it plainly matters.
                */
                'pushSetupNeeded' => $identified && $bookings->hasLiveBookingsMissingPush(),
            ]);
        });
    }
}
