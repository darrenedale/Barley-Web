<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
@stack("styles")
@stack("scripts")
</head>
<body>
<header>
    <h1>Barley</h1>
    @yield("header-content")
    @if (\Illuminate\Support\Facades\Auth::check())
        <div class="account-avatar">
            {{ \Illuminate\Support\Facades\Auth::user()->username }}
        </div>
    @else
        {{-- TODO extract this to an include/component --}}
        <div class="login-form">
            <form>
                @csrf
                @error("username")
                <span class="barley-error">{{ $errors->first("username") }}</span>
                @enderror
                <input @error("username") class="barley-error-field" @enderror type="text" name="username" placeholder="Username or email..." />
                <input type="password" name="password" placeholder="Password..." />
                <button type="submit" formaction="/login" formmethod="POST">Log in</button>
            </form>
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
