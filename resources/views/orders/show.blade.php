@extends('layouts.app')
@section('title', 'Commande #' . $order->id)

@section('content')
<div class="container py-5" style="max-width:750px">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="color:#2d6a4f">Commande #{{ $order->id }}</h2>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Retour
        </a>
    </div>

    {{-- Statut --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">Passée le</small>
                <div>{{ $order->created_at->format('d/m/Y à H:i') }}</div>
            </div>
            <span class="badge fs-6
                @if($order->status === 'pending') bg-warning text-dark
                @elseif($order->status === 'confirmed') bg-info
                @elseif($order->status === 'shipped') bg-primary
                @elseif($order->status === 'delivered') bg-success
                @else bg-danger @endif">
                {{ ucfirst($order->status) }}
            </span>
        </div>
    </div>

    {{-- Articles --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-bold">Articles commandés</div>
        <ul class="list-group list-group-flush">
            @foreach($order->items as $item)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-medium">{{ $item->product->name }}</span>
                    <small class="text-muted ms-2">
                        {{ number_format($item->unit_price, 2) }} TND × {{ $item->quantity }}
                    </small>
                </div>
                <span class="fw-bold">
                    {{ number_format($item->unit_price * $item->quantity, 2) }} TND
                </span>
            </li>
            @endforeach
            <li class="list-group-item d-flex justify-content-between fw-bold fs-5">
                <span>Total</span>
                <span style="color:#2d6a4f">{{ number_format($order->total, 2) }} TND</span>
            </li>
        </ul>
    </div>

    {{-- Livraison --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Informations de livraison</div>
        <div class="card-body">
            <p class="mb-1"><strong>Adresse :</strong> {{ $order->address }}</p>
            <p class="mb-0"><strong>Téléphone :</strong> {{ $order->phone }}</p>
        </div>
    </div>

</div>
@endsection
