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

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="{{ route('dashboard') }}"
                        >
                            <span class="nav-link-title">
                                Tableau de bord
                            </span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="{{ route('reports.create') }}"
                        >
                            <span class="nav-link-title">
                                Rapport journalier
                            </span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="{{ route('reports.index') }}"
                        >
                            <span class="nav-link-title">
                                Historique
                            </span>
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