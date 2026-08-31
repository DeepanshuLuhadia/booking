@props(['paginator', 'label' => 'records'])

@if($paginator->hasPages() || $paginator->total() > 0)
    <div class="px-6 py-6 border-t border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pagination-container">
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 whitespace-nowrap">
            Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} {{ $label }}
        </p>

        @if($paginator->hasPages())
            @php
                $previousUrl = $paginator->previousPageUrl();
                $nextUrl = $paginator->nextPageUrl();
            @endphp
            <nav role="navigation" aria-label="Pagination Navigation" class="pagination-links">
                <div>
                    @if($previousUrl)
                        <a href="{{ $previousUrl }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif
                    <div></div>
                    @if($nextUrl)
                        <a href="{{ $nextUrl }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </div>
                <div class="hidden sm:flex items-center gap-1">
                    @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                        @if($page == $paginator->currentPage())
                            <span aria-current="page">
                                <span class="px-3 py-2">{{ $page }}</span>
                            </span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-2">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>
            </nav>
        @endif
    </div>

    {{-- Emitted once per page however many tables it carries. --}}
    @once
        <style>
            .pagination-container nav [role="navigation"] {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .pagination-container nav div:first-child {
                display: flex;
                flex: 1 1 0%;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
            }

            .pagination-container nav div:last-child { display: none; }

            @media (min-width: 640px) {
                .pagination-container nav div:first-child { display: none; }

                .pagination-container nav div:last-child {
                    display: flex;
                    flex: 1 1 0%;
                    align-items: center;
                    justify-content: flex-end;
                }
            }

            .pagination-container a,
            .pagination-container span[aria-current="page"] > span {
                padding: 0.5rem 1rem;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 0.75rem;
                font-size: 10px;
                font-weight: 900;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1);
            }

            .pagination-container a {
                background-color: rgba(255, 255, 255, 0.05);
                color: rgba(255, 255, 255, 0.7);
                text-decoration: none;
            }

            .pagination-container a:hover {
                background-color: rgba(255, 255, 255, 0.15);
                color: #ffffff;
                border-color: rgba(255, 255, 255, 0.3);
            }

            .pagination-container span[aria-current="page"] > span {
                background-color: #2563EB;
                color: #ffffff;
                border-color: #3b82f6;
            }

            .pagination-container nav span[aria-current="page"],
            .pagination-container nav a { margin: 0 0.15rem; }

            .pagination-container svg { width: 1.25rem; height: 1.25rem; }
        </style>
    @endonce
@endif
