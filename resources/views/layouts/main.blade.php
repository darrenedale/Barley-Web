<!DOCTYPE html>
<html>
<head>
@stack("styles")
@stack("scripts")
</head>
<body>
<header>
    <h1>Barley</h1>
    @yield("header-content")
</header>
<section id="main-content" clas="main-content">
    @yield("main-content")
</section>
<footer>
</footer>
</body>
</html>
