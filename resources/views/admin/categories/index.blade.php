@extends('layouts.admin')
@section('title', 'Catégories')
@section('breadcrumb')
    <li class="breadcrumb-item active">Catégories</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Catégories</h4>
        <small class="text-muted">{{ $categories->count() }} catégorie(s)</small>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-green px-4">
        <i class="bi bi-plus-lg me-1"></i> Nouvelle catégorie
    </a>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Produits</th>
                    <th style="width:120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td class="fw-semibold">{{ $category->name }}</td>
                    <td><code class="text-muted">{{ $category->slug }}</code></td>
                    <td class="text-muted">{{ Str::limit($category->description, 60) ?: '—' }}</td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ $category->products_count }} produit(s)
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.categories.edit', $category) }}"
                               class="btn btn-sm btn-outline-secondary" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST"
                                  action="{{ route('admin.categories.destroy', $category) }}"
                                  onsubmit="return confirm('Supprimer cette catégorie ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-tags display-5 d-block mb-2 opacity-25"></i>
                        Aucune catégorie
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
