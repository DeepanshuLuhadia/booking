<x-admin-layout>
    <div class="space-y-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Reviews Moderation</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Manage every review and resolve vendor reports</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2">
            @foreach(['all' => 'All Reviews', 'reported' => 'Reported'] as $key => $label)
                <a href="{{ route('admin.reviews.index', ['filter' => $key]) }}"
                   class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ ($filter ?? 'all') === $key ? 'bg-white text-black' : 'bg-white/10 text-white/60 hover:bg-white/20' }}">
                    {{ $label }}
                    @if($key === 'reported' && ($reportedCount ?? 0) > 0)
                        <span class="ml-1 bg-amber-500 text-white text-[9px] rounded-full px-1.5 py-0.5">{{ $reportedCount }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="glass-card overflow-hidden">
            <div class="table-responsive-wrapper">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/5/50">
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Vendor</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Reviewer</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Rating</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden md:table-cell">Comment</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                            <tr class="border-b border-white/10 hover:bg-white/5/30 transition-all {{ $review->is_reported ? 'bg-amber-500/5' : '' }}">
                                <td class="p-4">
                                    <div class="font-black text-white text-sm">{{ $review->vendor?->business_name ?? '—' }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 mt-0.5">{{ $review->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="p-4 text-slate-300 font-semibold text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ $review->reviewer_name }}</span>
                                        @if($review->is_verified)
                                            <svg class="w-3.5 h-3.5 text-sky-400 shrink-0" fill="currentColor" viewBox="0 0 20 20" title="Verified via Google"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.42 0l-3.5-3.5a1 1 0 011.42-1.42l2.79 2.79 6.79-6.79a1 1 0 011.42 0z" clip-rule="evenodd"/></svg>
                                        @endif
                                    </div>
                                    @if($review->is_verified && $review->reviewer_email)
                                        <div class="text-[10px] font-medium text-slate-500 mt-0.5">{{ $review->reviewer_email }}</div>
                                    @else
                                        <div class="text-[10px] font-bold text-slate-600 mt-0.5 uppercase tracking-widest">Anonymous</div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-white/15' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        @endfor
                                    </div>
                                </td>
                                <td class="p-4 hidden md:table-cell max-w-xs">
                                    <p class="text-slate-400 text-xs italic truncate">{{ $review->comment ?: '—' }}</p>
                                    @if(!empty($review->images))
                                        <span class="inline-flex items-center gap-1 mt-1 text-[9px] font-black uppercase tracking-widest text-sky-400">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ count($review->images) }} photo{{ count($review->images) > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($review->is_reported)
                                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-amber-500/15 text-amber-400 border border-amber-500/20">Reported</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-500/15 text-emerald-400 border border-emerald-500/20">Published</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        @if($review->is_reported)
                                            <button type="button"
                                                onclick="openReportModal(@js([
                                                    'vendor' => $review->vendor?->business_name,
                                                    'reviewer' => $review->reviewer_name,
                                                    'verified' => $review->is_verified,
                                                    'email' => $review->reviewer_email,
                                                    'rating' => $review->rating,
                                                    'comment' => $review->comment,
                                                    'reason' => $review->report_reason,
                                                    'images' => collect($review->images ?? [])->map(fn ($p) => asset('storage/' . $p))->all(),
                                                    'deleteAction' => route('admin.reviews.destroy', $review),
                                                    'unreportAction' => route('admin.reviews.unreport', $review),
                                                ]))"
                                                class="btn-primary py-2 px-3 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                                View Report
                                            </button>
                                        @endif
                                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Delete this review permanently?')" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 border border-red-500/20 text-red-500 hover:text-white rounded-lg hover:bg-red-500 transition-colors duration-150 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center text-slate-500 font-black uppercase tracking-widest text-xs italic">
                                    No reviews found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-admin-pagination :paginator="$reviews" label="reviews" />
        </div>
    </div>

    <!-- REPORT DETAIL MODAL -->
    <div id="reportModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-[9999] p-4">
        <div class="glass-card p-8 max-w-lg w-full shadow-2xl rounded-[2rem]">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 2H21l-3 6 3 6h-8.5l-1-2H5a2 2 0 00-2 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white italic tracking-tighter leading-none">Reported Review</h3>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mt-1">Flagged by <span id="rmVendor" class="text-white"></span></p>
                </div>
            </div>

            <div class="space-y-4 mb-8">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-black text-white italic" id="rmReviewer"></span>
                        <div class="flex gap-0.5" id="rmStars"></div>
                    </div>
                    <div id="rmIdentity" class="text-[10px] font-bold mb-2"></div>
                    <p class="text-slate-400 text-sm italic" id="rmComment"></p>
                    <div id="rmImages" class="flex flex-wrap gap-2 mt-3"></div>
                </div>
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-5">
                    <span class="block text-[10px] font-black uppercase tracking-widest text-amber-400 mb-1">Vendor's Report Reason</span>
                    <p class="text-amber-100/80 text-sm italic" id="rmReason"></p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <form id="rmUnreportForm" method="POST" class="flex-grow m-0">
                    @csrf
                    <button type="submit" class="w-full py-3 text-xs font-black uppercase tracking-widest rounded-xl bg-emerald-500 text-white hover:bg-emerald-600 transition-colors">
                        Keep &amp; Unreport
                    </button>
                </form>
                <form id="rmDeleteForm" method="POST" class="flex-grow m-0" onsubmit="return confirm('Delete this review permanently?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 text-xs font-black uppercase tracking-widest rounded-xl bg-rose-500 text-white hover:bg-rose-600 transition-colors">
                        Delete Review
                    </button>
                </form>
            </div>
            <button type="button" onclick="closeReportModal()" class="mt-6 w-full text-[9px] font-black uppercase tracking-widest text-white/30 hover:text-white transition-colors">Close</button>
        </div>
    </div>

    <script>
        function openReportModal(data) {
            document.getElementById('rmVendor').textContent = data.vendor || '—';
            document.getElementById('rmReviewer').textContent = data.reviewer || '—';
            document.getElementById('rmIdentity').innerHTML = data.verified
                ? `<span class="text-sky-400 uppercase tracking-widest">✓ Verified · ${data.email || ''}</span>`
                : `<span class="text-slate-500 uppercase tracking-widest">Anonymous reviewer</span>`;
            document.getElementById('rmComment').textContent = data.comment || 'No written comment.';
            document.getElementById('rmReason').textContent = data.reason || 'No reason provided.';

            let stars = '';
            for (let i = 1; i <= 5; i++) {
                const cls = i <= data.rating ? 'text-amber-400' : 'text-white/15';
                stars += `<svg class="w-4 h-4 ${cls}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>`;
            }
            document.getElementById('rmStars').innerHTML = stars;

            const imgWrap = document.getElementById('rmImages');
            imgWrap.innerHTML = (data.images || []).map(src =>
                `<a href="${src}" target="_blank" rel="noopener"><img src="${src}" class="w-20 h-20 object-cover rounded-xl border border-white/10 hover:scale-105 transition-transform"></a>`
            ).join('');

            document.getElementById('rmDeleteForm').action = data.deleteAction;
            document.getElementById('rmUnreportForm').action = data.unreportAction;
            document.getElementById('reportModal').classList.remove('hidden');
        }
        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeReportModal(); });
    </script>
</x-admin-layout>
