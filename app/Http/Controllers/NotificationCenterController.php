<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * The notification tab on the admin, vendor and employee panels.
 *
 * One controller for all three: notifications hang off the signed-in user, so
 * the only thing that differs between the panels is which layout wraps the page
 * and which route names the buttons post back to. That is derived from the
 * route name prefix rather than duplicated into three controllers.
 */
class NotificationCenterController extends Controller
{
    private const PAGE_SIZE = 20;

    public function index(Request $request)
    {
        $panel = $this->panel($request);

        $notifications = $request->user()
            ->notifications()
            ->paginate(self::PAGE_SIZE);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $request->user()->unreadNotifications()->count(),
            'layout'        => "{$panel}-layout",
            'routePrefix'   => $panel,
        ]);
    }

    /**
     * Tick off one notification. If it carries a destination the click was a
     * navigation, so follow it; otherwise stay on the list.
     */
    public function read(Request $request, string $id)
    {
        // Scoped to the user's own notifications — the id alone proves nothing.
        $notification = $request->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return $url ? redirect($url) : back();
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    /**
     * 'admin', 'employee' or 'vendor', read off the route name the request came
     * in on. Vendor is the fallback because its routes are the ones without a
     * matching prefix (vendor.notifications.index, but also plain /vendor/...).
     */
    private function panel(Request $request): string
    {
        $name = (string) $request->route()?->getName();

        return match (true) {
            str_starts_with($name, 'admin.')    => 'admin',
            str_starts_with($name, 'employee.') => 'employee',
            default                             => 'vendor',
        };
    }
}
