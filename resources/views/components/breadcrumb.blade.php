<div class="breadcrumb-container mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-light p-3 rounded shadow-sm">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}" class="text-decoration-none">
                    <i class="bi bi-house-door"></i> Accueil
                </a>
            </li>
            
            @isset($links)
                @foreach($links as $link)
                    @if($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $link['text'] }}
                        </li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $link['url'] }}" class="text-decoration-none">
                                {{ $link['text'] }}
                            </a>
                        </li>
                    @endif
                @endforeach
            @endisset
            
            {{-- Ajouter automatiquement des liens basés sur la route actuelle --}}
            @php
                $currentRoute = Route::currentRouteName();
                $breadcrumbs = [];
                
                // Logique pour déterminer les breadcrumbs
                if ($currentRoute === 'products.show') {
                    $breadcrumbs = [
                        ['text' => 'Boutique', 'url' => route('products.index')],
                        ['text' => $product->name ?? 'Produit', 'url' => ''],
                    ];
                } elseif ($currentRoute === 'cart.index') {
                    $breadcrumbs = [
                        ['text' => 'Mon panier', 'url' => ''],
                    ];
                } elseif ($currentRoute === 'orders.index') {
                    $breadcrumbs = [
                        ['text' => 'Mes commandes', 'url' => ''],
                    ];
                } elseif ($currentRoute === 'orders.show') {
                    $breadcrumbs = [
                        ['text' => 'Mes commandes', 'url' => route('orders.index')],
                        ['text' => 'Commande #' . ($order->id ?? ''), 'url' => ''],
                    ];
                } elseif ($currentRoute === 'chatbot.index') {
                    $breadcrumbs = [
                        ['text' => 'Basma - Chatbot', 'url' => ''],
                    ];
                } elseif ($currentRoute === 'profile.edit') {
                    $breadcrumbs = [
                        ['text' => 'Mon profil', 'url' => ''],
                    ];
                } elseif (strpos($currentRoute, 'admin.') === 0) {
                    $breadcrumbs = [
                        ['text' => 'Administration', 'url' => route('admin.dashboard')],
                    ];
                    
                    // Ajouter des sous-pages admin
                    if (strpos($currentRoute, 'admin.products') !== false) {
                        $breadcrumbs[] = ['text' => 'Produits', 'url' => route('admin.products.index')];
                    } elseif (strpos($currentRoute, 'admin.categories') !== false) {
                        $breadcrumbs[] = ['text' => 'Catégories', 'url' => route('admin.categories.index')];
                    } elseif (strpos($currentRoute, 'admin.orders') !== false) {
                        $breadcrumbs[] = ['text' => 'Commandes', 'url' => route('admin.orders.index')];
                    }
                }
            @endphp
            
            @foreach($breadcrumbs as $crumb)
                @if($loop->last)
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $crumb['text'] }}
                    </li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ $crumb['url'] }}" class="text-decoration-none">
                            {{ $crumb['text'] }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
</div>

<style>
    .breadcrumb-container {
        margin-top: 1rem;
    }
    
    .breadcrumb {
        background-color: #f8f9fa !important;
        border-left: 4px solid #2d6a4f;
    }
    
    .breadcrumb-item a {
        color: #2d6a4f;
        transition: color 0.2s ease;
    }
    
    .breadcrumb-item a:hover {
        color: #1b4332;
        text-decoration: underline !important;
    }
    
    .breadcrumb-item.active {
        color: #6c757d;
        font-weight: 500;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        color: #6c757d;
        content: "›";
    }
</style>