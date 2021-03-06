<!DOCTYPE html>
<html lang="en">
<head>
    <title>Barley</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
@stack("styles")
@stack("scripts")
</head>
<body>
<header>
    <h1>Barley</h1>
    @yield("header-content")
    @if (\Illuminate\Support\Facades\Auth::check())
        <x-account.controls :user="\Illuminate\Support\Facades\Auth::user()" />
    @else
        <x-account.login-form />
    @endif
</header>
<section id="main-content" class="main-content">
@yield("main-content")
</section>
<footer>
</footer>
</body>
</html>
