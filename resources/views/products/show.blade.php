@extends('layouts.app')
@section('title', $product->name . ' — Bosten')

@section('content')
<div class="container py-5">

    {{-- Fil d'Ariane --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">
                    {{ $product->category->name }}
                </a>
            </li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-5">

        {{-- Image --}}
        <div class="col-md-5">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}"
                     class="img-fluid rounded-3 shadow-sm"
                     alt="{{ $product->name }}">
            @else
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                     style="height:350px; background:#e9f5ee; font-size:6rem;">
                    🌿
                </div>
            @endif
        </div>

        {{-- Détails --}}
        <div class="col-md-7">
            <span class="badge mb-2 text-white" style="background-color:#52b788">
                {{ $product->category->name }}
            </span>

            <h2 class="fw-bold">{{ $product->name }}</h2>

            <p class="fs-3 fw-bold my-3" style="color:#2d6a4f">
                {{ number_format($product->price, 2) }} TND
            </p>

            <p class="text-muted">{{ $product->description }}</p>

            {{-- Stock --}}
            <div class="mb-4">
                @if($product->stock > 0)
                    <span class="badge bg-success fs-6">
                        ✓ En stock ({{ $product->stock }} disponibles)
                    </span>
                @else
                    <span class="badge bg-danger fs-6">Rupture de stock</span>
                @endif
            </div>

            {{-- Formulaire ajout panier --}}
            @auth
                @if($product->stock > 0)
                <form method="POST" action="{{ route('cart.store') }}" class="d-flex gap-2">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div style="width:100px">
                        <input type="number" name="quantity" value="1" min="1"
                               max="{{ $product->stock }}"
                               class="form-control text-center">
                    </div>
                    <button type="submit" class="btn text-white px-4"
                            style="background-color:#2d6a4f">
                        🛒 Ajouter au panier
                    </button>
                </form>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-success">
                    Connectez-vous pour acheter
                </a>
            @endauth
        </div>
    </div>

    {{-- Produits similaires --}}
    @if($related->isNotEmpty())
    <div class="mt-5">
        <h4 class="fw-bold mb-3">Vous pourriez aussi aimer</h4>
        <div class="row row-cols-2 row-cols-md-4 g-3">
            @foreach($related as $item)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <a href="{{ route('products.show', $item->slug) }}">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}"
                                 class="card-img-top"
                                 style="height:150px; object-fit:cover"
                                 alt="{{ $item->name }}">
                        @else
                            <div class="card-img-top d-flex align-items-center
                                        justify-content-center"
                                 style="height:150px; background:#e9f5ee; font-size:2rem;">
                                🌿
                            </div>
                        @endif
                    </a>
                    <div class="card-body">
                        <p class="card-title small fw-bold mb-1">{{ $item->name }}</p>
                        <span class="fw-bold" style="color:#2d6a4f">
                            {{ number_format($item->price, 2) }} TND
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
