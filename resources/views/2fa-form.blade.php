@extends("layouts.main")

@section("main-content")
    <form>
        @csrf
        <input type="password" name="totp_password" placeholder="Two factor password..." />
        <button type="submit" formmethod="post" formaction="{{ route("2fa.login") }}">Log in</button>
    </form>
@endsection
