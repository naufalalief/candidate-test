<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('suppliers.index') }}" class="hover:text-brand-800 transition-colors">Suppliers</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.show', $supplier) }}" class="hover:text-brand-800 transition-colors">{{ $supplier->name }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Conflict Resolution</span>
        </nav>
    </x-slot>

    <div x-data="{ activeConflict: 0, resolved: {}, totalConflicts: {{ count($conflicts) }}, resolve(idx, value) { this.resolved[idx] = value; setTimeout(() => { if (idx < this.totalConflicts - 1) this.activeConflict = idx + 1; }, 300); } }" class="card">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-lg font-bold text-gray-900">Conflict Resolution: Import</h2>
                <span class="badge-yellow">Needs Review</span>
            </div>
            <p class="text-sm text-gray-500">Please review discrepancies between incoming data and existing records.</p>
        </div>

        <form method="POST" action="{{ route('suppliers.import.resolve', $supplier) }}">
            @csrf

            <div class="flex flex-col md:flex-row md:min-h-[500px]">
                <div class="w-full md:w-72 border-b md:border-b-0 md:border-r border-gray-200 bg-gray-50/50 shrink-0">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <span class="text-sm font-semibold text-gray-700">Conflicting Layups ({{ count($conflicts) }})</span>
                        </div>
                    </div>
                    <div class="p-2 space-y-1">
                        @foreach ($conflicts as $idx => $conflict)
                            <button
                                type="button"
                                @click="activeConflict = {{ $idx }}"
                                class="w-full text-left px-3 py-2.5 rounded-lg flex items-center justify-between transition-colors"
                                :class="activeConflict === {{ $idx }} ? 'bg-brand-50 border border-brand-200' : 'hover:bg-white border border-transparent'"
                            >
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $conflict['layup_name'] }}</p>
                                    <p class="text-xs text-gray-500">Layer {{ $conflict['layer_order'] }}</p>
                                    <p x-show="resolved[{{ $idx }}]" x-text="resolved[{{ $idx }}] === 'existing' ? 'âœ“ Keep Existing' : 'âœ“ Accept New'" class="text-xs font-semibold text-green-600 mt-0.5"></p>
                                </div>
                                <template x-if="resolved[{{ $idx }}]">
                                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </template>
                                <template x-if="!resolved[{{ $idx }}]">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ !empty($conflict['identical']) ? 'bg-yellow-400' : 'bg-red-500' }}"></span>
                                </template>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="flex-1">
                    @foreach ($conflicts as $index => $conflict)
                        <div x-show="activeConflict === {{ $index }}" x-cloak class="h-full flex flex-col">
                            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-base font-bold text-gray-900">{{ $conflict['layup_name'] }} Comparison</h3>
                                    <span class="badge-gray">Layer {{ $conflict['layer_order'] }}</span>
                                </div>
                                @if (!empty($conflict['identical']))
                                    <span class="badge-yellow">Identical Data</span>
                                @else
                                    <div class="flex items-center gap-1 text-xs text-gray-500">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        Differences highlighted in <span class="font-semibold text-red-600">Red</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 p-4 sm:p-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                                        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <div>
                                                <h4 class="text-sm font-bold text-gray-800">Existing Version</h4>
                                                <p class="text-[11px] text-gray-400">Current record in database</p>
                                            </div>
                                            <span class="ml-auto w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                                        </div>
                                        <table class="w-full">
                                            <thead>
                                                <tr class="bg-gray-50/50">
                                                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Field</th>
                                                    <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-400 uppercase">Value</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach (['thickness', 'width', 'angle'] as $field)
                                                    <tr class="{{ isset($conflict['diffs'][$field]) ? 'bg-red-50/50' : '' }}">
                                                        <td class="px-4 py-3 text-sm text-gray-600 capitalize">{{ $field }}</td>
                                                        <td class="px-4 py-3 text-sm text-right {{ isset($conflict['diffs'][$field]) ? 'font-bold text-red-600' : 'text-gray-900' }}">
                                                            {{ $conflict['existing'][$field] }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="border border-brand-200 rounded-xl overflow-hidden">
                                        <div class="px-5 py-3 bg-brand-50 border-b border-brand-200 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-brand-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            <div>
                                                <h4 class="text-sm font-bold text-brand-900">Importing Version</h4>
                                                <p class="text-[11px] text-brand-600">From imported file</p>
                                            </div>
                                            <span class="ml-auto w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                                        </div>
                                        <table class="w-full">
                                            <thead>
                                                <tr class="bg-brand-50/30">
                                                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase">Field</th>
                                                    <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-gray-400 uppercase">Value</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach (['thickness', 'width', 'angle'] as $field)
                                                    <tr class="{{ isset($conflict['diffs'][$field]) ? 'bg-brand-50/30' : '' }}">
                                                        <td class="px-4 py-3 text-sm text-gray-600 capitalize">{{ $field }}</td>
                                                        <td class="px-4 py-3 text-sm text-right {{ isset($conflict['diffs'][$field]) ? 'font-bold text-brand-800' : 'text-gray-900' }}">
                                                            {{ $conflict['incoming'][$field] }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mt-4">
                                    <label class="cursor-pointer" @click="resolve({{ $index }}, 'existing')">
                                        <input type="radio" name="resolutions[{{ $conflict['layup_index'] }}_{{ $conflict['layer_index'] }}]" value="existing" checked class="hidden peer" />
                                        <div class="peer-checked:ring-2 peer-checked:ring-brand-800 border border-gray-200 rounded-xl py-3 px-4 text-center transition-all hover:bg-gray-50">
                                            <span class="text-sm font-semibold text-gray-700 flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/></svg>
                                                Keep Existing
                                            </span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer" @click="resolve({{ $index }}, 'incoming')">
                                        <input type="radio" name="resolutions[{{ $conflict['layup_index'] }}_{{ $conflict['layer_index'] }}]" value="incoming" class="hidden peer" />
                                        <div class="peer-checked:ring-2 peer-checked:ring-brand-800 border border-brand-200 bg-brand-800 rounded-xl py-3 px-4 text-center transition-all hover:bg-brand-900">
                                            <span class="text-sm font-semibold text-white flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                Accept New
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50/50 rounded-b-xl">
                <a href="{{ route('suppliers.show', $supplier) }}" class="btn-secondary w-full sm:w-auto justify-center">
                    Cancel Import
                </a>

                <div class="flex items-center gap-4 order-first sm:order-none">
                    <button type="button" @click="activeConflict = Math.max(0, activeConflict - 1)" class="text-sm text-gray-500 hover:text-gray-700 transition-colors flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Previous Conflict
                    </button>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        <span x-text="activeConflict + 1">1</span> of {{ count($conflicts) }} Discrepancies
                        <span class="ml-2 text-green-600" x-show="Object.keys(resolved).length > 0" x-text="'(' + Object.keys(resolved).length + ' resolved)'"></span>
                    </span>
                    <button type="button" @click="activeConflict = Math.min({{ count($conflicts) - 1 }}, activeConflict + 1)" class="text-sm font-semibold text-brand-800 hover:text-brand-900 transition-colors flex items-center gap-1">
                        Next Conflict
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>

                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Apply Resolutions
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
