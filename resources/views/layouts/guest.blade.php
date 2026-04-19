<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex">
            <div class="hidden lg:flex lg:w-1/2 bg-brand-800 relative overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <svg class="absolute -top-24 -left-24 w-96 h-96 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <svg class="absolute bottom-12 right-12 w-72 h-72 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="relative z-10 flex flex-col justify-between p-12 w-full">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <span class="text-lg font-bold text-white block leading-tight">CLT Layup</span>
                                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/60">Manager</span>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <h1 class="text-4xl font-bold text-white leading-tight">Manage your CLT<br>layup data with<br>confidence.</h1>
                        <p class="text-white/70 text-lg max-w-md">Track suppliers, configure layups, and manage layer specifications â€” all in one place.</p>
                        <div class="flex items-center gap-6 pt-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">{{ number_format($layupCount) }}</div>
                                <div class="text-xs text-white/50 uppercase tracking-wider">Layups</div>
                            </div>
                            <div class="w-px h-10 bg-white/20"></div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">{{ number_format($supplierCount) }}</div>
                                <div class="text-xs text-white/50 uppercase tracking-wider">Suppliers</div>
                            </div>
                            <div class="w-px h-10 bg-white/20"></div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">{{ number_format($layerCount) }}</div>
                                <div class="text-xs text-white/50 uppercase tracking-wider">Layers</div>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-white/40">&copy; {{ date('Y') }} CLT Layup Manager. All rights reserved.</p>
                </div>
            </div>

            <div class="flex-1 flex flex-col justify-center items-center px-6 py-12 bg-surface-50">
                <div class="lg:hidden mb-8 flex items-center gap-2.5">
                    <div class="w-9 h-9 bg-brand-800 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div class="leading-tight">
                        <span class="text-sm font-bold text-gray-900 block">CLT Layup</span>
                        <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-brand-700">Manager</span>
                    </div>
                </div>

                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
