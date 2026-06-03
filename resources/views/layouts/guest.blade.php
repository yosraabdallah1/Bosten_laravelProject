<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bosten')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <style>
        body {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            min-height: 100vh;
        }
        .auth-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(45, 106, 79, 0.12);
        }
        .auth-card .card-body {
            padding: 2.5rem;
        }
        .form-control:focus {
            border-color: #2d6a4f;
            box-shadow: 0 0 0 0.2rem rgba(45, 106, 79, 0.2);
        }
        .btn-bosten {
            background-color: #2d6a4f;
            color: white;
            border: none;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-bosten:hover {
            background-color: #1b4332;
            color: white;
        }
        .logo-link:hover h2 {
            color: #1b4332 !important;
        }
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.25rem 0;
            color: #adb5bd;
            font-size: 0.85rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #dee2e6;
        }
    </style>
    @yield('styles')
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-sm-10 col-md-6 col-lg-5 col-xl-4">

            {{-- Logo --}}
            <div class="text-center mb-4">
                <a href="{{ route('home') }}" class="text-decoration-none logo-link">
                    <div class="display-6 mb-1">🌿</div>
                    <h2 class="fw-bold mb-0" style="color: #2d6a4f;">Bosten</h2>
                    <p class="text-muted small mb-0">Plantes & Jardinage en Tunisie</p>
                </a>
            </div>

            {{-- Carte --}}
            <div class="card auth-card">
                <div class="card-body">
                    @yield('content')
                </div>
            </div>

            {{-- Retour boutique --}}
            <div class="text-center mt-3">
                <a href="{{ route('home') }}" class="text-muted small text-decoration-none">
                    ← Retour à la boutique
                </a>
            </div>

        </div>
    </div>
</div>
</body>
</html>
