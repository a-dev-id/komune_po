<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Purchasing Lite Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-8">
        <section class="w-full max-w-md border border-slate-300 bg-white p-8 shadow-xl">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                    Purchasing Lite
                </h1>

                <p class="mt-3 text-lg text-slate-600">
                    Please login using your username.
                </p>
            </div>

            @if ($errors->any())
            <div class="mb-6 border border-red-300 bg-red-50 px-4 py-3 text-base font-medium text-red-700">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('purchasing-lite.login.submit') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="username" class="mb-2 block text-lg font-bold text-slate-800">
                        Username
                    </label>

                    <input type="text" id="username" name="username" value="{{ old('username') }}" autocomplete="username" autofocus class="h-14 w-full border border-slate-400 bg-white px-4 text-xl text-slate-900 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label for="password" class="mb-2 block text-lg font-bold text-slate-800">
                        Password
                    </label>

                    <input type="password" id="password" name="password" autocomplete="current-password" class="h-14 w-full border border-slate-400 bg-white px-4 text-xl text-slate-900 outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
                </div>

                <label class="flex cursor-pointer items-center gap-3 text-lg text-slate-700">
                    <input type="checkbox" name="remember" value="1" class="h-5 w-5 border-slate-400 text-slate-900">

                    <span>Remember me</span>
                </label>

                <button type="submit" class="h-14 w-full bg-slate-900 text-xl font-bold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-300">
                    Login
                </button>
            </form>
        </section>
    </main>
</body>

</html>