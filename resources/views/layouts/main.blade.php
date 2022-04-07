<!DOCTYPE html>
<html lang="en">
<head>
    <title>Barley</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
@stack("styles")
    <script type="module" src="js/Main.js"></script>
@stack("scripts")
</head>
<body>
<header>
    <h1>Barley</h1>
    @yield("header-content")
    @if (\Illuminate\Support\Facades\Auth::check())
        <x-account.controls :user="\Illuminate\Support\Facades\Auth::user()" />
    @else
        <div>
            <ul class="barley-login-selector">
                <li data-form-type="registration">register</li>
                <li data-form-ttype="login">log in</li>
            </ul>
            <x-account.login-form />
            <x-account.registration-form />
        </div>
    @endif
</header>
<section id="main-content" class="main-content">
@yield("main-content")
</section>
<footer>
</footer>
</body>
</html>
