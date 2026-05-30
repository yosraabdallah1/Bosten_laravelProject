@extends('layouts.guest')
@section('title', 'Connexion — Bosten')

@section('content')
<h4 class="fw-bold mb-4" style="color: #2d6a4f;">Connexion</h4>

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label fw-medium">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" required autofocus>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label fw-medium">Mot de passe</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               required>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label small" for="remember">Se souvenir de moi</label>
        </div>
        @if(Route::has('password.request'))
            <a class="small text-decoration-none" href="{{ route('password.request') }}">
                Mot de passe oublié ?
            </a>
        @endif
    </div>

    <button type="submit" class="btn w-100 text-white fw-medium"
            style="background-color: #2d6a4f;">
        Se connecter
    </button>

    <hr>
    <p class="text-center mb-0 small">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="text-decoration-none fw-medium"
           style="color: #2d6a4f;">S'inscrire</a>
    </p>
</form>
@endsection
