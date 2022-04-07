<div class="login-form">
    <form>
        @csrf

        @error("username")
        <span class="barley-error">{{ $errors->first("username") }}</span>
        @enderror
        <input @error("username") class="barley-error-field" @enderror type="text" name="username" placeholder="{{ $usernamePlaceholder }}" value="{{ $username ?? "" }}"/>

        @error("password")
        <span class="barley-error">{{ $errors->first("password") }}</span>
        @enderror
        <input @error("password") class="barley-error-field" @enderror type="password" name="password" placeholder="{{ $passwordPlaceholder }}" />

        {{-- NOTE $method is guaranteed to be either GET or POST --}}
        <button type="submit" formaction="{{ $endpoint }}" formmethod="{!! $method !!}">{{ $loginButtonLabel }}</button>
    </form>
</div>
