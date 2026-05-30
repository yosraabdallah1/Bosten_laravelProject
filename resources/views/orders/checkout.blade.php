@extends('layouts.app')
@section('title', 'Finaliser la commande — Bosten')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold mb-4" style="color:#2d6a4f">📦 Finaliser la commande</h2>

    <div class="row g-4">

        {{-- Formulaire livraison --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Informations de livraison</h5>

                    <form method="POST" action="{{ route('orders.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-medium">Adresse de livraison *</label>
                            <textarea name="address" rows="3"
                                      class="form-control @error('address') is-invalid @enderror"
                                      placeholder="Numéro, rue, ville, gouvernorat..."
                                      required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium">Numéro de téléphone *</label>
                            <input type="text" name="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="ex: 55 123 456"
                                   value="{{ old('phone') }}"
                                   required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit"
                                class="btn w-100 text-white fw-medium py-2"
                                style="background-color:#2d6a4f">
                            ✓ Confirmer la commande ({{ number_format($total, 2) }} TND)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Récap commande --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Votre commande</div>
                <ul class="list-group list-group-flush">
                    @foreach($items as $item)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>
                            {{ $item->product->name }}
                            <small class="text-muted">× {{ $item->quantity }}</small>
                        </span>
                        <span class="fw-medium">
                            {{ number_format($item->product->price * $item->quantity, 2) }} TND
                        </span>
                    </li>
                    @endforeach
                    <li class="list-group-item d-flex justify-content-between fw-bold">
                        <span>Total</span>
                        <span style="color:#2d6a4f">{{ number_format($total, 2) }} TND</span>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>
@endsection
