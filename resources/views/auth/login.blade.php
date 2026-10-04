
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Connexion - Poulailler</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/css/tabler.min.css">
</head>

<body class="d-flex flex-column bg-surface-secondary">

<div class="page page-center">

    <div class="container container-tight py-4">

        <div class="text-center mb-4">
            <h1>🐔 Poulailler</h1>
            <p class="text-secondary">
                Gestion et suivi de l'exploitation
            </p>
        </div>

        <div class="card card-md">

            <div class="card-body">

                <h2 class="h2 text-center mb-4">
                    Connexion
                </h2>

                @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('login.submit') }}">

                    @csrf

                    <div class="mb-3">
                        <label class="form-label">
                            Adresse email
                        </label>

                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email') }}"
                               autocomplete="username"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Mot de passe
                        </label>

                        <input type="password"
                               name="password"
                               class="form-control"
                               autocomplete="current-password"
                               required>
                    </div>

                    <div class="d-grid">
                        <button type="submit"
                                class="btn btn-primary">
                            Se connecter
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

</body>
</html>
