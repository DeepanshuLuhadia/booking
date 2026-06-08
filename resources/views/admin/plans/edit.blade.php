<x-admin-layout>
    <div class="space-y-10">
        <div class="flex flex-col gap-2">
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">Edit Subscription Plan</h2>
            <p class="text-xs md:text-sm font-medium text-slate-400 uppercase tracking-widest">Modify plan details for {{ $plan->name }}</p>
        </div>

        <form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="space-y-8 m-0">
            @csrf
            @method('PUT')
            <div class="glass-card p-6 sm:p-8 space-y-6">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Plan Name</label>
                    <input type="text" name="name" value="{{ $plan->name }}" required class="w-full glass-input min-h-[2.75rem] rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Price (₹)</label>
                        <input type="number" name="price" value="{{ $plan->price }}" required class="w-full glass-input min-h-[2.75rem] rounded-xl px-4 py-2.5 text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Max Employees</label>
                        <input type="number" name="max_employees" value="{{ $plan->max_employees }}" required class="w-full glass-input min-h-[2.75rem] rounded-xl px-4 py-2.5 text-sm font-semibold">
                    </div>
                </div>

                <div id="features-container" class="space-y-3">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Plan Features</label>
                    @foreach($plan->features as $feature)
                        <div class="flex gap-2">
                            <input type="text" name="features[]" value="{{ $feature }}" class="w-full glass-input min-h-[2.75rem] rounded-xl px-4 py-2.5 text-sm font-semibold">
                            <button type="button" onclick="this.parentElement.remove()" class="btn-outline px-4 min-h-[2.75rem] rounded-xl flex items-center justify-center font-bold text-red-500 hover:bg-red-500 hover:text-white">-</button>
                        </div>
                    @endforeach
                    <div class="flex gap-2 mt-3">
                        <input type="text" name="features[]" class="w-full glass-input min-h-[2.75rem] rounded-xl px-4 py-2.5 text-sm font-semibold" placeholder="Add new feature...">
                        <button type="button" onclick="addFeature()" class="btn-outline px-4 min-h-[2.75rem] rounded-xl flex items-center justify-center font-bold text-lg">+</button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <button type="submit" class="btn-primary py-3 px-8 text-xs font-black uppercase tracking-widest rounded-xl">Update Plan</button>
                <a href="{{ route('admin.dashboard') }}" class="btn-outline py-3 px-8 text-xs font-black uppercase tracking-widest rounded-xl text-center">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function addFeature() {
            const container = document.getElementById('features-container');
            const div = document.createElement('div');
            div.className = 'flex gap-2 mt-3';
            div.innerHTML = `
                <input type="text" name="features[]" class="w-full glass-input min-h-[2.75rem] rounded-xl px-4 py-2.5 text-sm font-semibold" placeholder="Feature">
                <button type="button" onclick="this.parentElement.remove()" class="btn-outline px-4 min-h-[2.75rem] rounded-xl flex items-center justify-center font-bold text-red-500 hover:bg-red-500 hover:text-white">-</button>
            `;
            container.appendChild(div);
        }
    </script>
</x-admin-layout>
