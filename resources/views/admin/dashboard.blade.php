@extends('layouts.admin')
@section('title', 'Tableau de bord')

@section('content')
<h3 class="fw-bold mb-4">Tableau de bord</h3>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-2 fw-bold" style="color:#2d6a4f">{{ $totalProducts }}</div>
            <div class="text-muted small">Produits</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-warning">{{ $pendingOrders }}</div>
            <div class="text-muted small">Commandes en attente</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-primary">{{ $totalClients }}</div>
            <div class="text-muted small">Clients</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-success">{{ number_format($totalRevenue, 2) }} TND</div>
            <div class="text-muted small">Chiffre d'affaires</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Commandes récentes --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-medium">📦 Commandes récentes</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th><th>Client</th><th>Total</th><th>Statut</th><th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $order->total }} TND</td>
                            <td>
                                <span class="badge
                                    @if($order->status === 'pending') bg-warning text-dark
                                    @elseif($order->status === 'delivered') bg-success
                                    @elseif($order->status === 'cancelled') bg-danger
                                    @else bg-info @endif">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Stock faible --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm border-start border-danger border-3">
            <div class="card-header bg-white fw-medium text-danger">⚠️ Stock faible</div>
            <ul class="list-group list-group-flush">
                @forelse($lowStockProducts as $product)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $product->name }}</span>
                    <span class="badge bg-danger">{{ $product->stock }} restants</span>
                </li>
                @empty
                <li class="list-group-item text-muted small">Tout est bien approvisionné 👍</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
