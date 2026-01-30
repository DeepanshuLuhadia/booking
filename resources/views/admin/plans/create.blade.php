<x-admin-layout>
    <div class="space-y-8">
        <div class="mb-8">
            <h2 class="text-3xl font-bold">Add New Subscription Plan</h2>
            <p class="text-gray-400">Create a new plan for vendors</p>
        </div>

        <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="glass-card p-8 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Plan Name</label>
                    <input type="text" name="name" required class="w-full glass-input" placeholder="e.g. Enterprise">
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Price (₹)</label>
                        <input type="number" name="price" required class="w-full glass-input" placeholder="4999">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Max Employees</label>
                        <input type="number" name="max_employees" required class="w-full glass-input" placeholder="20">
                    </div>
                </div>

                <div id="features-container" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-400 mb-2">Plan Features</label>
                    <div class="flex gap-2">
                        <input type="text" name="features[]" class="w-full glass-input" placeholder="e.g. 24/7 Support">
                        <button type="button" onclick="addFeature()" class="btn-outline px-4">+</button>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn-primary px-8">Create Plan</button>
                <a href="{{ route('admin.dashboard') }}" class="btn-outline px-8">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function addFeature() {
            const container = document.getElementById('features-container');
            const div = document.createElement('div');
            div.className = 'flex gap-2 mt-2';
            div.innerHTML = `
                <input type="text" name="features[]" class="w-full glass-input" placeholder="Feature">
                <button type="button" onclick="this.parentElement.remove()" class="btn-outline px-4 text-red-400">-</button>
            `;
            container.appendChild(div);
        }
    </script>
</x-admin-layout>
