<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('suppliers.index') }}" class="hover:text-brand-800 transition-colors">Suppliers</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">{{ $supplier->name }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h1>
                <span class="badge-green">Active Partner</span>
            </div>
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Supplier
            </a>
        </div>
        <p class="mt-1 text-sm text-gray-400 font-mono">ID: SUP-{{ str_pad($supplier->id, 3, '0', STR_PAD_LEFT) }}</p>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-gray-200">
            <div class="stat-card">
                <div class="stat-label">Total Layups</div>
                <div class="text-xl font-bold text-brand-800">{{ $supplier->layups->count() }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Layers</div>
                <div class="text-xl font-bold text-brand-800">{{ $supplier->layups->sum(fn($l) => $l->layers->count()) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Created</div>
                <div class="stat-value">{{ $supplier->created_at->format('M d, Y') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Last Updated</div>
                <div class="stat-value">{{ $supplier->updated_at->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-lg font-semibold text-gray-900">Associated Layups</h2>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('suppliers.import', $supplier) }}" class="btn-secondary text-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="btn-secondary text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export
                        <svg class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-44 bg-white rounded-lg border border-gray-200 shadow-lg z-20 py-1" style="display: none;">
                        <a href="{{ route('suppliers.export', [$supplier, 'format' => 'json']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <span class="w-6 text-center text-xs font-bold text-brand-800">{}</span> JSON
                        </a>
                        <a href="{{ route('suppliers.export', [$supplier, 'format' => 'csv']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <span class="w-6 text-center text-xs font-bold text-green-600">csv</span> CSV
                        </a>
                        <a href="{{ route('suppliers.export', [$supplier, 'format' => 'excel']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <span class="w-6 text-center text-xs font-bold text-emerald-700">xls</span> Excel
                        </a>
                    </div>
                </div>
                <a href="{{ route('suppliers.layups.create', $supplier) }}" class="btn-primary text-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Layup
                </a>
            </div>
        </div>

        @if ($supplier->layups->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
                <p class="mt-3 text-sm text-gray-500">No layups yet. Add your first layup to get started.</p>
            </div>
        @else
            <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50">
                        <th class="table-header">Layup ID</th>
                        <th class="table-header">Name</th>
                        <th class="table-header">Layers</th>
                        <th class="table-header">Total Thickness</th>
                        <th class="table-header">Created</th>
                        <th class="table-header text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($supplier->layups as $layup)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="table-cell font-mono text-xs text-gray-400">L-{{ str_pad($layup->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="table-cell">
                                <a href="{{ route('suppliers.layups.show', [$supplier, $layup]) }}" class="font-semibold text-gray-900 hover:text-brand-800 transition-colors">{{ $layup->name }}</a>
                            </td>
                            <td class="table-cell">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-brand-50 text-brand-800 text-xs font-bold">{{ $layup->layers->count() }}</span>
                            </td>
                            <td class="table-cell text-gray-600">
                                {{ $layup->layers->sum('thickness') }}mm
                            </td>
                            <td class="table-cell text-gray-500 text-xs">
                                {{ $layup->created_at->format('M d, Y') }}
                            </td>
                            <td class="table-cell text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('suppliers.layups.show', [$supplier, $layup]) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="View">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('suppliers.layups.edit', [$supplier, $layup]) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form id="delete-layup-{{ $layup->id }}" action="{{ route('suppliers.layups.destroy', [$supplier, $layup]) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <a href="#" onclick="event.preventDefault(); if(confirm('Delete this layup and all its layers?')) document.getElementById('delete-layup-{{ $layup->id }}').submit();" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
                <p class="text-xs text-gray-400">Showing {{ $supplier->layups->count() }} layup(s)</p>
            </div>
        @endif
    </div>
</x-app-layout>
