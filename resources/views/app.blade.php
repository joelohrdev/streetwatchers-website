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

        {{--
            Link previews for WhatsApp, Facebook, X and other sites. They're written here rather than with Inertia's
            <Head> so crawlers that don't run JavaScript always get them, whether or not SSR is running. Group and
            meetup pages pass their own through view data; every other page uses these defaults.
        --}}
        @php
            $meta = [
                'title' => config('app.name'),
                'description' => 'Find a street photography group near you and join its photo walks, or start one in your city.',
                'url' => url()->current(),
                'type' => 'website',
                ...($meta ?? []),
            ];
        @endphp
        <meta name="description" content="{{ $meta['description'] }}">
        <link rel="canonical" href="{{ $meta['url'] }}">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:type" content="{{ $meta['type'] }}">
        <meta property="og:title" content="{{ $meta['title'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
        <meta property="og:url" content="{{ $meta['url'] }}">
        <meta property="og:image" content="{{ asset('og-image.jpg') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="A black-and-white photograph of a cracked crosswalk on a sunny Chicago street corner.">
        <meta name="twitter:card" content="summary_large_image">

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
