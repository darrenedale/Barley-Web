<div class="registration-form">
    <form>
        @csrf

        @error("name")
        <span class="barley-error">{{ $errors->first("name") }}</span>
        @enderror
        <input @error("name") class="barley-error-field" @enderror type="text" name="name" autocomplete="name" placeholder="{{ $namePlaceholder }}" value="{{ $defaults["name"] ?? "" }}" />

        @error("username")
        <span class="barley-error">{{ $errors->first("username") }}</span>
        @enderror
        <input @error("username") class="barley-error-field" @enderror type="text" name="username" autocomplete="username" placeholder="{{ $usernamePlaceholder }}" value="{{ $defaults["username"] ?? "" }}" />

        @error("email")
        <span class="barley-error">{{ $errors->first("email") }}</span>
        @enderror
        <input @error("email") class="barley-error-field" @enderror type="email" name="email" autocomplete="email" placeholder="{{ $emailPlaceholder }}" value="{{ $defaults["email"] ?? "" }}" />

        @error("password")
        <span class="barley-error">{{ $errors->first("email") }}</span>
        @enderror
        <input @error("password") class="barley-error-field" @enderror type="password" autocomplete="new-password" name="password" placeholder="{{ $passwordPlaceholder }}" />
        <input type="password" name="password_confirmed" autocomplete="new-password" placeholder="{{ $confirmPasswordPlaceholder }}" />

        {{-- NOTE $method is guaranteed to be either GET or POST --}}
        <button type="submit" formaction="{{ $endpoint }}" formmethod="{!! $method !!}">{{ $registerButtonLabel }}</button>
    </form>
</div>
