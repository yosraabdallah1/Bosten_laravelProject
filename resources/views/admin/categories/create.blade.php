@extends('layouts.admin')
@section('title', 'Nouvelle catégorie')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Catégories</a></li>
    <li class="breadcrumb-item active">Créer</li>
@endsection

@section('content')
<div style="max-width:560px">
    <h4 class="fw-bold mb-4">Nouvelle catégorie</h4>

    <div class="card table-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-medium">Nom *</label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required autofocus
                           placeholder="ex: Plantes d'intérieur">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" rows="3"
                              class="form-control"
                              placeholder="Description optionnelle de la catégorie">{{ old('description') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-green px-4">
                        <i class="bi bi-check-lg me-1"></i> Créer
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
