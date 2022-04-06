<div {{ $attributes->merge(["class" => "account-controls",]) }} >
    <div class="account-avatar">
        {{ $user->name }}
        [{{ $user->username }}]
    </div>
    <a href="/logout">Log out</a>
</div>
