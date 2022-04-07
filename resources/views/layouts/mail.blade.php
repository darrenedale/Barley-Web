<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Barley</title>
</head>
<body>
<header>
    <h1>
        @section('heading')
            Barley
        @show
    </h1>
</header>

<section class="body">
    @yield('body')
</section>

</body>
</html>
