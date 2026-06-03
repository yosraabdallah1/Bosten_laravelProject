@extends('layouts.app')
@section('title', 'Mon profil — Bosten')

@section('content')
<div class="container py-5" style="max-width: 760px;">

    {{-- En-tête --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white fs-3"
             style="width:64px;height:64px;background:#2d6a4f;flex-shrink:0;">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <h4 class="fw-bold mb-0">{{ $user->name }}</h4>
            <span class="text-muted small">{{ $user->email }}</span>
            <span class="badge ms-2 {{ $user->is_admin ? 'bg-warning text-dark' : 'bg-success' }}">
                {{ $user->is_admin ? 'Administrateur' : 'Client' }}
            </span>
        </div>
    </div>

    {{-- Onglets --}}
    <ul class="nav nav-tabs mb-4" id="profileTabs">
        <li class="nav-item">
            <a class="nav-link {{ !request()->has('tab') || request()->tab == 'info' ? 'active' : '' }}"
               href="{{ route('profile.edit') }}?tab=info">
                <i class="bi bi-person me-1"></i> Informations
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->tab == 'password' ? 'active' : '' }}"
               href="{{ route('profile.edit') }}?tab=password">
                <i class="bi bi-lock me-1"></i> Mot de passe
            </a>
        </li>
        @if(!$user->is_admin)
        <li class="nav-item">
            <a class="nav-link {{ request()->tab == 'orders' ? 'active' : '' }}"
               href="{{ route('profile.edit') }}?tab=orders">
                <i class="bi bi-box-seam me-1"></i> Mes commandes
            </a>
        </li>
        @endif
        <li class="nav-item ms-auto">
            <a class="nav-link text-danger {{ request()->tab == 'delete' ? 'active' : '' }}"
               href="{{ route('profile.edit') }}?tab=delete">
                <i class="bi bi-trash me-1"></i> Supprimer
            </a>
        </li>
    </ul>

    {{-- ── Onglet : Informations ── --}}
    @if(!request()->has('tab') || request()->tab == 'info')
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h6 class="fw-semibold mb-0">Informations personnelles</h6>
            <p class="text-muted small">Modifiez votre nom et votre adresse email.</p>
        </div>
        <div class="card-body px-4 pb-4">

            @if(session('status') === 'profile-updated')
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    Profil mis à jour avec succès.
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label class="form-label fw-medium">Nom complet</label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}"
                           required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium">Adresse email</label>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}"
                           required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                        <div class="mt-2 p-3 bg-warning bg-opacity-10 rounded border border-warning">
                            <p class="mb-1 small text-warning-emphasis fw-medium">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Votre email n'est pas vérifié.
                            </p>
                            <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    Renvoyer l'email de vérification
                                </button>
                            </form>
                            @if(session('status') === 'verification-link-sent')
                                <p class="mt-2 mb-0 small text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Lien envoyé à {{ $user->email }}.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <button type="submit" class="btn text-white px-4" style="background:#2d6a4f;">
                    <i class="bi bi-check-lg me-1"></i> Enregistrer
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- ── Onglet : Mot de passe ── --}}
    @if(request()->tab == 'password')
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h6 class="fw-semibold mb-0">Changer le mot de passe</h6>
            <p class="text-muted small">Utilisez un mot de passe long et aléatoire pour sécuriser votre compte.</p>
        </div>
        <div class="card-body px-4 pb-4">

            @if(session('status') === 'password-updated')
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    Mot de passe mis à jour avec succès.
                </div>
            @endif

            @if($errors->updatePassword->any())
                <div class="alert alert-danger border-0 shadow-sm">
                    @foreach($errors->updatePassword->all() as $error)
                        <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-medium">Mot de passe actuel</label>
                    <input type="password" name="current_password"
                           class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
                           autocomplete="current-password">
                    @if($errors->updatePassword->has('current_password'))
                        <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">Nouveau mot de passe</label>
                    <input type="password" name="password"
                           class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
                           autocomplete="new-password">
                    @if($errors->updatePassword->has('password'))
                        <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="password_confirmation"
                           class="form-control"
                           autocomplete="new-password">
                </div>

                <button type="submit" class="btn text-white px-4" style="background:#2d6a4f;">
                    <i class="bi bi-shield-check me-1"></i> Mettre à jour
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- ── Onglet : Mes commandes ── --}}
    @if(request()->tab == 'orders' && !$user->is_admin)
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-semibold mb-0">Mes commandes</h6>
                <p class="text-muted small">Historique de vos {{ $orders->total() }} commande(s).</p>
            </div>
            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">
                Voir tout
            </a>
        </div>
        <div class="card-body p-0">
            @forelse($orders as $order)
            <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">Commande #{{ $order->id }}</div>
                    <small class="text-muted">{{ $order->created_at->format('d/m/Y') }}</small>
                </div>
                <div class="text-end">
                    <div class="fw-semibold">{{ number_format($order->total, 2) }} TND</div>
                    <span class="badge
                        {{ match($order->status) {
                            'pending'   => 'bg-warning text-dark',
                            'confirmed' => 'bg-info text-dark',
                            'shipped'   => 'bg-primary',
                            'delivered' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            default     => 'bg-secondary',
                        } }}">
                        {{ match($order->status) {
                            'pending'   => 'En attente',
                            'confirmed' => 'Confirmée',
                            'shipped'   => 'Expédiée',
                            'delivered' => 'Livrée',
                            'cancelled' => 'Annulée',
                            default     => $order->status,
                        } }}
                    </span>
                </div>
                <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-secondary ms-3">
                    <i class="bi bi-eye"></i>
                </a>
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-box-seam display-5 d-block mb-2 opacity-25"></i>
                Aucune commande pour le moment.
                <div class="mt-3">
                    <a href="{{ route('products.index') }}" class="btn btn-sm" style="background:#2d6a4f;color:white;">
                        Découvrir la boutique
                    </a>
                </div>
            </div>
            @endforelse
        </div>
        @if($orders->hasPages())
        <div class="card-footer bg-white border-0 px-4 py-3">
            {{ $orders->withQueryString()->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- ── Onglet : Supprimer le compte ── --}}
    @if(request()->tab == 'delete')
    <div class="card border-0 shadow-sm rounded-3 border-danger" style="border-left: 4px solid #dc3545 !important;">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h6 class="fw-semibold text-danger mb-0">
                <i class="bi bi-exclamation-triangle me-1"></i> Supprimer mon compte
            </h6>
            <p class="text-muted small">
                Une fois supprimé, toutes vos données seront définitivement perdues.
            </p>
        </div>
        <div class="card-body px-4 pb-4">

            @if($errors->userDeletion->any())
                <div class="alert alert-danger border-0">
                    @foreach($errors->userDeletion->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="alert alert-warning border-0 d-flex gap-2">
                <i class="bi bi-info-circle-fill mt-1"></i>
                <span>Cette action est <strong>irréversible</strong>. Toutes vos commandes et données seront supprimées.</span>
            </div>

            <form method="POST" action="{{ route('profile.destroy') }}"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ?')">
                @csrf
                @method('DELETE')

                <div class="mb-4" style="max-width:360px;">
                    <label class="form-label fw-medium">Confirmez avec votre mot de passe</label>
                    <input type="password" name="password"
                           class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif"
                           placeholder="Votre mot de passe actuel"
                           autocomplete="current-password">
                    @if($errors->userDeletion->has('password'))
                        <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                    @endif
                </div>

                <button type="submit" class="btn btn-danger px-4">
                    <i class="bi bi-trash me-1"></i> Supprimer définitivement mon compte
                </button>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
