<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('suppliers.index') }}" class="hover:text-brand-800 transition-colors">Suppliers</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.show', $supplier) }}" class="hover:text-brand-800 transition-colors">{{ $supplier->name }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Import</span>
        </nav>
    </x-slot>

    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="card w-full max-w-lg">
            <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Import Layup Data</h2>
                <a href="{{ route('suppliers.show', $supplier) }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>

            <form method="POST" action="{{ route('suppliers.import.preview', $supplier) }}" enctype="multipart/form-data" class="p-6">
                @csrf

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

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Conflict Resolution Strategy</label>
                    <select name="strategy" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-brand-800 focus:border-brand-800 bg-white">
                        <option value="skip">Skip conflicts (Default)</option>
                        <option value="overwrite">Overwrite existing</option>
                        <option value="keep">Keep existing</option>
                        <option value="review">Review each conflict</option>
                    </select>
                </div>

                <div class="mb-6 flex items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <input type="checkbox" name="dry_run" id="dry_run" class="mt-0.5 rounded border-gray-300 text-brand-800 focus:ring-brand-800" />
                    <div>
                        <label for="dry_run" class="text-sm font-semibold text-gray-700 cursor-pointer">Run as Dry Run</label>
                        <p class="text-xs text-gray-500 mt-0.5">Simulate the import process without saving changes to the database.</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 ml-auto shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>

                @if ($errors->any() && !$errors->has('file'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Potential Conflicts Detected</p>
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
                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Confirm Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
