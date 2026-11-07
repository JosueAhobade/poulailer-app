<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Poulailler')</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/css/tabler.min.css"
    >

    <style>
        tr[data-href] { cursor: pointer; }
    </style>
</head>

<body>

<div class="page">

   <aside class="navbar navbar-vertical navbar-expand-lg">
        <div class="container-fluid">

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#sidebar-menu"
                aria-controls="sidebar-menu"
                aria-expanded="false"
                aria-label="Ouvrir le menu"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <h1 class="navbar-brand navbar-brand-autodark">
                🐔 Poulailler
            </h1>

            <div class="collapse navbar-collapse" id="sidebar-menu">

                <ul class="navbar-nav pt-lg-3">

                    @foreach([
                        ['route' => 'dashboard', 'active' => 'dashboard', 'label' => 'Tableau de bord'],
                        ['route' => 'reports.create', 'active' => 'reports.create', 'label' => 'Rapport journalier'],
                        ['route' => 'reports.index', 'active' => ['reports.index', 'reports.show', 'reports.consumption.*', 'treatments.*', 'vaccinations.*'], 'label' => 'Historique'],
                        ['route' => 'inputs.index', 'active' => ['inputs.*', 'movements.*'], 'label' => 'Intrants & stock'],
                        ['route' => 'eggs.collections.index', 'active' => 'eggs.collections.*', 'label' => 'Œufs — ramassages'],
                        ['route' => 'eggs.sales.index', 'active' => 'eggs.sales.*', 'label' => 'Œufs — ventes'],
                    ] as $item)

                        <li class="nav-item @if(request()->routeIs($item['active'])) active @endif">
                            <a
                                class="nav-link"
                                href="{{ route($item['route']) }}"
                            >
                                <span class="nav-link-title">
                                    {{ $item['label'] }}
                                </span>
                            </a>
                        </li>

                    @endforeach

                    @can('access-accounting')
                        <li class="nav-item @if(request()->routeIs('accounting.*', 'transactions.*')) active @endif">
                            <a class="nav-link" href="{{ route('accounting.index') }}">
                                <span class="nav-link-title">
                                    Comptabilité
                                </span>
                            </a>
                        </li>
                    @endcan

                </ul>

                @auth
                    <div class="mt-auto pt-3 pb-3 px-3 border-top">
                        <div class="fw-bold">{{ auth()->user()->name }}</div>
                        <div class="text-secondary small mb-2">
                            {{ auth()->user()->role?->label() }}
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                Se déconnecter
                            </button>
                        </form>
                    </div>
                @endauth

            </div>

        </div>
    </aside>

    <div class="page-wrapper">

        <div class="page-header">
            <div class="container-xl">
                <h2 class="page-title">
                    @yield('title')
                </h2>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/js/tabler.min.js"></script>
<script>
    // Rend cliquables les lignes de tableau portant un attribut data-href,
    // sans intercepter les clics sur les boutons, liens et formulaires.
    document.addEventListener('click', function (event) {
        const row = event.target.closest('tr[data-href]');

        if (!row || event.target.closest('a, button, input, select, form')) {
            return;
        }

        window.location.href = row.dataset.href;
    });
</script>
@stack('scripts')
</body>
</html>