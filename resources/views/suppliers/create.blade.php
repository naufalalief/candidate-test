<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('suppliers.index') }}" class="hover:text-brand-800 transition-colors">Suppliers</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">New Supplier</span>
        </nav>
        <h2 class="text-2xl font-bold text-gray-900">Create Supplier</h2>
    </x-slot>

    <div class="max-w-lg">
        <div class="card">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-base font-bold text-gray-900">Supplier Details</h3>
                <p class="text-sm text-gray-500 mt-0.5">Enter information for the new supplier.</p>
            </div>
            <form method="POST" action="{{ route('suppliers.store') }}" class="p-6">
                @csrf
                <div class="mb-5">
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Supplier Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-brand-800 focus:border-brand-800" placeholder="e.g. Stora Enso" />
                    @if ($errors->has('name'))
                        <p class="mt-2 text-sm text-red-600">{{ $errors->first('name') }}</p>
                    @endif
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('suppliers.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Create Supplier</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
