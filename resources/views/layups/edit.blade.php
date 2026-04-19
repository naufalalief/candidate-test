<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('suppliers.index') }}" class="hover:text-brand-800 transition-colors">Suppliers</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.show', $supplier) }}" class="hover:text-brand-800 transition-colors">{{ $supplier->name }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.layups.show', [$supplier, $layup]) }}" class="hover:text-brand-800 transition-colors">{{ $layup->name }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Edit</span>
        </nav>
        <h2 class="text-2xl font-bold text-gray-900">Edit Layup</h2>
    </x-slot>

    <div class="max-w-lg">
        <div class="card">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-base font-bold text-gray-900">Layup Details</h3>
                <p class="text-sm text-gray-500 mt-0.5">Update the layup information below.</p>
            </div>
            <form method="POST" action="{{ route('suppliers.layups.update', [$supplier, $layup]) }}" class="p-6">
                @csrf
                @method('PUT')
                <div class="mb-5">
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Layup Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $layup->name) }}" required autofocus
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-brand-800 focus:border-brand-800" />
                    @if ($errors->has('name'))
                        <p class="mt-2 text-sm text-red-600">{{ $errors->first('name') }}</p>
                    @endif
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('suppliers.layups.show', [$supplier, $layup]) }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
