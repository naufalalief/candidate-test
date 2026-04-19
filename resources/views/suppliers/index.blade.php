<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Suppliers</h1>
                <p class="mt-1 text-sm text-gray-500">Manage timber suppliers and material sourcing.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('suppliers.import.all') }}" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import
                </a>
                <a href="{{ route('suppliers.create') }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Supplier
                </a>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-2 text-sm">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <form method="GET" action="{{ route('suppliers.index') }}" class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search suppliers by name..." class="pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg w-full sm:w-72 focus:ring-1 focus:ring-brand-800 focus:border-brand-800 bg-white placeholder-gray-400" />
            </form>
            <div class="flex items-center gap-2">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export All
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-44 bg-white rounded-lg border border-gray-200 shadow-lg z-20 py-1" style="display: none;">
                        <a href="{{ route('suppliers.export.all', ['format' => 'json']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <span class="w-6 text-center text-xs font-bold text-brand-800">{}</span> JSON
                        </a>
                        <a href="{{ route('suppliers.export.all', ['format' => 'csv']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <span class="w-6 text-center text-xs font-bold text-green-600">csv</span> CSV
                        </a>
                        <a href="{{ route('suppliers.export.all', ['format' => 'excel']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <span class="w-6 text-center text-xs font-bold text-emerald-700">xls</span> Excel
                        </a>
                    </div>
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Sort by
                        @if(request('sort'))
                            <span class="w-1.5 h-1.5 bg-brand-800 rounded-full"></span>
                        @endif
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-48 bg-white rounded-lg border border-gray-200 shadow-lg z-20 py-1" style="display: none;">
                        <a href="{{ route('suppliers.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'name', 'direction' => 'asc'])) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request('sort') === 'name' && request('direction') === 'asc' ? 'font-semibold text-brand-800' : '' }}">
                            Name (Aâ€“Z)
                        </a>
                        <a href="{{ route('suppliers.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'name', 'direction' => 'desc'])) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request('sort') === 'name' && request('direction') === 'desc' ? 'font-semibold text-brand-800' : '' }}">
                            Name (Zâ€“A)
                        </a>
                        <a href="{{ route('suppliers.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'layups', 'direction' => 'desc'])) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request('sort') === 'layups' ? 'font-semibold text-brand-800' : '' }}">
                            Most Layups
                        </a>
                        <a href="{{ route('suppliers.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'created', 'direction' => 'desc'])) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ request('sort') === 'created' ? 'font-semibold text-brand-800' : '' }}">
                            Newest First
                        </a>
                        @if(request('sort'))
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <a href="{{ route('suppliers.index', request()->except('sort', 'direction')) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Clear Sort
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                @if(request('search'))
                    <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Clear
                    </a>
                @endif
            </div>
        </div>

        <div class="border-t border-gray-200">
            @if ($suppliers->isEmpty())
                <div class="py-16 text-center">
                    <svg class="mx-auto w-14 h-14 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <p class="text-sm text-gray-400">No suppliers found. Create your <a href="{{ route('suppliers.create') }}" class="text-brand-800 hover:underline font-medium">first supplier</a> to get started.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Layups</th>
                            <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Created At</th>
                            <th class="px-6 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suppliers as $supplier)
                            @php
                                $avatarColors = [
                                    'bg-brand-800',
                                    'bg-blue-600',
                                    'bg-rose-500',
                                    'bg-amber-600',
                                    'bg-purple-600',
                                    'bg-teal-600',
                                    'bg-indigo-600',
                                    'bg-pink-500',
                                ];
                                $words = explode(' ', $supplier->name);
                                $initials = strtoupper(substr($words[0], 0, 1));
                                if (count($words) > 1) {
                                    $initials .= strtoupper(substr($words[count($words) - 1], 0, 1));
                                }
                                $color = $avatarColors[$supplier->id % count($avatarColors)];
                                $year = $supplier->created_at->format('Y');
                                $supplierId = 'SUP-' . $year . '-' . str_pad($supplier->id, 3, '0', STR_PAD_LEFT);
                            @endphp
                            <tr class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 {{ $color }} rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <a href="{{ route('suppliers.show', $supplier) }}" class="text-sm font-semibold text-gray-900 hover:text-brand-800 transition-colors">{{ $supplier->name }}</a>
                                            <p class="text-xs text-gray-400 mt-0.5">ID: {{ $supplierId }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $supplier->layups_count }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $supplier->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-0.5">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors" title="View">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form id="delete-supplier-{{ $supplier->id }}" action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <a href="#" onclick="event.preventDefault(); if(confirm('Delete this supplier?')) document.getElementById('delete-supplier-{{ $supplier->id }}').submit();" class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>

                @if ($suppliers->hasPages())
                    <div class="px-4 sm:px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-sm text-gray-500">
                            Showing {{ $suppliers->firstItem() }} to {{ $suppliers->lastItem() }} of {{ $suppliers->total() }} results
                        </p>
                        <div class="flex items-center gap-1">
                            @if ($suppliers->onFirstPage())
                                <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </span>
                            @else
                                <a href="{{ $suppliers->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                            @endif
                            @if ($suppliers->hasMorePages())
                                <a href="{{ $suppliers->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
