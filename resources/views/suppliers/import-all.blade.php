<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('suppliers.index') }}" class="hover:text-brand-800 transition-colors">Suppliers</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Import Suppliers</span>
        </nav>
    </x-slot>

    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="card w-full max-w-lg">
            <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Import Suppliers</h2>
                <a href="{{ route('suppliers.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>

            <form method="POST" action="{{ route('suppliers.import.all.preview') }}" enctype="multipart/form-data" class="p-6">
                @csrf

                <div class="mb-6 p-4 bg-brand-50 border border-brand-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-brand-800 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-brand-900">Bulk Supplier Import</p>
                            <p class="text-xs text-brand-700 mt-0.5">Import multiple suppliers with their layups and layers. New suppliers will be created, and existing suppliers will have their data merged.</p>
                        </div>
                    </div>
                </div>

                <div class="relative mb-6" x-data="{ dragover: false, fileName: '' }">
                    <label
                        class="flex flex-col items-center justify-center w-full h-44 border-2 border-dashed rounded-xl cursor-pointer transition-colors"
                        :class="dragover ? 'border-brand-800 bg-brand-50' : 'border-gray-300 hover:border-gray-400 bg-white'"
                        @dragover.prevent="dragover = true"
                        @dragleave.prevent="dragover = false"
                        @drop.prevent="dragover = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0]?.name || ''"
                    >
                        <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm">
                            <span class="font-semibold text-brand-800">Click to upload</span>
                            <span class="text-gray-500">or drag and drop</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-1">JSON, CSV, or Excel up to 10MB</p>
                        <p x-show="fileName" x-text="fileName" class="text-xs font-semibold text-brand-800 mt-2"></p>
                        <input
                            x-ref="fileInput"
                            type="file"
                            name="file"
                            accept=".json,.txt,.csv,.xls,.xlsx"
                            class="hidden"
                            required
                            @change="fileName = $event.target.files[0]?.name || ''"
                        />
                    </label>
                    @if ($errors->has('file'))
                        <p class="mt-2 text-sm text-red-600">{{ $errors->first('file') }}</p>
                    @endif
                </div>

                <div class="mb-6 space-y-3">
                    <p class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Accepted Formats</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                                <span class="text-xs font-bold text-brand-800">{}</span> JSON
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">Multi or single supplier</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                                <span class="text-xs font-bold text-green-600">csv</span> CSV
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">Supplier, Layup, Layer cols</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                                <span class="text-xs font-bold text-blue-600">xls</span> Excel
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">XML Spreadsheet format</p>
                        </div>
                    </div>
                </div>

                @if ($errors->any() && !$errors->has('file'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Import Error</p>
                                <p class="text-xs text-red-600 mt-0.5">
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('suppliers.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import Suppliers
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
