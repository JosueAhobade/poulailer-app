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
</head>

<body>

<div class="page">

    <aside class="navbar navbar-vertical navbar-expand-lg">
        <div class="container-fluid">

            <h1 class="navbar-brand">
                🐔 Poulailler
            </h1>

            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav pt-lg-3">

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            Tableau de bord
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reports.create') }}">
                            Rapport journalier
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reports.index') }}">
                            Historique
                        </a>
                    </li>

                </ul>
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
                @yield('content')
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/js/tabler.min.js"></script>

</body>
</html>