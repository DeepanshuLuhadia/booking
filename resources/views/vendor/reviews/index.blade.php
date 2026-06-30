<x-vendor-layout>
    <div class="space-y-10">
        @if(session('success'))
            <div class="bg-emerald-500 text-white p-6 rounded-[2rem] text-xs font-black uppercase tracking-widest italic shadow-xl shadow-emerald-500/10">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col gap-2">
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Reviews &amp; Ratings</h2>
            <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">What your clients are saying about you</p>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="glass-card p-6 rounded-[2rem] flex flex-col gap-1">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Average Rating</span>
                <div class="flex items-end gap-2">
                    <span class="text-4xl font-black text-white italic tracking-tighter">{{ $stats['average'] > 0 ? number_format($stats['average'], 1) : '—' }}</span>
                    <div class="flex gap-0.5 mb-1.5">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= round($stats['average']) ? 'text-amber-400' : 'text-white/15' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="glass-card p-6 rounded-[2rem] flex flex-col gap-1">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Total Reviews</span>
                <span class="text-4xl font-black text-white italic tracking-tighter">{{ $stats['total'] }}</span>
            </div>
            <div class="glass-card p-6 rounded-[2rem] flex flex-col gap-1">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Reported by You</span>
                <span class="text-4xl font-black {{ $stats['reported'] > 0 ? 'text-amber-400' : 'text-white' }} italic tracking-tighter">{{ $stats['reported'] }}</span>
            </div>
        </div>

        <!-- Reviews list -->
        <div class="space-y-4">
            @forelse($reviews as $review)
                <div class="glass-card p-6 rounded-[2rem] flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex items-start gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white text-lg font-black italic shrink-0">
                            {{ strtoupper(substr($review->reviewer_name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-base font-black text-white italic">{{ $review->reviewer_name }}</h4>
                                @if($review->is_verified)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-sky-500/15 text-sky-400 border border-sky-500/20" title="{{ $review->reviewer_email }}">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.42 0l-3.5-3.5a1 1 0 011.42-1.42l2.79 2.79 6.79-6.79a1 1 0 011.42 0z" clip-rule="evenodd"/></svg>
                                        Verified
                                    </span>
                                @endif
                                @if($review->is_reported)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-amber-500/15 text-amber-400 border border-amber-500/20">Reported</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-white/15' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @endfor
                                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest italic ml-2">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if($review->comment)
                                <p class="text-slate-400 text-sm font-medium italic leading-relaxed mt-3">{{ $review->comment }}</p>
                            @endif
                            @if(!empty($review->images))
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach($review->images as $img)
                                        <a href="{{ asset('storage/' . $img) }}" target="_blank" rel="noopener">
                                            <img src="{{ asset('storage/' . $img) }}" loading="lazy" class="w-16 h-16 object-cover rounded-xl border border-white/10 hover:scale-105 transition-transform">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                            @if($review->is_reported && $review->report_reason)
                                <p class="text-amber-400/70 text-[11px] font-bold italic mt-2">Your report: "{{ $review->report_reason }}"</p>
                            @endif
                        </div>
                    </div>
                    <div class="shrink-0 self-end sm:self-start">
                        @if($review->is_reported)
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-amber-400/70 italic">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Awaiting Review
                            </span>
                        @else
                            <button type="button"
                                onclick="openReportModal('{{ route('vendor.reviews.report', $review) }}', @js($review->reviewer_name))"
                                class="btn-outline py-2 px-4 text-[10px] font-black uppercase tracking-widest rounded-xl flex items-center gap-2 text-rose-400 border-rose-500/20 hover:bg-rose-500 hover:text-white">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 2H21l-3 6 3 6h-8.5l-1-2H5a2 2 0 00-2 2z"/></svg>
                                Report
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-20 text-center border-2 border-dashed border-white/5 rounded-[2.5rem] opacity-40 italic">
                    <p class="font-black uppercase tracking-widest text-white text-sm">No Reviews Yet</p>
                    <p class="text-slate-400 text-xs mt-2">Client reviews will appear here once customers rate your establishment.</p>
                </div>
            @endforelse
        </div>

        @if($reviews->hasPages())
            <div class="pt-4">{{ $reviews->links() }}</div>
        @endif
    </div>

    <!-- REPORT MODAL -->
    <div id="reportModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-[9999] p-4">
        <div class="glass-card p-8 max-w-md w-full shadow-2xl rounded-[2rem]">
            <div class="w-14 h-14 rounded-2xl bg-rose-500/15 border border-rose-500/20 flex items-center justify-center mb-5">
                <svg class="w-7 h-7 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 2H21l-3 6 3 6h-8.5l-1-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <h3 class="text-2xl font-black text-white mb-1 italic tracking-tighter">Report Review</h3>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-6">Flagging review by <span id="reportReviewerName" class="text-white"></span> for admin moderation</p>

            <form id="reportForm" method="POST" class="space-y-6 m-0">
                @csrf
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Reason <span class="normal-case text-slate-500">(optional)</span></label>
                    <textarea name="report_reason" rows="3" maxlength="500" placeholder="Why should this review be removed? (spam, abusive, fake, etc.)"
                        class="w-full glass-input rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none resize-none"></textarea>
                </div>
                <div class="flex gap-4">
                    <button type="submit" class="flex-grow py-3 text-xs font-black uppercase tracking-widest rounded-xl bg-rose-500 text-white hover:bg-rose-600 transition-colors">
                        Submit Report
                    </button>
                    <button type="button" onclick="closeReportModal()" class="btn-outline px-6 py-3 text-xs font-black uppercase tracking-widest rounded-xl">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReportModal(action, reviewerName) {
            const modal = document.getElementById('reportModal');
            document.getElementById('reportForm').action = action;
            document.getElementById('reportReviewerName').textContent = reviewerName;
            modal.classList.remove('hidden');
        }
        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeReportModal(); });
    </script>
</x-vendor-layout>
