<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin — Bosten')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<nav class="navbar navbar-dark" style="background-color: #1b4332;">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
            ⚙️ Bosten Admin
        </a>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('home') }}" class="text-white small text-decoration-none">
                🌐 Voir le site
            </a>
            <span class="text-white-50 small">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-light">Déconnexion</button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        {{-- Sidebar --}}
        <nav class="col-md-2 bg-light min-vh-100 pt-4 border-end">
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                       class="nav-link text-dark {{ request()->routeIs('admin.dashboard') ? 'fw-bold' : '' }}">
                        📊 Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.products.create') }}"
                       class="nav-link text-dark {{ request()->routeIs('admin.products*') ? 'fw-bold' : '' }}">
                        🌿 Produits
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.categories.index') }}"
                       class="nav-link text-dark {{ request()->routeIs('admin.categories*') ? 'fw-bold' : '' }}">
                        🏷️ Catégories
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.orders.index') }}"
                       class="nav-link text-dark {{ request()->routeIs('admin.orders*') ? 'fw-bold' : '' }}">
                        📦 Commandes
                    </a>
                </li>
            </ul>
        </nav>

        {{-- Contenu --}}
        <main class="col-md-10 py-4 px-4">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@yield('scripts')
</body>
</html>
