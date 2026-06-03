<!DOCTYPE html>
<html lang="fr">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bosten — Plantes & Jardinage')</title>
    
    {{-- Bootstrap CSS + Icons via CDN --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- Bootstrap JS (requis pour dropdowns / hamburger) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    @yield('styles')
</head>

<body>

    {{-- ── Navbar ───────────────────────────────────────── --}}
    @include('layouts.navigation') {{-- ← inclut votre nouvelle navbar --}}

    {{-- ── Breadcrumb (indicateur de navigation) ─────────── --}}
    @if(!request()->routeIs('home') && !request()->routeIs('login') && !request()->routeIs('register'))
        <div class="container">
            @include('components.breadcrumb')
        </div>
    @endif

    {{-- ── Flash messages ───────────────────────────────── --}}
    <div class="container mt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-octagon me-2"></i> {{ session('warning') }}
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
