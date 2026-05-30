@extends('layouts.admin')
@section('title', 'Modifier — ' . $product->name)

@section('content')
<div style="max-width:650px">
    <h4 class="fw-bold mb-4">✏️ Modifier : {{ $product->name }}</h4>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.products.update', $product) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-medium">Nom du produit *</label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $product->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-medium">Catégorie *</label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" rows="3" class="form-control">
                            {{ old('description', $product->description) }}
                        </textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Prix (TND) *</label>
                        <input type="number" name="price" step="0.01" min="0"
                               class="form-control"
                               value="{{ old('price', $product->price) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Stock *</label>
                        <input type="number" name="stock" min="0"
                               class="form-control"
                               value="{{ old('stock', $product->stock) }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-medium">Nouvelle image (optionnel)</label>
                        @if($product->image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $product->image) }}"
                                     height="80" class="rounded">
                            </div>
                        @endif
                        <input type="file" name="image" accept="image/*" class="form-control">
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_active"
                                   class="form-check-input" id="is_active"
                                   {{ $product->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Produit visible dans la boutique
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn text-white px-4"
                            style="background-color:#2d6a4f">
                        Enregistrer les modifications
                    </button>
                    <a href="{{ route('admin.dashboard') }}"
                       class="btn btn-outline-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
