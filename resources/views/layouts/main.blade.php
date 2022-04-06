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
</header>
<section id="main-content" class="main-content">
@yield("main-content")
</section>
<footer>
</footer>
</body>
</html>
