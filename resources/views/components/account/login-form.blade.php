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
