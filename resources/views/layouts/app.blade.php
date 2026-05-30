<!DOCTYPE html>
<html lang="fr">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bosten — Plantes & Jardinage')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>

<body>

    {{-- ── Navbar ───────────────────────────────────────── --}}
    @include('layouts.navigation') {{-- ← inclut votre nouvelle navbar --}}

    {{-- ── Flash messages ───────────────────────────────── --}}
    <div class="container mt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- ── Contenu principal ────────────────────────────── --}}
    <main>
        @yield('content')
    </main>

    {{-- ── Footer ───────────────────────────────────────── --}}
    <footer class="py-4 mt-5 text-white text-center" style="background-color: #1b4332;">
        <p class="mb-0">© {{ date('Y') }} Bosten — Plantes & Jardinage en Tunisie 🌿</p>
    </footer>

    @yield('scripts')
</body>

</html>
