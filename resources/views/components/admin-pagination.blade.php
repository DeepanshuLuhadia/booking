@props(['paginator', 'label' => 'records'])

@if($paginator->hasPages() || $paginator->total() > 0)
    <div class="px-6 py-6 border-t border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pagination-container">
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 whitespace-nowrap">
            Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} {{ $label }}
        </p>

        @if($paginator->hasPages())
            <div class="pagination-links">{{ $paginator->onEachSide(1)->links() }}</div>
        @endif
    </div>

    {{-- Emitted once per page however many tables it carries. --}}
    @once
        <style>
            /* Hand-styled for the same reason as the vendor panel's: the prebuilt
               CSS bundle ships only part of the palette, so the framework's default
               pagination markup renders unstyled against these dark cards. */
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

                /* The framework prints its own "showing x to y" copy in that
                   second block; ours already sits to the left of the links. */
                .pagination-container nav div:last-child > div:first-child { display: none; }
            }

            .pagination-container a,
            .pagination-container span[aria-current="page"] > span,
            .pagination-container span[aria-disabled="true"] > span {
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

            .pagination-container span[aria-disabled="true"] > span {
                background-color: rgba(255, 255, 255, 0.02);
                color: rgba(255, 255, 255, 0.2);
                cursor: not-allowed;
            }

            .pagination-container nav span[aria-current="page"],
            .pagination-container nav span[aria-disabled="true"],
            .pagination-container nav a { margin: 0 0.15rem; }

            .pagination-container svg { width: 1.25rem; height: 1.25rem; }
        </style>
    @endonce
@endif
