<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('suppliers.index') }}" class="hover:text-brand-800 transition-colors">Suppliers</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.show', $supplier) }}" class="hover:text-brand-800 transition-colors">{{ $supplier->name }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.layups.show', [$supplier, $layup]) }}" class="hover:text-brand-800 transition-colors">{{ $layup->name }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Edit Layer #{{ $layer->layer_order }}</span>
        </nav>
        <h2 class="text-2xl font-bold text-gray-900">Edit Layer</h2>
    </x-slot>

    <div class="max-w-lg">
        <div class="card">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-base font-bold text-gray-900">Layer Properties</h3>
                <p class="text-sm text-gray-500 mt-0.5">Update properties for layer #{{ $layer->layer_order }}.</p>
            </div>
            <form method="POST" action="{{ route('suppliers.layups.layers.update', [$supplier, $layup, $layer]) }}" class="p-6 space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label for="layer_order" class="block text-sm font-semibold text-gray-700 mb-2">Layer Order</label>
                    <input id="layer_order" name="layer_order" type="number" value="{{ old('layer_order', $layer->layer_order) }}" required min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-brand-800 focus:border-brand-800" />
                    @if ($errors->has('layer_order'))
                        <p class="mt-2 text-sm text-red-600">{{ $errors->first('layer_order') }}</p>
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="thickness" class="block text-sm font-semibold text-gray-700 mb-2">Thickness (mm)</label>
                        <input id="thickness" name="thickness" type="number" step="0.01" value="{{ old('thickness', $layer->thickness) }}" required min="0"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-brand-800 focus:border-brand-800" />
                        @if ($errors->has('thickness'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('thickness') }}</p>
                        @endif
                    </div>
                    <div>
                        <label for="width" class="block text-sm font-semibold text-gray-700 mb-2">Width (mm)</label>
                        <input id="width" name="width" type="number" step="0.01" value="{{ old('width', $layer->width) }}" required min="0"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-brand-800 focus:border-brand-800" />
                        @if ($errors->has('width'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('width') }}</p>
                        @endif
                    </div>
                    <div>
                        <label for="angle" class="block text-sm font-semibold text-gray-700 mb-2">Angle (&deg;)</label>
                        <input id="angle" name="angle" type="number" step="0.01" value="{{ old('angle', $layer->angle) }}" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-brand-800 focus:border-brand-800" />
                        @if ($errors->has('angle'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('angle') }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('suppliers.layups.layers.index', [$supplier, $layup]) }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
