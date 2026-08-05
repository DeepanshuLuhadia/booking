@php
    $status = $status ?? ($vendor ? $vendor->status : 'pending');
    $isEmployee = $isEmployee ?? (auth()->check() && auth()->user()->isEmployee());
    $bizName = $vendor && $vendor->business_name ? $vendor->business_name : null;

    if ($status === 'rejected') {
        $pageTitle = 'Approval Rejected';
        $badgeClass = 'bg-red-500/10 border-red-500/30 text-red-400';
        $badgeIcon = '<svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
        $badgeText = 'Approval Rejected';
        $titleGradient = 'linear-gradient(135deg,#ef4444,#f87171)';
        $mainTitle = 'Approval <span style="color:transparent; background:' . $titleGradient . '; -webkit-background-clip:text; background-clip:text;">Rejected.</span>';
        
        if ($isEmployee) {
            $description = "Your employer's business account" . ($bizName ? " ({$bizName})" : '') . " application has been rejected by administration. Staff services are currently unavailable. Please contact your manager or support team below for assistance.";
        } else {
            $description = "Your vendor account application" . ($bizName ? " for {$bizName}" : '') . " has been rejected by administration. Please contact our support team below if you believe this is an error or need further information.";
        }
    } elseif ($status === 'suspended') {
        $pageTitle = 'Account Suspended';
        $badgeClass = 'bg-amber-500/10 border-amber-500/30 text-amber-400';
        $badgeIcon = '<svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>';
        $badgeText = 'Account Suspended';
        $titleGradient = 'linear-gradient(135deg,#f97316,#ef4444)';
        $mainTitle = 'Account <span style="color:transparent; background:' . $titleGradient . '; -webkit-background-clip:text; background-clip:text;">Suspended.</span>';
        
        if ($isEmployee) {
            $description = "Your employer's business account" . ($bizName ? " ({$bizName})" : '') . " has been suspended by administration. Staff dashboard access is currently disabled. Please contact your manager or support team below.";
        } else {
            $description = "Your vendor account" . ($bizName ? " ({$bizName})" : '') . " has been suspended by administration. Access to your business hub and booking services is currently disabled. Please contact admin below for assistance.";
        }
    } else {
        // pending
        $pageTitle = 'Approval Pending';
        $badgeClass = 'bg-white/10 border-white/20 text-white/80';
        $badgeIcon = '<svg class="w-3.5 h-3.5 text-orange-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        $badgeText = 'Awaiting Admin Confirmation';
        $titleGradient = 'linear-gradient(135deg,#ff8c42,#ffab40)';
        $mainTitle = 'Approval <span style="color:transparent; background:' . $titleGradient . '; -webkit-background-clip:text; background-clip:text;">Pending.</span>';
        
        if ($isEmployee) {
            $description = "Your employer's business account" . ($bizName ? " ({$bizName})" : '') . " is currently pending admin confirmation. Once approved, you will gain access to your staff dashboard.";
        } else {
            $description = "Thanks for registering" . ($bizName ? ", {$bizName}" : '') . ". Your account is currently under review — once an admin approves it, you'll unlock your business hub.";
        }
    }
@endphp

<x-app-layout :page-title="$pageTitle . ' | Appointment Platform'">
    <div class="relative min-h-[85vh] md:min-h-[90vh] flex items-center justify-center pt-24 sm:pt-28 md:pt-32 pb-12 sm:pb-16 md:pb-24 px-3 sm:px-6 overflow-hidden" style="background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%);">
        <!-- Glowing Orbs -->
        <div style="position:absolute; top:0; left:50%; transform:translateX(-50%); width:100%; max-width:500px; height:500px; background:rgba(255,109,0,.08); border-radius:50%; filter:blur(120px); pointer-events:none;"></div>
        <div style="position:absolute; bottom:0; right:10%; width:100%; max-width:600px; height:600px; background:rgba(255,109,0,.04); border-radius:50%; filter:blur(150px); pointer-events:none;"></div>
        <!-- Subtle Institutional Pattern -->
        <div class="absolute inset-0 z-0 bg-dot-pattern opacity-30"></div>

        <div class="relative z-10 w-full max-w-xl animate-reveal">
            <div class="text-center mb-6 sm:mb-8 md:mb-12">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 border rounded-full text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-5 sm:mb-8 max-w-full {{ $badgeClass }}">
                    {!! $badgeIcon !!}
                    <span class="truncate">{{ $badgeText }}</span>
                </div>
                <h1 class="text-2xl sm:text-4xl md:text-5xl lg:text-[3.5rem] font-black text-white mb-4 sm:mb-6 tracking-tight leading-tight sm:leading-[1.1] italic break-words">
                    {!! $mainTitle !!}
                </h1>
                <p class="text-sm sm:text-base md:text-lg font-medium text-white/80 max-w-md mx-auto italic leading-relaxed px-2 sm:px-4 break-words">
                    {{ $description }}
                </p>
            </div>

            <div class="glass-card overflow-hidden shadow-2xl rounded-2xl sm:rounded-3xl border border-white/10">
                <div class="p-4 sm:p-6 md:p-8 space-y-5 sm:space-y-6 md:space-y-8">

                    <div class="text-center">
                        <p class="text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] text-white/50 leading-loose">
                            Need help? Contact Admin
                        </p>
                    </div>

                    <!-- Contact admin: two ways to reach us -->
                    <div class="space-y-3.5 sm:space-y-4">
                        @if($adminEmail)
                        <a href="mailto:{{ $adminEmail }}?subject={{ urlencode($pageTitle . ' Inquiry') }}"
                           class="w-full flex items-center gap-3 sm:gap-4 p-3.5 sm:p-4 md:p-5 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/25 rounded-xl sm:rounded-2xl transition-all group min-w-0">
                            <span class="w-10 h-10 sm:w-12 sm:h-12 shrink-0 bg-white/10 text-orange-400 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <span class="flex-1 min-w-0 text-left">
                                <span class="block text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-white/40 italic">Email Us</span>
                                <span class="block text-xs sm:text-sm font-bold text-white truncate break-all sm:break-normal" title="{{ $adminEmail }}">{{ $adminEmail }}</span>
                            </span>
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white/30 transition-transform group-hover:translate-x-1 shrink-0 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        @endif

                        @if($adminPhone)
                        <a href="tel:{{ $adminPhone }}"
                           class="w-full flex items-center gap-3 sm:gap-4 p-3.5 sm:p-4 md:p-5 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/25 rounded-xl sm:rounded-2xl transition-all group min-w-0">
                            <span class="w-10 h-10 sm:w-12 sm:h-12 shrink-0 bg-white/10 text-orange-400 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </span>
                            <span class="flex-1 min-w-0 text-left">
                                <span class="block text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-white/40 italic">Call Us</span>
                                <span class="block text-xs sm:text-sm font-bold text-white truncate" title="{{ $adminPhone }}">{{ $adminPhone }}</span>
                            </span>
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white/30 transition-transform group-hover:translate-x-1 shrink-0 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        @endif
                    </div>

                    <div class="pt-6 sm:pt-8 text-center border-t border-white/10">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] text-white/40 hover:text-white transition-colors italic">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
