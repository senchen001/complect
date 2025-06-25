<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>эКомплекты</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }
    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }
    .table td {
        vertical-align: middle;
    }
</style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('makeComplect') }}">Комплекты</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/">Книговыдача</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('inventory') }}">Проверка фонда</a>
            </li>    
        </ul>
<!---------------------------------------------------------------------------------->
@auth
    <!-- Показывать для авторизованных пользователей -->
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            {{ Auth::user()->name }}
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('profile') }}">Профиль</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">Выйти</button>
                </form>
            </li>
        </ul>
    </div>
@else
    <!-- Показывать для гостей -->
    <a href="{{ route('login') }}" class="btn btn-primary me-2">Войти</a>
    @if (Route::has('register'))
        <a href="{{ route('register') }}" class="btn btn-outline-primary">Регистрация</a>
    @endif
@endauth


<!---------------------------------------------------------------------------------->
        </div>
    </nav>

    <div class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>