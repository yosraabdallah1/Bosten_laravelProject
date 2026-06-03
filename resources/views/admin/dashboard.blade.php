@extends('layouts.admin')
@section('title', 'Tableau de bord')
@section('breadcrumb')
    <li class="breadcrumb-item active">Tableau de bord</li>
@endsection

@section('content')
{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon" style="background:#d1e7dd">🌿</div>
                <div>
                    <div class="fs-3 fw-bold" style="color:#2d6a4f">{{ $totalProducts }}</div>
                    <div class="text-muted small">Produits actifs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon" style="background:#fff3cd">⏳</div>
                <div>
                    <div class="fs-3 fw-bold text-warning">{{ $pendingOrders }}</div>
                    <div class="text-muted small">Commandes en attente</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon" style="background:#cfe2ff">👥</div>
                <div>
                    <div class="fs-3 fw-bold text-primary">{{ $totalClients }}</div>
                    <div class="text-muted small">Clients inscrits</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="kpi-icon" style="background:#d1e7dd">💰</div>
                <div>
                    <div class="fs-3 fw-bold text-success">{{ number_format($totalRevenue, 2) }}</div>
                    <div class="text-muted small">Chiffre d'affaires (TND)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Commandes récentes --}}
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3 px-4">
                <span class="fw-semibold">📦 Commandes récentes</span>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                    Voir tout
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr>
                            <td class="fw-semibold text-muted">#{{ $order->id }}</td>
                            <td>{{ $order->user->name ?? '—' }}</td>
                            <td class="fw-semibold">{{ number_format($order->total, 2) }} TND</td>
                            <td>
                                <span class="badge badge-{{ $order->status }} px-2 py-1">
                                    {{ match($order->status) {
                                        'pending'   => '⏳ En attente',
                                        'confirmed' => '✅ Confirmée',
                                        'shipped'   => '🚚 Expédiée',
                                        'delivered' => '📬 Livrée',
                                        'cancelled' => '❌ Annulée',
                                        default     => $order->status,
                                    } }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucune commande</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Stock faible --}}
    <div class="col-lg-4">
        <div class="card table-card h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3 px-4">
                <span class="fw-semibold text-danger">⚠️ Stock faible</span>
                <a href="{{ route('admin.products.index') }}?stock=low"
                   class="btn btn-sm btn-outline-danger">Voir</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($lowStockProducts as $product)
                <li class="list-group-item d-flex justify-content-between align-items-center px-4">
                    <div>
                        <div class="fw-medium">{{ $product->name }}</div>
                        <small class="text-muted">{{ $product->category->name ?? '' }}</small>
                    </div>
                    <span class="badge {{ $product->stock == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                        {{ $product->stock == 0 ? 'Rupture' : $product->stock.' restants' }}
                    </span>
                </li>
                @empty
                <li class="list-group-item text-muted text-center py-4">
                    <i class="bi bi-check-circle text-success d-block fs-3 mb-1"></i>
                    Tout est bien approvisionné
                </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- Raccourcis --}}
<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card table-card p-3">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.products.create') }}" class="btn btn-green">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter un produit
                </a>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-plus-lg me-1"></i> Ajouter une catégorie
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-receipt me-1"></i> Gérer les commandes
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
