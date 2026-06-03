<nav class="navbar navbar-expand-lg navbar-dark shadow" style="background-color: #2d6a4f;">
    <div class="container">

        {{-- Logo --}}
        <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">
            🌿 Bosten
        </a>

        {{-- Hamburger mobile --}}
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNav"
                aria-controls="mainNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">

            {{-- ── Liens gauche ── --}}
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                

                {{-- Chatbot (client connecté seulement) --}}
                @auth
                    @if(!auth()->user()->is_admin)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('chatbot.*') ? 'active fw-semibold' : '' }}"
                           href="{{ route('chatbot.index') }}">
                            💬 Basma
                        </a>
                    </li>
                    @endif
                @endauth

            </ul>

            {{-- ── Liens droite ── --}}
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-2">

                @guest
                    {{-- Non connecté --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}"
                           href="{{ route('login') }}">
                            Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm px-3 fw-semibold"
                           href="{{ route('register') }}">
                            S'inscrire
                        </a>
                    </li>

                @else
                    {{-- Panier (client seulement) --}}
                    @if(!auth()->user()->is_admin)
                    @php $cartCount = auth()->user()->cartItems()->sum('quantity'); @endphp
                    <li class="nav-item">
                        <a class="nav-link position-relative {{ request()->routeIs('cart.*') ? 'active' : '' }}"
                           href="{{ route('cart.index') }}">
                            🛒 Panier
                            @if($cartCount > 0)
                                <span class="badge bg-warning text-dark ms-1">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </li>
                    @endif

                    {{-- Mes commandes (client seulement) --}}
                    @if(!auth()->user()->is_admin)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}"
                           href="{{ route('orders.index') }}">
                            📦 Commandes
                        </a>
                    </li>
                    @endif

                    {{-- Admin dashboard --}}
                    @if(auth()->user()->is_admin)
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-semibold
                                  {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                           href="{{ route('admin.dashboard') }}">
                            ⚙️ Admin
                        </a>
                    </li>
                    @endif

                    {{-- Dropdown utilisateur --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                           href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="rounded-circle bg-white text-success fw-bold d-inline-flex
                                         align-items-center justify-content-center"
                                  style="width:32px;height:32px;font-size:0.85rem;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            {{ auth()->user()->name }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:210px;">
                            {{-- Infos --}}
                            <li class="px-3 py-2">
                                <div class="fw-bold">{{ auth()->user()->name }}</div>
                                <div class="small text-muted">{{ auth()->user()->email }}</div>
                                <span class="badge mt-1 {{ auth()->user()->is_admin ? 'bg-warning text-dark' : 'bg-success' }}">
                                    {{ auth()->user()->is_admin ? 'Administrateur' : 'Client' }}
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>

                            @if(auth()->user()->is_admin)
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">⚙️ Tableau de bord</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.products.index') }}">📦 Produits</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.categories.index') }}">🏷️ Catégories</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}">🧾 Commandes</a></li>
                            @else
                                <li><a class="dropdown-item" href="{{ route('cart.index') }}">🛒 Mon panier</a></li>
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">📦 Mes commandes</a></li>
                                <li><a class="dropdown-item" href="{{ route('chatbot.index') }}">💬 Chat Basma</a></li>
                            @endif

                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">✏️ Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger fw-semibold">
                                        🚪 Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>

                @endguest
            </ul>

        </div>{{-- /.navbar-collapse --}}
    </div>{{-- /.container --}}
</nav>
