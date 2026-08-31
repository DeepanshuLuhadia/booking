<x-admin-layout>
    <div class="space-y-8 max-w-5xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-2">
                <a href="{{ route('admin.contacts.index') }}" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-white transition-colors">&larr; Back to enquiries</a>
                <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white">{{ $message->subject }}</h2>
            </div>
            <span class="inline-flex self-start px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $message->statusClasses() }}">
                {{ $message->status }}
            </span>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Sender -->
            <div class="lg:col-span-1 space-y-6">
                <div class="glass-card p-6">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-5">Sender</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="text-[9px] font-black uppercase tracking-widest text-slate-600 mb-1">Name</div>
                            <div class="text-white font-bold">{{ $message->name }}</div>
                        </div>
                        <div>
                            <div class="text-[9px] font-black uppercase tracking-widest text-slate-600 mb-1">Email</div>
                            <a href="mailto:{{ $message->email }}" class="text-sky-400 font-bold hover:underline break-all">{{ $message->email }}</a>
                        </div>
                        @if($message->phone)
                            <div>
                                <div class="text-[9px] font-black uppercase tracking-widest text-slate-600 mb-1">Phone</div>
                                <a href="tel:{{ $message->phone }}" class="text-white font-bold hover:text-sky-400">{{ $message->phone }}</a>
                            </div>
                        @endif
                        <div>
                            <div class="text-[9px] font-black uppercase tracking-widest text-slate-600 mb-1">Received</div>
                            <div class="text-slate-300 font-semibold text-sm">{{ $message->created_at->format('d M Y, g:i A') }}</div>
                        </div>
                        @if($message->user)
                            <div>
                                <div class="text-[9px] font-black uppercase tracking-widest text-slate-600 mb-1">Registered Account</div>
                                <div class="text-slate-300 font-semibold text-sm">{{ $message->user->name }} &middot; {{ ucfirst($message->user->role) }}</div>
                            </div>
                        @endif
                        @if($message->ip_address)
                            <div>
                                <div class="text-[9px] font-black uppercase tracking-widest text-slate-600 mb-1">IP Address</div>
                                <div class="text-slate-500 font-mono text-xs">{{ $message->ip_address }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Lifecycle without emailing -->
                <div class="glass-card p-6">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-5">Mark As</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['read' => 'Opened', 'replied' => 'Replied', 'closed' => 'Closed'] as $value => $label)
                            <form method="POST" action="{{ route('admin.contacts.status', $message) }}" class="m-0">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $value }}">
                                <button type="submit" @disabled($message->status === $value)
                                        class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ $message->status === $value ? 'bg-white/5 text-white/30 cursor-default' : 'bg-white/10 text-white/70 hover:bg-white/20' }}">
                                    {{ $label }}
                                </button>
                            </form>
                        @endforeach
                    </div>

                    <form action="{{ route('admin.contacts.destroy', $message) }}" method="POST" onsubmit="return confirm('Delete this enquiry permanently?')" class="mt-6 pt-6 border-t border-white/10">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-3 rounded-xl border border-red-500/25 text-red-400 text-[10px] font-black uppercase tracking-widest hover:bg-red-500 hover:text-white transition-colors">
                            Delete Enquiry
                        </button>
                    </form>
                </div>
            </div>

            <!-- Message + reply -->
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-card p-6 md:p-8">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-5">Their Message</h3>
                    <p class="text-slate-200 leading-relaxed whitespace-pre-wrap">{{ $message->message }}</p>
                </div>

                @if($message->admin_reply)
                    <div class="glass-card p-6 md:p-8 border-emerald-500/20">
                        <div class="flex items-center justify-between gap-4 mb-5">
                            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400">Reply Sent</h3>
                            <span class="text-[10px] font-bold text-slate-500">
                                {{ $message->replied_at?->format('d M Y, g:i A') }}
                                @if($message->repliedBy) &middot; by {{ $message->repliedBy->name }} @endif
                            </span>
                        </div>
                        <p class="text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $message->admin_reply }}</p>
                    </div>
                @endif

                <div class="glass-card p-6 md:p-8">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">
                        {{ $message->admin_reply ? 'Send Another Reply' : 'Reply By Email' }}
                    </h3>
                    <p class="text-xs text-slate-500 font-medium mb-6">This is emailed straight to {{ $message->email }} and recorded here.</p>

                    <form method="POST" action="{{ route('admin.contacts.reply', $message) }}" class="space-y-5">
                        @csrf

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Subject</label>
                            <input type="text" name="subject" required maxlength="150"
                                   value="{{ old('subject', 'Re: ' . $message->subject) }}"
                                   class="w-full h-12 px-4 bg-white/5 border border-white/10 rounded-xl text-sm font-semibold text-white focus:bg-white/10 focus:outline-none">
                            @error('subject')<p class="text-rose-400 text-[10px] font-black uppercase tracking-widest">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Message</label>
                            <textarea name="body" rows="8" required minlength="5" maxlength="5000"
                                      placeholder="Write your reply…"
                                      class="w-full p-4 bg-white/5 border border-white/10 rounded-xl text-sm font-medium text-white leading-relaxed placeholder:text-white/25 focus:bg-white/10 focus:outline-none">{{ old('body') }}</textarea>
                            @error('body')<p class="text-rose-400 text-[10px] font-black uppercase tracking-widest">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="btn-primary py-3 px-8 text-[10px] font-black uppercase tracking-widest rounded-xl">
                            Send Reply
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
