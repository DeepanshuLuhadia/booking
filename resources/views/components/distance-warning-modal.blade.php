<!-- Distance Warning Modal -->
<div id="distanceWarningModal"
    class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
    x-show="showDistanceWarning"
    x-cloak
    x-transition
    @click.self="showDistanceWarning = false; distanceWarning = null">

    <div class="bg-gradient-to-b from-slate-900 to-slate-800 rounded-2xl max-w-md w-full shadow-2xl border border-slate-700/50 overflow-hidden" @click.stop>
        <!-- Header with icon -->
        <div class="bg-gradient-to-r from-amber-500/20 to-orange-500/20 px-6 py-6 border-b border-slate-700/50">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M6.343 3.665c.886-.887 2.318-.887 3.203 0l9.759 9.759c.886.887.886 2.318 0 3.203l-9.759 9.759c-.886.887-2.318.887-3.203 0L3.14 16.168c-.886-.887-.886-2.318 0-3.203L6.343 3.665z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">Location Notice</h3>
                    <p class="text-sm text-slate-400 mt-1">This vendor is far from you</p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-6">
            <p class="text-slate-300 text-center leading-relaxed">
                <span class="font-semibold text-white" x-text="distanceWarning?.vendor_name || 'This vendor'"></span>
                is <span class="font-bold text-amber-400" x-text="distanceWarning?.distance_km || '0'"></span> km away from your current location.
            </p>
            <p class="text-slate-400 text-sm text-center mt-3">
                Are you sure you want to proceed with the booking?
            </p>
        </div>

        <!-- Footer with actions -->
        <div class="bg-slate-800/50 px-6 py-4 border-t border-slate-700/50 flex gap-3">
            <button @click="showDistanceWarning = false; distanceWarning = null"
                class="flex-1 px-4 py-2.5 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 font-medium transition-colors border border-slate-600/50">
                Cancel
            </button>
            <button @click="confirmDistanceWarning()"
                class="flex-1 px-4 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium transition-colors shadow-lg">
                Yes, Continue
            </button>
        </div>
    </div>
</div>
