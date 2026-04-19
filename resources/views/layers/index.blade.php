<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('suppliers.index') }}" class="hover:text-brand-800 transition-colors">Suppliers</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.show', $supplier) }}" class="hover:text-brand-800 transition-colors">{{ $supplier->name }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.layups.show', [$supplier, $layup]) }}" class="hover:text-brand-800 transition-colors">{{ $layup->name }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Layers</span>
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-2xl font-bold text-gray-900">Layers</h2>
            <a href="{{ route('suppliers.layups.layers.create', [$supplier, $layup]) }}" class="btn-primary shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Layer
            </a>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-2 text-sm">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <!-- Search Bar -->
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <form method="GET" action="{{ route('suppliers.layups.layers.index', [$supplier, $layup]) }}" class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order, thickness, width, angle..." class="pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg w-full sm:w-80 focus:ring-1 focus:ring-brand-800 focus:border-brand-800 bg-white placeholder-gray-400" />
            </form>
            @if(request('search'))
                <a href="{{ route('suppliers.layups.layers.index', [$supplier, $layup]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear
                </a>
            @endif
        </div>

        @if ($layers->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>
                <h3 class="mt-3 text-sm font-semibold text-gray-900">No layers yet</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by adding a new layer to this layup.</p>
                <a href="{{ route('suppliers.layups.layers.create', [$supplier, $layup]) }}" class="btn-primary mt-4 inline-flex">Add Layer</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="table-header">Order</th>
                            <th class="table-header">Thickness</th>
                            <th class="table-header">Width</th>
                            <th class="table-header">Angle</th>
                            <th class="table-header text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($layers as $layer)
                            <tr class="hover:bg-surface-50 transition-colors">
                                <td class="table-cell">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand-50 text-brand-800 text-xs font-bold">{{ $layer->layer_order }}</span>
                                </td>
                                <td class="table-cell font-medium text-gray-900">{{ $layer->thickness }} mm</td>
                                <td class="table-cell font-medium text-gray-900">{{ $layer->width }} mm</td>
                                <td class="table-cell font-medium text-gray-900">{{ $layer->angle }}&deg;</td>
                                <td class="table-cell text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('suppliers.layups.layers.edit', [$supplier, $layup, $layer]) }}" class="p-2 text-gray-400 hover:text-amber-600 rounded-lg hover:bg-amber-50 transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form id="delete-layer-{{ $layer->id }}" action="{{ route('suppliers.layups.layers.destroy', [$supplier, $layup, $layer]) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <a href="#" onclick="event.preventDefault(); if(confirm('Delete this layer?')) document.getElementById('delete-layer-{{ $layer->id }}').submit();" class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $layers->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
