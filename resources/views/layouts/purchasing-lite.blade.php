<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>@yield('title', 'Purchasing Lite')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    @php
    $currentUser = auth()->user();
    @endphp

    <div class="min-h-screen">
        <header class="border-b border-slate-300 bg-white">
            <div class="flex items-center justify-between px-6 py-4">
                <a href="/purchasing-lite/dashboard" class="text-inherit no-underline">
                    <h1 class="text-lg font-bold text-slate-950">
                        Purchasing Lite
                    </h1>

                    <p class="mt-1 text-sm text-slate-600">
                        Simple Excel-style purchasing system
                    </p>
                </a>

                <div class="flex items-center gap-4">
                    @if ($currentUser)
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-bold text-slate-900">
                            {{ $currentUser->name }}
                        </p>
                    </div>
                    @endif

                    <form method="POST" action="/purchasing-lite/logout">
                        @csrf

                        <button type="submit" class="inline-flex items-center justify-center border border-slate-300 bg-white px-5 py-2 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
            <div class="mb-6 border border-green-300 bg-green-50 px-5 py-4 text-base font-medium text-green-800">
                {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="mb-6 border border-red-300 bg-red-50 px-5 py-4 text-base font-medium text-red-800">
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>

</html>