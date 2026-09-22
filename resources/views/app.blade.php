<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline style to set the HTML background color from our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }
        </style>

        {{-- The site is always light, but the browser's tab bar follows the OS color scheme. --}}
        {{-- favicon.svg switches its own fill; the PNGs are fallbacks for browsers without SVG favicons. --}}
        <link rel="icon" href="/icon-black-32.png" type="image/png" sizes="32x32" media="(prefers-color-scheme: light)">
        <link rel="icon" href="/icon-white-32.png" type="image/png" sizes="32x32" media="(prefers-color-scheme: dark)">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml" sizes="any">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
