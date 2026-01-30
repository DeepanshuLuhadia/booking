<x-app-layout>
    <!-- Modern 2026 Hero -->
    <div class="relative py-32 mb-16 overflow-hidden rounded-[4rem] bg-white shadow-[0_40px_100px_-20px_rgba(37,99,235,0.15)] border border-blue-50">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 via-white to-sky-50/30"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-sky-300/10 rounded-full blur-[100px]"></div>
        
        <div class="relative z-10 max-w-5xl mx-auto text-center px-6">
            <h1 class="text-7xl md:text-9xl font-black mb-10 tracking-tight text-slate-900 leading-[0.9]">
                Find Your <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-sky-500 bg-clip-text text-transparent">Vibe.</span> <br/>
                Book in <span class="italic font-light">Seconds.</span>
            </h1>
            
            <p class="text-xl text-slate-500 mb-14 max-w-2xl mx-auto font-medium leading-relaxed">
                Connect with the world's finest professionals through our production-ready marketplace built for 2026.
            </p>
            
            <form action="{{ route('home') }}" method="GET" class="relative max-w-4xl mx-auto">
                <div class="flex flex-col md:flex-row gap-4 bg-white p-3 rounded-[3rem] shadow-2xl shadow-blue-200/40 border border-slate-100">
                    <div class="flex-grow relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            class="w-full h-16 pl-14 pr-6 bg-transparent text-slate-800 text-xl rounded-2xl border-none focus:ring-0 placeholder:text-slate-300" 
                            placeholder="What are you looking for?">
                        <div class="absolute left-6 top-1/2 -translate-y-1/2 text-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <button type="submit" class="h-16 px-12 bg-blue-600 text-white rounded-[2.2rem] font-black text-lg transition-all hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-200 active:scale-95 flex items-center justify-center gap-3">
                        <span>SEARCH NOW</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-12">
        <div class="flex flex-col md:flex-row items-end justify-between mb-16 gap-8">
            <div class="space-y-2">
                <span class="text-blue-600 font-black text-xs uppercase tracking-[0.3em]">Verified Partners</span>
                <h2 class="text-5xl font-black tracking-tight text-slate-900">
                    @if(request('search'))
                        Results for "<span class="text-blue-600">{{ request('search') }}</span>"
                    @else
                        The Elite Selection
                    @endif
                </h2>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="flex bg-slate-100 p-1.5 rounded-2xl border border-slate-200 shadow-inner">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'rating']) }}" class="px-6 py-3 rounded-xl text-xs font-black tracking-widest uppercase transition-all {{ request('sort') == 'rating' ? 'bg-white text-blue-600 shadow-md' : 'text-slate-400 hover:text-slate-600' }}">Top Rated</a>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" class="px-6 py-3 rounded-xl text-xs font-black tracking-widest uppercase transition-all {{ request('sort') == 'newest' ? 'bg-white text-blue-600 shadow-md' : 'text-slate-400 hover:text-slate-600' }}">Fresh</a>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'distance']) }}" class="px-6 py-3 rounded-xl text-xs font-black tracking-widest uppercase transition-all {{ request('sort') == 'distance' ? 'bg-white text-blue-600 shadow-md' : 'text-slate-400 hover:text-slate-600' }}">Distance</a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            @forelse($vendors as $vendor)
                <div class="group relative bg-white rounded-[3.5rem] border border-slate-100 hover:border-blue-200 shadow-[0_20px_50px_-10px_rgba(0,0,0,0.05)] hover:shadow-[0_40px_80px_-20px_rgba(37,99,235,0.15)] transition-all duration-700 cursor-pointer overflow-hidden isolate" 
                     onclick="window.location='/vendors/{{ $vendor->slug }}'">
                    
                    <!-- Shop Image -->
                    <div class="h-80 relative overflow-hidden m-4 rounded-[2.5rem]">
                        @if($vendor->shop_photo)
                            <img src="{{ asset('storage/' . $vendor->shop_photo) }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-slate-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Badges -->
                        <div class="absolute top-6 left-6 flex flex-col gap-2">
                            @php $isAvailable = $vendor->hasAvailableSlotsToday(); @endphp
                            <span class="px-4 py-2 {{ $isAvailable ? 'bg-emerald-500/90' : 'bg-rose-500/90' }} backdrop-blur-md text-white text-[10px] uppercase font-black rounded-xl shadow-lg tracking-widest">
                                {{ $isAvailable ? '● ACTIVE' : '○ CLOSED' }}
                            </span>
                        </div>
                        
                        <!-- Rating -->
                        <div class="absolute bottom-6 right-6 bg-white/95 backdrop-blur-md px-4 py-2 rounded-2xl flex items-center gap-2 shadow-2xl border border-white/20">
                            <span class="text-amber-500 font-bold text-xl">★</span>
                            <span class="text-lg font-black text-slate-900">4.9</span>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-10 flex flex-col pt-4">
                        <div class="mb-6">
                            <h3 class="text-3xl font-black text-slate-900 mb-2 transition-colors group-hover:text-blue-600 tracking-tight">{{ $vendor->business_name }}</h3>
                            <p class="text-slate-400 font-bold text-sm flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                {{ Str::limit($vendor->address ?: 'Downtown Mumbai District', 30) }}
                            </p>
                        </div>
                        
                        <div class="mt-auto flex items-center justify-between py-6 border-t border-slate-50">
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase font-black tracking-[0.2em] text-slate-400 mb-1">Experience starts from</span>
                                <span class="text-3xl font-black text-slate-900 leading-none">₹{{ number_format($vendor->service_fee) }}</span>
                            </div>
                            <div class="w-16 h-16 bg-blue-600 text-white rounded-[1.5rem] flex items-center justify-center shadow-xl shadow-blue-200 group-hover:scale-110 transition-transform duration-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-32 text-center bg-slate-50 rounded-[4rem] border-4 border-dashed border-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto mb-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-4xl font-black text-slate-900 mb-4">No Matches Found</h3>
                    <p class="text-slate-500 text-xl font-medium">Try adjusting your search criteria or explore other categories.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
