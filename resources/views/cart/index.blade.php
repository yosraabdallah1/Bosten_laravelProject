@extends('layouts.app')
@section('title', 'Mon Panier — Bosten')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold mb-4" style="color:#2d6a4f">🛒 Mon Panier</h2>

    @if($items->isEmpty())
        <div class="text-center py-5">
            <p class="fs-5 text-muted">Votre panier est vide.</p>
            <a href="{{ route('products.index') }}" class="btn text-white"
               style="background-color:#2d6a4f">
                Découvrir nos produits
            </a>
        </div>
    @else
        <div class="row g-4">

            {{-- Liste des articles --}}
            <div class="col-md-8">
                @foreach($items as $item)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">

                            {{-- Image --}}
                            <div class="col-2">
                                @if($item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}"
                                         class="img-fluid rounded"
                                         style="height:70px; object-fit:cover"
                                         alt="{{ $item->product->name }}">
                                @else
                                    <div class="rounded d-flex align-items-center
                                                justify-content-center"
                                         style="height:70px; background:#e9f5ee; font-size:1.5rem">
                                        🌿
                                    </div>
                                @endif
                            </div>

                            {{-- Nom --}}
                            <div class="col-4">
                                <h6 class="fw-bold mb-0">{{ $item->product->name }}</h6>
                                <small class="text-muted">
                                    {{ number_format($item->product->price, 2) }} TND / unité
                                </small>
                            </div>

                            {{-- Quantité --}}
                            <div class="col-3">
                                <form method="POST"
                                      action="{{ route('cart.update', $item) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="quantity"
                                               value="{{ $item->quantity }}"
                                               min="1" max="{{ $item->product->stock }}"
                                               class="form-control text-center"
                                               onchange="this.form.submit()">
                                    </div>
                                </form>
                            </div>

                            {{-- Sous-total --}}
                            <div class="col-2 text-end fw-bold" style="color:#2d6a4f">
                                {{ number_format($item->product->price * $item->quantity, 2) }} TND
                            </div>

                            {{-- Supprimer --}}
                            <div class="col-1 text-end">
                                <form method="POST"
                                      action="{{ route('cart.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger border-0">
                                        ✕
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Récapitulatif --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm sticky-top" style="top:20px">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Récapitulatif</h5>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Sous-total</span>
                            <span>{{ number_format($total, 2) }} TND</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-muted small">
                            <span>Livraison</span>
                            <span>Gratuite</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
                            <span>Total</span>
                            <span style="color:#2d6a4f">
                                {{ number_format($total, 2) }} TND
                            </span>
                        </div>

                        <a href="{{ route('orders.checkout') }}"
                           class="btn w-100 text-white fw-medium"
                           style="background-color:#2d6a4f">
                            Passer la commande →
                        </a>
                        <a href="{{ route('products.index') }}"
                           class="btn btn-outline-secondary w-100 mt-2 btn-sm">
                            Continuer mes achats
                        </a>
                    </div>
                </div>
            </div>

        </div>
    @endif
</div>
@endsection
