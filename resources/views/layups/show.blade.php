<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('suppliers.index') }}" class="hover:text-brand-800 transition-colors">Suppliers</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.show', $supplier) }}" class="hover:text-brand-800 transition-colors">{{ $supplier->name }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.layups.index', $supplier) }}" class="hover:text-brand-800 transition-colors">Layups</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">{{ $layup->name }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Layup Specification: {{ $layup->name }}</h1>
                    <span class="badge-green">Active</span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('suppliers.layups.edit', [$supplier, $layup]) }}" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="card mb-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-gray-200">
            <div class="stat-card">
                <div class="stat-label">Supplier</div>
                <div class="stat-value">{{ $supplier->name }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Last Modified</div>
                <div class="stat-value">{{ $layup->updated_at->format('M d, Y') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Thickness</div>
                <div class="text-xl font-bold text-brand-800">{{ $layup->layers->sum('thickness') }}mm</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Layers</div>
                <div class="text-xl font-bold text-brand-800">{{ $layup->layers->count() }} Layers</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="card">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Layer Composition</h2>
                    <a href="{{ route('suppliers.layups.layers.create', [$supplier, $layup]) }}" class="text-sm font-semibold text-brand-800 hover:text-brand-900 transition-colors flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Add Layer
                    </a>
                </div>

                @if ($layup->layers->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <p class="mt-3 text-sm text-gray-500">No layers defined yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/50">
                                <th class="table-header w-8"></th>
                                <th class="table-header">Order</th>
                                <th class="table-header">Thickness</th>
                                <th class="table-header">Width</th>
                                <th class="table-header">Angle</th>
                                <th class="table-header text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($layup->layers->sortBy('layer_order') as $layer)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="table-cell text-gray-300">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/></svg>
                                    </td>
                                    <td class="table-cell font-semibold text-gray-900">{{ $layer->layer_order }}</td>
                                    <td class="table-cell">{{ $layer->thickness }}mm</td>
                                    <td class="table-cell">{{ $layer->width }}mm</td>
                                    <td class="table-cell">
                                        <span class="inline-flex items-center gap-1">
                                            @if ((float)$layer->angle === 0.0 || (float)$layer->angle === 180.0)
                                                <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            @else
                                                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            @endif
                                            {{ $layer->angle }}&deg;
                                        </span>
                                    </td>
                                    <td class="table-cell text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('suppliers.layups.layers.edit', [$supplier, $layup, $layer]) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <form id="delete-layer-{{ $layer->id }}" action="{{ route('suppliers.layups.layers.destroy', [$supplier, $layup, $layer]) }}" method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <a href="#" onclick="event.preventDefault(); if(confirm('Delete this layer?')) document.getElementById('delete-layer-{{ $layer->id }}').submit();" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    <div class="px-4 sm:px-6 py-3 border-t border-gray-200 flex flex-col sm:flex-row justify-between text-xs text-gray-400 gap-1">
                        <span>Showing {{ $layup->layers->count() }} layers</span>
                        <span>Calculated Sum: {{ $layup->layers->sum('thickness') }}mm</span>
                    </div>
                @endif
            </div>
        </div>

        <div>
            <div class="card">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Structure Visualizer</h2>
                    <div class="flex items-center gap-3 text-xs text-gray-400 flex-wrap">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-100 border border-amber-300"></span> Longitudinal (0&deg;)</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-400"></span> Transverse (90&deg;)</span>
                    </div>
                </div>
                <div class="p-6">
                    @if ($layup->layers->isEmpty())
                        <div class="text-center text-sm text-gray-400 py-8">No layers to visualize</div>
                    @else
                        <div class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mb-2 text-center">Top (Outside)</div>
                        <div class="space-y-1.5 max-w-xs mx-auto">
                            @foreach ($layup->layers->sortBy('layer_order') as $layer)
                                @php
                                    $isTransverse = (float)$layer->angle === 90.0;
                                @endphp
                                <div class="relative rounded-lg border {{ $isTransverse ? 'bg-amber-400/80 border-amber-500' : 'bg-amber-100 border-amber-300' }} py-3 px-4 flex items-center justify-between">
                                    <span class="text-sm font-semibold text-amber-900">
                                        L{{ $layer->layer_order }} ({{ $layer->thickness }}mm)
                                    </span>
                                    @if ($isTransverse)
                                        <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mt-2 text-center">Bottom (Inside)</div>
                        <p class="text-center text-[11px] text-gray-400 mt-4">Cross-Laminated Structural Assembly</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
