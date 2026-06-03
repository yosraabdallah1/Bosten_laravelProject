@extends('layouts.guest')
@section('title', 'Créer un compte — Bosten')

@section('content')
<h4 class="fw-bold mb-4" style="color: #2d6a4f;">Créer un compte</h4>

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label fw-medium">Nom complet</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name') }}" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label fw-medium">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label fw-medium">Mot de passe</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               required>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-4">
        <label class="form-label fw-medium">Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-bosten w-100">
        Créer mon compte
    </button>

    <div class="divider">ou</div>
    <p class="text-center mb-0 small">
        Déjà un compte ?
        <a href="{{ route('login') }}" class="text-decoration-none fw-semibold"
           style="color: #2d6a4f;">Se connecter</a>
    </p>
</form>
@endsection
