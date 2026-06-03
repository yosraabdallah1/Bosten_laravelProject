<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Bosten</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <style>
        :root {
            --green-dark:  #1b4332;
            --green-main:  #2d6a4f;
            --green-light: #52b788;
            --sidebar-w:   240px;
        }

        body { background: #f4f6f9; min-height: 100vh; }

        /* ── Sidebar ── */
        #sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--green-dark);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform .3s;
        }
        #sidebar .sidebar-brand {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
            text-decoration: none;
        }
        #sidebar .sidebar-brand h5 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1.1rem;
        }
        #sidebar .sidebar-brand small { color: rgba(255,255,255,.55); font-size: .75rem; }

        #sidebar .nav-section {
            padding: .75rem 1rem .25rem;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .08em;
            color: rgba(255,255,255,.4);
            text-transform: uppercase;
        }
        #sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: .55rem 1.25rem;
            border-radius: 8px;
            margin: 2px .75rem;
            display: flex;
            align-items: center;
            gap: .65rem;
            font-size: .9rem;
            transition: all .2s;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(255,255,255,.12);
            color: #fff;
        }
        #sidebar .nav-link.active { font-weight: 600; }
        #sidebar .nav-link i { font-size: 1rem; width: 20px; text-align: center; }

        #sidebar .sidebar-footer {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,.1);
        }
        #sidebar .sidebar-footer .user-info { color: rgba(255,255,255,.8); font-size: .85rem; }
        #sidebar .sidebar-footer .user-avatar {
            width: 36px; height: 36px;
            background: var(--green-light);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: #fff; font-size: .9rem;
        }

        /* ── Main content ── */
        #main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar ── */
        #topbar {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: .75rem 1.5rem;
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
        }
        #topbar .page-title { font-weight: 600; font-size: 1rem; color: #343a40; }
        #topbar .breadcrumb { font-size: .8rem; margin: 0; }
        #topbar .breadcrumb-item a { color: var(--green-main); text-decoration: none; }

        /* ── Content area ── */
        .content-area { padding: 1.75rem; flex: 1; }

        /* ── Cards ── */
        .kpi-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            transition: transform .2s, box-shadow .2s;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.1); }
        .kpi-card .kpi-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }

        /* ── Table ── */
        .table thead th {
            font-size: .8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c757d;
            border-bottom: none;
            padding: .9rem 1rem;
        }
        .table tbody td { padding: .85rem 1rem; vertical-align: middle; }
        .table-card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); overflow: hidden; }

        /* ── Badges statut ── */
        .badge-pending   { background: #fff3cd; color: #856404; }
        .badge-confirmed { background: #cff4fc; color: #055160; }
        .badge-shipped   { background: #d1ecf1; color: #0c5460; }
        .badge-delivered { background: #d1e7dd; color: #0a3622; }
        .badge-cancelled { background: #f8d7da; color: #842029; }

        /* ── Responsive ── */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }

        /* ── Focus inputs ── */
        .form-control:focus, .form-select:focus {
            border-color: var(--green-main);
            box-shadow: 0 0 0 .2rem rgba(45,106,79,.2);
        }
        .btn-green {
            background: var(--green-main);
            color: #fff;
            border: none;
        }
        .btn-green:hover { background: var(--green-dark); color: #fff; }
    </style>
    @yield('styles')
</head>
<body>

{{-- ══════════ SIDEBAR ══════════ --}}
<nav id="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        <h5>🌿 Bosten Admin</h5>
        <small>Panneau d'administration</small>
    </a>

    <div class="mt-2">
        <div class="nav-section">Navigation</div>

        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>

        <div class="nav-section">Catalogue</div>

        <a href="{{ route('admin.products.index') }}"
           class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Produits
        </a>
        <a href="{{ route('admin.categories.index') }}"
           class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Catégories
        </a>

        <div class="nav-section">Ventes</div>

        <a href="{{ route('admin.orders.index') }}"
           class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Commandes
            @php $pending = \App\Models\Order::where('status','pending')->count(); @endphp
            @if($pending > 0)
                <span class="badge bg-warning text-dark ms-auto">{{ $pending }}</span>
            @endif
        </a>

        <div class="nav-section">Compte</div>

        <a href="{{ route('profile.edit') }}" class="nav-link">
            <i class="bi bi-person"></i> Mon profil
        </a>
        <a href="{{ route('home') }}" class="nav-link" target="_blank">
            <i class="bi bi-shop"></i> Voir la boutique
        </a>
    </div>

    {{-- Footer sidebar --}}
    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            <div class="user-info">
                <div class="fw-semibold">{{ auth()->user()->name ?? '' }}</div>
                <div style="font-size:.75rem;opacity:.6">Administrateur</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="btn btn-sm w-100 text-white border-0"
                    style="background:rgba(255,255,255,.12);">
                <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
            </button>
        </form>
    </div>
</nav>

{{-- ══════════ MAIN ══════════ --}}
<div id="main-content">

    {{-- Topbar --}}
    <div id="topbar">
        <div>
            <button class="btn btn-sm btn-outline-secondary me-2 d-lg-none"
                    id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <span class="page-title">@yield('title', 'Admin')</span>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                @yield('breadcrumb')
            </ol>
        </nav>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mx-4 mt-3">
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="mx-4 mt-3">
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- Contenu --}}
    <div class="content-area">
        @yield('content')
    </div>

</div>

<script>
    // Toggle sidebar mobile
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>
@yield('scripts')
</body>
</html>
