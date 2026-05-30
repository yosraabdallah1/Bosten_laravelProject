@extends('layouts.app')
@section('title', 'Boutique — Bosten')

@section('content')
<div class="container py-5">

    {{-- En-tête --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="fw-bold" style="color:#2d6a4f">🌿 Notre Boutique</h2>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control"
                   placeholder="Rechercher un produit..."
                   value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}"
                        {{ request('category') === $cat->slug ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn w-100 text-white"
                    style="background-color:#2d6a4f">
                Filtrer
            </button>
        </div>
        @if(request('search') || request('category'))
        <div class="col-md-1">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100">
                ✕
            </a>
        </div>
        @endif
    </form>

    {{-- Grille produits --}}
    @if($products->isEmpty())
        <div class="text-center py-5 text-muted">
            <p class="fs-5">Aucun produit trouvé.</p>
            <a href="{{ route('products.index') }}" class="btn btn-outline-success">
                Voir tous les produits
            </a>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach($products as $product)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">

                    {{-- Image --}}
                    <a href="{{ route('products.show', $product->slug) }}">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 class="card-img-top"
                                 style="height:200px; object-fit:cover;"
                                 alt="{{ $product->name }}">
                        @else
                            <div class="card-img-top d-flex align-items-center justify-content-center"
                                 style="height:200px; background:#e9f5ee; font-size:3rem;">
                                🌿
                            </div>
                        @endif
                    </a>

                    <div class="card-body d-flex flex-column">
                        <span class="badge mb-2 text-white"
                              style="background-color:#52b788; width:fit-content">
                            {{ $product->category->name }}
                        </span>

                        <h6 class="card-title fw-bold">
                            <a href="{{ route('products.show', $product->slug) }}"
                               class="text-decoration-none text-dark">
                                {{ $product->name }}
                            </a>
                        </h6>

                        <p class="text-muted small flex-grow-1">
                            {{ Str::limit($product->description, 60) }}
                        </p>

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-bold fs-5" style="color:#2d6a4f">
                                {{ number_format($product->price, 2) }} TND
                            </span>

                            @if($product->stock > 0)
                                <span class="badge bg-success">En stock</span>
                            @else
                                <span class="badge bg-danger">Rupture</span>
                            @endif
                        </div>

                        {{-- Bouton ajout panier --}}
                        @auth
                            @if($product->stock > 0)
                            <form method="POST" action="{{ route('cart.store') }}" class="mt-3">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit"
                                        class="btn btn-sm w-100 text-white"
                                        style="background-color:#2d6a4f">
                                    🛒 Ajouter au panier
                                </button>
                            </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="btn btn-sm btn-outline-success w-100 mt-3">
                                Connectez-vous pour acheter
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $products->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
