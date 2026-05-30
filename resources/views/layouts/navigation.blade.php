<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #2d6a4f;">
    <div class="container">

        {{-- Logo --}}
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">
            🌿 Bosten
        </a>

        {{-- Hamburger mobile --}}
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">

            {{-- Liens gauche --}}
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') || request()->routeIs('products.*') ? 'active' : '' }}"
                       href="{{ route('products.index') }}">
                        Boutique
                    </a>
                </li>

                @auth
                    @if(!auth()->user()->is_admin)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('chatbot.*') ? 'active' : '' }}"
                           href="{{ route('chatbot.index') }}">
                            💬 Basma
                        </a>
                    </li>
                    @endif
                @endauth
            </ul>

            {{-- Liens droite --}}
            <ul class="navbar-nav ms-auto align-items-center gap-2">

                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm px-3 fw-medium"
                           href="{{ route('register') }}">
                            S'inscrire
                        </a>
                    </li>

                @else
                    {{-- Panier (clients seulement) --}}
                    @if(!auth()->user()->is_admin)
                    <li class="nav-item">
                        <a class="nav-link position-relative"
                           href="{{ route('cart.index') }}">
                            🛒
                            @php
                                $cartCount = auth()->user()->cartItems()->sum('quantity');
                            @endphp
                            @if($cartCount > 0)
                                <span class="position-absolute top-0 start-100
                                             translate-middle badge rounded-pill bg-warning
                                             text-dark" style="font-size:0.65rem">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                    </li>
                    @endif

                    {{-- Dropdown utilisateur --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-1"
                           href="#" data-bs-toggle="dropdown">
                            <span class="rounded-circle bg-white text-success d-inline-flex
                                         align-items-center justify-content-center fw-bold"
                                  style="width:30px; height:30px; font-size:0.8rem">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="d-none d-lg-inline">
                                {{ auth()->user()->name }}
                            </span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">

                            {{-- Infos utilisateur --}}
                            <li class="px-3 py-2">
                                <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                                <div class="small text-muted">{{ auth()->user()->email }}</div>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>

                            @if(auth()->user()->is_admin)
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        ⚙️ Administration
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a class="dropdown-item
                                       {{ request()->routeIs('orders.*') ? 'active' : '' }}"
                                       href="{{ route('orders.index') }}">
                                        📦 Mes commandes
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item
                                       {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                                       href="{{ route('profile.edit') }}">
                                        ✏️ Mon profil
                                    </a>
                                </li>
                            @endif

                            <li><hr class="dropdown-divider my-1"></li>

                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="dropdown-item text-danger">
                                        🚪 Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest

            </ul>
        </div>
    </div>
</nav>
