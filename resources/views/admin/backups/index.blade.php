@php
    $formatSize = function (int $bytes): string {
        if ($bytes < 1024) return "{$bytes} B";
        $units = ['KB', 'MB', 'GB'];
        $value = $bytes / 1024;
        foreach ($units as $unit) {
            if ($value < 1024 || $unit === end($units)) {
                return number_format($value, 1) . " {$unit}";
            }
            $value /= 1024;
        }
    };
@endphp

<x-admin-layout>
    <div class="space-y-8">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div class="flex flex-col gap-2">
                <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Database Backups</h2>
                <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">
                    Runs automatically every night &middot; kept for {{ $retentionDays }} days
                </p>
            </div>

            <form method="POST" action="{{ route('admin.backups.store') }}"
                  onsubmit="const btn = this.querySelector('button'); btn.disabled = true; btn.innerText = 'Generating…';">
                @csrf
                <button type="submit" class="btn-primary py-3 px-8 text-[10px] font-black uppercase tracking-widest rounded-xl whitespace-nowrap">
                    Generate Backup Now
                </button>
            </form>
        </div>

        @if($backups->isEmpty())
            <div class="glass-card p-6 md:p-8">
                <p class="text-sm text-slate-400 font-medium">No backups yet. Trigger one above, or wait for tonight's scheduled run.</p>
            </div>
        @else
            {{--
                `glass-card overflow-hidden` clips instead of scrolling, so a
                table inside it needs `table-responsive-wrapper` (app.css) or
                the rightmost column — here, Download/Delete — just vanishes
                off the right edge of a phone screen with no way to reach it.
            --}}
            <div class="glass-card overflow-hidden">
                <div class="table-responsive-wrapper">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/5/50">
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400">File</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden sm:table-cell">Created</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 hidden sm:table-cell">Size</th>
                                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($backups as $backup)
                                <tr class="hover:bg-white/5/30 transition-all">
                                    <td class="p-4 text-sm font-semibold text-white whitespace-nowrap">
                                        {{ $backup['name'] }}
                                        <div class="text-slate-400 font-bold text-[10px] tracking-wider mt-0.5 sm:hidden">
                                            {{ $backup['modified']->format('d M Y, h:i A') }} &middot; {{ $formatSize($backup['size']) }}
                                        </div>
                                    </td>
                                    <td class="p-4 text-sm text-slate-400 whitespace-nowrap hidden sm:table-cell">{{ $backup['modified']->format('d M Y, h:i A') }}</td>
                                    <td class="p-4 text-sm text-slate-400 whitespace-nowrap hidden sm:table-cell">{{ $formatSize($backup['size']) }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.backups.download', $backup['name']) }}"
                                           class="text-[10px] font-black uppercase tracking-widest text-sky-400 hover:text-white transition-colors mr-4">
                                            Download
                                        </a>
                                        <form method="POST" action="{{ route('admin.backups.destroy', $backup['name']) }}"
                                              class="inline"
                                              onsubmit="return confirm('Delete this backup permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-rose-400 transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>
