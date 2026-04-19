<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Overview of your CLT layup data.</p>
        </div>
    </x-slot>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('suppliers.index') }}" class="card p-5 hover:border-brand-800/30 transition-colors group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Suppliers</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($supplierCount) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center group-hover:bg-brand-100 transition-colors">
                    <svg class="w-6 h-6 text-brand-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                </div>
            </div>
        </a>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Layups</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($layupCount) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L12 12.75 6.429 9.75m11.142 0l4.179 2.25-4.179 2.25m0 0L12 17.25l-5.571-3m11.142 0l4.179 2.25L12 21.75l-9.75-5.25 4.179-2.25"/></svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Layers</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($layerCount) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zm0 9.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zm9.75-9.75A2.25 2.25 0 0115.75 3.75H18a2.25 2.25 0 012.25 2.25v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zm0 9.75a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 15.75V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Recent Suppliers --}}
        <div class="card">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Recent Suppliers</h2>
                <a href="{{ route('suppliers.index') }}" class="text-xs font-medium text-brand-800 hover:text-brand-900 transition-colors">View all &rarr;</a>
            </div>
            @if($recentSuppliers->isEmpty())
                <div class="px-5 py-10 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                    <p class="text-sm text-gray-400">No suppliers yet.</p>
                    <a href="{{ route('suppliers.create') }}" class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-brand-800 hover:text-brand-900">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Add your first supplier
                    </a>
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($recentSuppliers as $supplier)
                        <a href="{{ route('suppliers.show', $supplier) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center text-brand-800 text-xs font-bold">
                                    {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $supplier->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $supplier->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-gray-400">{{ $supplier->layups_count }} {{ Str::plural('layup', $supplier->layups_count) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent Layups --}}
        <div class="card">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Recent Layups</h2>
            </div>
            @if($recentLayups->isEmpty())
                <div class="px-5 py-10 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L12 12.75 6.429 9.75"/></svg>
                    <p class="text-sm text-gray-400">No layups yet.</p>
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($recentLayups as $layup)
                        <a href="{{ route('suppliers.layups.show', [$layup->supplier, $layup]) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-xs font-bold">
                                    {{ strtoupper(substr($layup->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $layup->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $layup->supplier->name }} &middot; {{ $layup->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-gray-400">{{ $layup->layers_count }} {{ Str::plural('layer', $layup->layers_count) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Quick Actions</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
            <a href="{{ route('suppliers.create') }}" class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50 transition-colors">
                <div class="w-9 h-9 rounded-lg bg-brand-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-brand-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">New Supplier</p>
                    <p class="text-xs text-gray-400">Add a supplier</p>
                </div>
            </a>
            <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50 transition-colors">
                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Browse Suppliers</p>
                    <p class="text-xs text-gray-400">View all suppliers</p>
                </div>
            </a>
            <a href="{{ route('suppliers.import.all') }}" class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50 transition-colors">
                <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Import Data</p>
                    <p class="text-xs text-gray-400">Upload from file</p>
                </div>
            </a>
            <a href="{{ route('suppliers.export.all', ['format' => 'json']) }}" class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50 transition-colors">
                <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Export All</p>
                    <p class="text-xs text-gray-400">Download as JSON</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Recent Activity --}}
    @if($notifications->isNotEmpty())
        <div class="card mt-6">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Recent Activity</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($notifications as $notification)
                    <div class="flex items-center gap-3 px-5 py-3">
                        @if(($notification->data['action'] ?? '') === 'created')
                            <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </div>
                        @elseif(($notification->data['action'] ?? '') === 'updated')
                            <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            </div>
                        @elseif(($notification->data['action'] ?? '') === 'deleted')
                            <div class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                        @else
                            <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700">{{ $notification->data['message'] ?? 'Data changed' }}</p>
                            <p class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @if(!empty($notification->data['url']))
                            <a href="{{ $notification->data['url'] }}" class="text-gray-400 hover:text-brand-800 transition-colors shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-app-layout>
