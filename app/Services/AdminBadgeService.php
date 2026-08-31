<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\Settlement;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorReview;

/**
 * The "needs an admin" counters behind the badges in the admin panel.
 *
 * One place, for two reasons. The admin layout draws its menu twice — once for
 * the desktop sidebar, once for the mobile sheet — and the dashboard draws the
 * same figures a third time; counting separately in each meant three queries
 * per number and three chances for them to disagree. And every count here is a
 * queue with a matching page: a badge that does not lead anywhere is noise, so
 * each entry carries the route and the filter that shows exactly those rows.
 *
 * Bound as a singleton (see AppServiceProvider), so the queries run once per
 * request no matter how many times the layout asks.
 *
 * Deliberately NOT a count of everything. Bookings, plans and reports have no
 * "unattended" state — a badge on them would be a number that never goes down,
 * which teaches admins to ignore badges.
 */
class AdminBadgeService
{
    /** @var array<string,int>|null */
    private ?array $counts = null;

    private ?int $unread = null;

    /**
     * Every queue waiting on an admin, keyed by the menu entry it belongs to.
     *
     * @return array<string,int>
     */
    public function counts(): array
    {
        return $this->counts ??= [
            // Businesses that have registered and cannot trade until someone
            // approves them. The headline queue: every hour here is a shop
            // sitting on the approval-pending screen.
            'vendors'     => Vendor::where('status', 'pending')->count(),

            // Contact-form enquiries nobody has opened yet.
            'enquiries'   => ContactMessage::unread()->count(),

            // Reviews a shop has flagged, waiting for a keep-or-delete call.
            'reviews'     => VendorReview::where('is_reported', true)->count(),

            // Payouts owed to shops that have not been marked paid.
            'settlements' => Settlement::whereIn('status', ['pending', 'processing'])->count(),
        ];
    }

    public function count(string $key): int
    {
        return $this->counts()[$key] ?? 0;
    }

    /** Everything outstanding, for the single figure on the dashboard. */
    public function total(): int
    {
        return array_sum($this->counts());
    }

    /**
     * Unread notifications for the signed-in admin. Separate from the queues
     * above because it is per-account rather than platform-wide — two admins
     * see the same pending vendors but their own unread lists.
     */
    public function unreadNotifications(): int
    {
        if ($this->unread !== null) {
            return $this->unread;
        }

        $user = auth()->user();

        return $this->unread = $user ? $user->unreadNotifications()->count() : 0;
    }

    /**
     * The menu rows the layout renders, each with the page its badge leads to.
     * Kept here beside the counts so a new queue cannot be added to one and
     * forgotten in the other.
     *
     * @return array<string,array{label:string,route:string,params:array}>
     */
    public function destinations(): array
    {
        return [
            'vendors'     => ['label' => 'Pending Vendors',  'route' => 'admin.vendors.index',     'params' => ['status' => 'pending']],
            'enquiries'   => ['label' => 'New Enquiries',    'route' => 'admin.contacts.index',    'params' => ['status' => 'new']],
            'reviews'     => ['label' => 'Reported Reviews', 'route' => 'admin.reviews.index',     'params' => ['filter' => 'reported']],
            'settlements' => ['label' => 'Pending Payouts',  'route' => 'admin.settlements.index', 'params' => []],
        ];
    }

    /**
     * Every active admin — who a platform-level alert goes to.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int,User>
     */
    public function recipients()
    {
        return User::where('role', 'admin')->where('status', 'active')->get();
    }
}
