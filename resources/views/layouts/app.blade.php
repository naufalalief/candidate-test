<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white flex flex-col min-h-screen">
        @include('layouts.navigation')
        <div class="flex-1 bg-surface-50">
            <main class="py-4 sm:py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @isset($header)
                        <div class="mb-8">
                            {{ $header }}
                        </div>
                    @endisset

                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
