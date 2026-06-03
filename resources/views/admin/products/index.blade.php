@extends('layouts.admin')
@section('title', 'Produits')
@section('breadcrumb')
    <li class="breadcrumb-item active">Produits</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Produits</h4>
        <small class="text-muted">{{ $products->total() }} produit(s) au total</small>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-green px-4">
        <i class="bi bi-plus-lg me-1"></i> Nouveau produit
    </a>
</div>

{{-- Filtres --}}
<div class="card table-card mb-4 p-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control"
                   placeholder="Rechercher par nom..."
                   value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="stock" class="form-select">
                <option value="">Tout le stock</option>
                <option value="low" {{ request('stock') == 'low' ? 'selected' : '' }}>Stock faible (&lt;5)</option>
                <option value="out" {{ request('stock') == 'out' ? 'selected' : '' }}>Rupture (0)</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-green flex-fill">Filtrer</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">✕</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:60px">Image</th>
                    <th>Produit</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Statut</th>
                    <th style="width:120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        @if($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}"
                                 width="44" height="44"
                                 class="rounded-2 object-fit-cover"
                                 style="object-fit:cover;">
                        @else
                            <div class="rounded-2 bg-light d-flex align-items-center justify-content-center"
                                 style="width:44px;height:44px;font-size:1.2rem;">🌿</div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $product->name }}</div>
                        <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $product->category->name ?? '—' }}</span>
                    </td>
                    <td class="fw-semibold">{{ number_format($product->price, 2) }} TND</td>
                    <td>
                        @if($product->stock == 0)
                            <span class="badge badge-cancelled">Rupture</span>
                        @elseif($product->stock < 5)
                            <span class="badge badge-pending">{{ $product->stock }} restants</span>
                        @else
                            <span class="badge badge-delivered">{{ $product->stock }}</span>
                        @endif
                    </td>
                    <td>
                        @if($product->is_active)
                            <span class="badge badge-delivered"><i class="bi bi-eye me-1"></i>Visible</span>
                        @else
                            <span class="badge badge-cancelled"><i class="bi bi-eye-slash me-1"></i>Masqué</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="btn btn-sm btn-outline-secondary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST"
                                  action="{{ route('admin.products.destroy', $product) }}"
                                  onsubmit="return confirm('Désactiver ce produit ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Désactiver">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-box-seam display-5 d-block mb-2 opacity-25"></i>
                        Aucun produit trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $products->withQueryString()->links() }}</div>
@endsection
