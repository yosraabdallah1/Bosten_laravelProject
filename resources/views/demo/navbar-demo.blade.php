@extends('layouts.app')

@section('title', 'Démonstration de la navigation Bosten')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">🌿 Démonstration de la nouvelle navigation Bosten</h1>
            <p class="lead mb-4">
                Découvrez les améliorations apportées à la navbar pour faciliter la navigation dans la plateforme.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-list"></i> Fonctionnalités principales</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Navigation responsive et intuitive</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Barre de recherche intégrée</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Menu déroulant des catégories</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Indicateur de panier avec compteur</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Menu utilisateur personnalisé</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Navigation séparée admin/client</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-palette"></i> Design amélioré</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-star-fill text-warning me-2"></i>
                            <span>Icônes Bootstrap Icons intégrées</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-star-fill text-warning me-2"></i>
                            <span>Animations et transitions fluides</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-star-fill text-warning me-2"></i>
                            <span>Indication visuelle de la page active</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-star-fill text-warning me-2"></i>
                            <span>Style cohérent avec la charte Bosten</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-star-fill text-warning me-2"></i>
                            <span>Breadcrumb pour la navigation contextuelle</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-phone"></i> Navigation mobile optimisée</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 border rounded bg-light">
                                <i class="bi bi-list display-4 text-success"></i>
                                <h6 class="mt-2">Menu hamburger</h6>
                                <p class="small">Accès complet au menu sur mobile</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 border rounded bg-light">
                                <i class="bi bi-search display-4 text-primary"></i>
                                <h6 class="mt-2">Recherche adaptative</h6>
                                <p class="small">Barre de recherche responsive</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 border rounded bg-light">
                                <i class="bi bi-person-circle display-4 text-warning"></i>
                                <h6 class="mt-2">Profil mobile</h6>
                                <p class="small">Menu utilisateur optimisé mobile</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="alert alert-success">
                <h5><i class="bi bi-lightbulb"></i> Conseils d'utilisation</h5>
                <ul class="mb-0">
                    <li>Passez votre souris sur "Catégories" pour voir le menu déroulant</li>
                    <li>Cliquez sur votre avatar pour accéder à votre menu personnel</li>
                    <li>Utilisez la barre de recherche pour trouver rapidement des produits</li>
                    <li>Le breadcrumb vous indique toujours où vous vous trouvez</li>
                    <li>Sur mobile, utilisez le menu hamburger pour naviguer</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="{{ route('home') }}" class="btn btn-success btn-lg">
                <i class="bi bi-shop me-2"></i> Retour à la boutique
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-success btn-lg ms-2">
                <i class="bi bi-box-seam me-2"></i> Voir les produits
            </a>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        border-radius: 10px;
        transition: transform 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    .list-group-item {
        border: none;
        padding: 0.75rem 0;
    }
    
    .display-4 {
        font-size: 3rem;
    }
</style>
@endsection