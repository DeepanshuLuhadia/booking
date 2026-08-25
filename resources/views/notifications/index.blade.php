{{--
    The notification tab, shared by the vendor and employee panels.

    Every push the account was sent is mirrored into the notifications table
    (see NotificationService::sendWebPush), so this page is the replay of what
    the phone may have missed — a switched-off device or a denied permission
    loses nothing.

    Which panel wraps the page and which routes the buttons post to comes from
    the controller ($layout / $routePrefix); the markup itself is identical on
    both panels. Unread rows carry the accent border and dot; a row whose
    stored data carries a `url` is a navigation, so "View" follows it after
    marking it read.
--}}
<x-dynamic-component :component="$layout">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-8 mb-12">
        <div>
            <h1 class="text-4xl font-black italic tracking-tight uppercase text-white">
                Notifi<span class="text-blue-600">cations.</span>
            </h1>
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mt-2 italic">
                Every Alert Sent To This Account &middot; Nothing Lost To A Missed Push
            </p>
        </div>

        <div class="flex items-center gap-4 shrink-0">
            @if($unreadCount > 0)
                <div class="px-6 py-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-center">
                    <p class="text-3xl font-black italic text-blue-400 leading-none">{{ $unreadCount }}</p>
                    <p class="text-[8px] font-black text-blue-300/70 uppercase tracking-widest italic mt-1.5">Unread</p>
                </div>

                <form method="POST" action="{{ route($routePrefix . '.notifications.readAll') }}">
                    @csrf
                    <button type="submit"
                            class="px-6 py-4 rounded-2xl bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest italic text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                        Mark All Read
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($notifications->isEmpty())
        <div class="rounded-[2rem] bg-white/5 border border-white/10 px-8 py-20 text-center">
            <div class="text-4xl opacity-40 mb-4">🔔</div>
            <p class="text-sm font-black uppercase tracking-widest italic text-white">No notifications yet</p>
            <p class="text-[10px] font-black uppercase tracking-widest italic text-slate-400 mt-2">
                New bookings, cancellations and payments will land here
            </p>
        </div>
    @else
        <div class="flex flex-col gap-3">
            @foreach($notifications as $notification)
                @php
                    $data    = $notification->data;
                    $unread  = is_null($notification->read_at);

                    // PushNotice rows carry their pushed title; older rows
                    // (e.g. DirectPaymentDue) only stored a type, so name them
                    // from that instead of showing a bare class string.
                    $title = $data['title'] ?? match($data['type'] ?? null) {
                        'direct_payment_due' => 'Online Payment — Please Check',
                        default              => 'Notification',
                    };
                @endphp

                <div class="flex items-start gap-4 p-5 rounded-2xl border transition-colors
                            {{ $unread ? 'bg-blue-500/[0.07] border-blue-500/25' : 'bg-white/5 border-white/10' }}">

                    <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $unread ? 'bg-blue-400 shadow-[0_0_6px_rgba(96,165,250,0.9)]' : 'bg-white/15' }}"></span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                            <p class="text-[12px] font-black uppercase tracking-widest italic {{ $unread ? 'text-white' : 'text-slate-300' }}">
                                {{ $title }}
                            </p>
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 italic"
                                  title="{{ $notification->created_at->format('d M Y, h:i A') }}">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>
                        @if(!empty($data['message']))
                            <p class="mt-1.5 text-[12px] font-semibold text-slate-400 leading-relaxed">
                                {{ $data['message'] }}
                            </p>
                        @endif
                    </div>

                    @if($unread || !empty($data['url']))
                        <form method="POST" action="{{ route($routePrefix . '.notifications.read', $notification->id) }}" class="shrink-0">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest italic transition-colors
                                           {{ $unread ? 'bg-blue-500/15 text-blue-300 hover:bg-blue-500/25' : 'bg-white/5 text-slate-400 hover:bg-white/10' }}">
                                {{ !empty($data['url']) ? 'View' : 'Mark Read' }}
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        @if($notifications->hasPages())
            <div class="mt-10">
                {{ $notifications->links() }}
            </div>
        @endif
    @endif
</x-dynamic-component>
