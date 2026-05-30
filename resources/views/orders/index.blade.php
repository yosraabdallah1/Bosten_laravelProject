@extends('layouts.app')
@section('title', 'Mes Commandes — Bosten')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold mb-4" style="color:#2d6a4f">📦 Mes Commandes</h2>

    @forelse($orders as $order)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <small class="text-muted">Commande</small>
                    <div class="fw-bold">#{{ $order->id }}</div>
                </div>
                <div class="col-md-2">
                    <small class="text-muted">Date</small>
                    <div>{{ $order->created_at->format('d/m/Y') }}</div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Articles</small>
                    <div>{{ $order->items->count() }} produit(s)</div>
                </div>
                <div class="col-md-2">
                    <small class="text-muted">Total</small>
                    <div class="fw-bold" style="color:#2d6a4f">
                        {{ number_format($order->total, 2) }} TND
                    </div>
                </div>
                <div class="col-md-2">
                    <span class="badge
                        @if($order->status === 'pending') bg-warning text-dark
                        @elseif($order->status === 'confirmed') bg-info
                        @elseif($order->status === 'shipped') bg-primary
                        @elseif($order->status === 'delivered') bg-success
                        @else bg-danger @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="col-md-1 text-end">
                    <a href="{{ route('orders.show', $order) }}"
                       class="btn btn-sm btn-outline-success">
                        Voir
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
        <div class="text-center py-5 text-muted">
            <p class="fs-5">Vous n'avez pas encore de commandes.</p>
            <a href="{{ route('products.index') }}" class="btn text-white"
               style="background-color:#2d6a4f">
                Commencer mes achats
            </a>
        </div>
    @endforelse

    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
